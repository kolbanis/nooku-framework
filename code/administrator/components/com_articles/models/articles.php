<?php
/**
 * @version     $Id$
 * @category    Nooku
 * @package     Nooku_Server
 * @subpackage  Articles
 * @copyright   Copyright (C) 2011 - 2012 Timble CVBA and Contributors. (http://www.timble.net).
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.nooku.org
 */

/**
 * Articles Model Class
 *
 * @author      John Bell <http://nooku.assembla.com/profile/johnbell>
 * @category    Nooku
 * @package     Nooku_Server
 * @subpackage  Articles
 */
class ComArticlesModelArticles extends ComDefaultModelDefault
{
    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        $this->getState()
            ->insert('category'         , 'slug')
            ->insert('category_recurse' , 'boolean', false)
            ->insert('published'     , 'int')
            ->insert('created_by', 'int')
            ->insert('access'    , 'int')
            ->insert('trashed'   , 'int')
            ->insert('searchword', 'string');

        $this->getState()->remove('sort')->insert('sort', 'cmd', 'category_title');
    }

    protected function _buildQueryColumns(KDatabaseQuerySelect $query)
    {
        parent::_buildQueryColumns($query);

        $query->columns(array(
            'category_title'    => 'categories.title',
            'created_by_name'   => 'users.name',
            'created_by_id'     => 'users.users_user_id'
        ));
    }

    protected function _buildQueryJoins(KDatabaseQuerySelect $query)
    {
        parent::_buildQueryJoins($query);

        $query->join(array('categories' => 'categories'), 'categories.categories_category_id = tbl.categories_category_id')
              ->join(array('users'  => 'users'), 'users.users_user_id = tbl.created_by');
    }

    protected function _buildQueryWhere(KDatabaseQuerySelect $query)
    {
        parent::_buildQueryWhere($query);
        
        $state = $this->getState();

        if (is_numeric($state->published)) {
        	$query->where('tbl.published = :published')->bind(array('published' => (int) $state->published));
        }

        if ($state->search || $state->searchword) {
            $search = $state->searchword ? $state->searchword : $state->search;
            $query->where('tbl.title LIKE :search')->bind(array('search' => '%' . $search . '%'));
        }

        if(is_numeric($state->category) || $state->category)
        {
            if($state->category)
            {
            	$query->where('tbl.categories_category_id IN :category' );
            	
	            if($state->category_recurse === true) {
	                $query->where('categories.parent_id IN :category', 'OR');
	            }
	
	            $query->bind(array('category' => (array) $state->category));
            }
            else $query->where('tbl.categories_category_id IS NULL');
        }

        if($state->created_by) 
        {
            $query->where('tbl.created_by = :created_by')->bind(array('created_by' => $state->created_by));
        }

        if($this->getTable()->isRevisable() && $state->trashed) {
            $query->where('tbl.deleted = :trashed')->bind(array('trashed' => 1));
        }

        if (is_numeric($state->access)) {
            $query->where('tbl.access = :access')
                ->bind(array('access' => $state->access));
        }
    }

    protected function _buildQueryOrder(KDatabaseQuerySelect $query)
    {
        $state = $this->getState();

        $direction = strtoupper($state->direction);

        if ($state->sort == 'ordering')
        {
            $query->order('category_title', 'ASC')
                  ->order('ordering', $direction);
        }
        else
        {
            $query->order($state->sort, $direction)
                  ->order('category_title', 'ASC')
                  ->order('ordering', 'ASC');
        }
    }
}