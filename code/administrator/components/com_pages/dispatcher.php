<?php
/**
 * @version     $Id: dispatcher.php 3030 2011-10-09 13:21:09Z johanjanssens $
 * @package     Nooku_Server
 * @subpackage  Pages
 * @copyright   Copyright (C) 2011 Timble CVBA and Contributors. (http://www.timble.net).
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.nooku.org
 */

/**
 * Dispatcher Class
 *
 * @author      Gergo Erdosi <http://nooku.assembla.com/profile/gergoerdosi>
 * @package     Nooku_Server
 * @subpackage  Pages
 */

class ComPagesDispatcher extends ComDefaultDispatcher
{
    protected function _actionDispatch(KCommandContext $context)
    {
        $view = KRequest::get('get.view', 'cmd', $this->_controller);

        if($view == 'pages' && !KRequest::has('get.menu'))
        {
            $page = $this->getService('com://admin/pages.database.table.pages')
                ->select(array('home' => 1), KDatabase::FETCH_ROW);

            $url = clone(KRequest::url());
            $url->query['view'] = $view;
            $url->query['menu'] = $page->pages_menu_id;

            $this->getService('application')->redirect($url);
        }

        if($view == 'modules' && !KRequest::has('get.application'))
        {
            $url = clone(KRequest::url());
            $url->query['application']  = 'site';

            $this->getService('application')->redirect($url);
        }

        return parent::_actionDispatch($context);
    }
}