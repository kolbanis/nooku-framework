<?php
/**
 * @version     $Id: separator.php 3029 2011-10-09 13:07:11Z johanjanssens $
 * @package     Nooku_Server
 * @subpackage  Pages
 * @copyright   Copyright (C) 2011 Timble CVBA and Contributors. (http://www.timble.net).
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.nooku.org
 */

/**
 * Seperator Database Row Class
 *
 * @author      Johan Janssens <http://nooku.assembla.com/profile/johanjanssens>
 * @package     Nooku_Server
 * @subpackage  Pages
 */

class ComPagesDatabaseRowSeparator extends ComPagesDatabaseRowPage
{
    public function __get($column)
    {
        if($column == 'type_title' && !isset($this->_data['type_title'])) {
            $this->_data['type_title'] = JText::_('Separator');
        }

        if($column == 'type_description' && !isset($this->_data['type_description'])) {
            $this->_data['type_description'] = JText::_('Separator');
        }

        return parent::__get($column);
   }
}