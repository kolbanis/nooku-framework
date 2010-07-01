<?php
/**
 * @version		$Id$
 * @package		Profiles
 * @copyright	Copyright (C) 2009 - 2010 Nooku. All rights reserved.
 * @license 	GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     	http://www.nooku.org
 */

class ComProfilesViewPeopleHtml extends ComProfilesViewHtml
{
	public function display()
	{		
		$model = KFactory::get($this->getModel());
		$this->assign('letters_name', $model->getLetters());

		return parent::display();
	}
}