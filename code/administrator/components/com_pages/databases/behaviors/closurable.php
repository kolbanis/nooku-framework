<?php
/**
 * @version     $Id: pages.php 3029 2011-10-09 13:07:11Z johanjanssens $
 * @package     Nooku_Server
 * @subpackage  Pages
 * @copyright   Copyright (C) 2011 Timble CVBA and Contributors. (http://www.timble.net).
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.nooku.org
 */

/**
 * Assignable Database Behavior Class
 *
 * This behavior is used for saving and deleting relations. The reason for using a separate behavior is to make sure that
 * other behaviors like orderable can use methods like getAncestors, getParent.
 *
 * @author      Gergo Erdosi <http://nooku.assembla.com/profile/gergoerdosi>
 * @package     Nooku_Server
 * @subpackage  Pages
 */
class ComPagesDatabaseBehaviorClosurable extends KDatabaseBehaviorAbstract
{
    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'priority'   => KCommand::PRIORITY_HIGHEST,
            'auto_mixin' => true
        ));

        parent::_initialize($config);
    }
    
    /**
     * Inserts relations into the relation table
     * 
     * @param KCommandContext $context A command context object.
     * @return boolean True on success, false on failure.
     */
    protected function _afterTableInsert(KCommandContext $context)
    {
        if($context->affected !== false)
        {
            $data  = $context->data;
            $table = $this->getTable();
            
            // Set path and level for the current row.
            if($data->parent_id)
            {
                $query = $this->getService('koowa:database.query.select')
                    ->columns('tbl.*')
                    ->columns(array('level' => 'COUNT(crumbs.ancestor_id)'))
                    ->columns(array('path' => 'GROUP_CONCAT(crumbs.ancestor_id ORDER BY crumbs.level DESC SEPARATOR \'/\')'))
                    ->table(array('tbl' => $table->getName()))
                    ->join(array('crumbs' => $table->getRelationTable()), 'crumbs.descendant_id = tbl.'.$table->getIdentityColumn(), 'INNER')
                    ->where('tbl.'.$table->getIdentityColumn().' = :parent_id')
                    ->group('tbl.'.$table->getIdentityColumn())
                    ->order('path', 'ASC')
                    ->bind(array('parent_id' => $data->parent_id));
                
                $parent = $table->select($query, KDatabase::FETCH_ROW);
                
                if(!$parent->isNew()) {
                    $data->setData(array('level' => $parent->level + 1, 'path' => $parent->path.'/'.$data->id), false);
                }
            }
            else $data->setData(array('level' => 1, 'path' => $data->id), false);
            
            // Insert the self relation.
            $query = $this->getService('koowa:database.query.insert')
                ->table($table->getRelationTable())
                ->columns(array('ancestor_id', 'descendant_id', 'level'))
                ->values(array($data->id, $data->id, 0));
            
            $result = $table->getDatabase()->insert($query);
            
            // Insert child relations.
            if($result && $data->parent_id)
            {
                $select = $this->getService('koowa:database.query.select')
                    ->columns(array('ancestor_id', $data->id, 'level + 1'))
                    ->table($table->getRelationTable())
                    ->where('descendant_id = :descendant_id')
                    ->bind(array('descendant_id' => $data->parent_id));
                
                $query = $this->getService('koowa:database.query.insert')
                    ->table($table->getRelationTable())
                    ->columns(array('ancestor_id', 'descendant_id', 'level'))
                    ->values($select);
                
                $result = $table->getDatabase()->insert($query);
            }
            
            return $result === false ? false : true;
        }
    }
    
    /**
     * Updates relations if parent has changes
     * 
     * @link http://www.mysqlperformanceblog.com/2011/02/14/moving-subtrees-in-closure-table/
     * 
     * @param KCommandContext $context A command context object.
     * @return boolean True on success, false on failure. 
     */
    protected function _afterTableUpdate(KCommandContext $context)
    {
        if($context->affected !== false)
        {
            $data = $context->data;
            if((int) $data->parent_id != (int) end(array_values($data->parent_ids)))
            {
                $table = $this->getTable();
                if($data->parent_id)
                {
                    $parent = $table->select((int) $this->parent_id, KDatabase::FETCH_ROW);
                    if($parent->isDescendantOf($data))
                    {
                        $this->setStatusMessage(JText::_('You cannot move a node under one of its descendants'));
                        $this->setStatus(KDatabase::STATUS_FAILED);
                        return false;
                    }
                }
                
                // Delete the outdated paths for the old location.
                $query = $this->getService('koowa:database.query.delete')
                    ->table(array('a' => $table->getRelationTable()))
                    ->join(array('d' => $table->getRelationTable()), 'a.descendant_id = d.descendant_id', 'INNER')
                    ->join(array('x' => $table->getRelationTable()), 'x.ancestor_id = d.ancestor_id AND x.descendant_id = a.ancestor_id')
                    ->where('d.ancestor_id = :ancestor_id')
                    ->where('x.ancestor_id IS NULL')
                    ->bind(array('ancestor_id' => $data->id));
                
                $table->getDatabase()->delete($query);
                
                // Insert the subtree under its new location.
                $select = $this->getService('koowa:database.query.select')
                    ->columns(array('supertree.ancestor_id', 'subtree.descendant_id', 'supertree.level + subtree.level + 1'))
                    ->table(array('supertree' => $table->getRelationTable()))
                    ->join(array('subtree' => $table->getRelationTable()), null, 'INNER')
                    ->where('subtree.ancestor_id = :ancestor_id')
                    ->where('supertree.descendant_id = :descendant_id')
                    ->bind(array('ancestor_id' => $data->id, 'descendant_id' => (int) $data->parent_id));
                    
                $query = $this->getService('koowa:database.query.insert')
                    ->table($table->getRelationTable())
                    ->columns(array('ancestor_id', 'descendant_id', 'level'))
                    ->values($select);
                
                $result = $table->getDatabase()->insert($query);
                
                if($result !== false)
                {
                    $data->path = ($data->parent_id ? $parent->path.'/' : '').$data->id;
                    $this->path = $data->path;
                }
                
                return $result === false ? false : true;
            }
        }
    }
    
    /**
     * Deletes the row and its children
     *
     * @param KCommandContext $context A command context object.
     * @return boolean True on success, false on failure. 
     */
    protected function _afterTableDelete(KCommandContext $context)
    {
        if($context->affected !== false)
        {
            $this->getTable()->getCommandChain()->disable();
            $result = $context->data->getDescendants()->delete();
            $this->getTable()->getCommandChain()->enable();
            
            return $result === false ? false : true;
        }
    }
}