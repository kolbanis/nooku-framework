<?php
/**
 * @version     $Id$
 * @package     Nooku_Server
 * @subpackage  Articles
 * @copyright   Copyright (C) 2011 - 2012 Timble CVBA and Contributors. (http://www.timble.net).
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.nooku.org
 */

/**
 * Orderable Database Behavior Class
 *
 * @author      Gergo Erdosi <http://nooku.assembla.com/profile/gergoerdosi>
 * @package     Nooku_Server
 * @subpackage  Articles
 */

class ComArticlesDatabaseBehaviorOrderable extends KDatabaseBehaviorOrderable
{
    protected function _beforeTableUpdate(KCommandContext $context)
    {
        if($this->featured_order) 
        {
            $this->getService('com://admin/articles.database.row.featured')
                 ->set('id', $this->id)
                 ->load()
                 ->order($this->order);
        } 
        else parent::_beforeTableUpdate($context);
    }
     
    public function _buildQueryWhere($query)
    {
        parent::_buildQueryWhere($query);
        
        if ($this->getMixer()->getIdentifier()->name == 'article' && !isset($this->featured_order)) 
        {
            $query->where('categories_category_id = :category')
                  ->where('published >= :published')
                  ->bind(array('category' => $this->category_id, 'published' => 0));
        }
    }
}