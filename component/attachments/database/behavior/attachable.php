<?php
/**
 * Nooku Framework - http://www.nooku.org
 *
 * @copyright	Copyright (C) 2011 - 2013 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		git://git.assembla.com/nooku-framework.git for the canonical source repository
 */

namespace Nooku\Component\Attachments;

use Nooku\Library;

/**
 * Attachable Database Behavior
 *
 * @author  Steven Rombauts <https://nooku.assembla.com/profile/stevenrombauts>
 * @package Nooku\Component\Attachments
 */
class DatabaseBehaviorAttachable extends Library\DatabaseBehaviorAbstract
{
    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param  Library\ObjectConfig $config A ObjectConfig object with configuration options
     * @return void
     */
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(array(
            'auto_mixin' => true
        ));

        parent::_initialize($config);
    }

    /**
     * Get a list of attachments
     *
     * @return Library\DatabaseRowsetInterface
     */
    public function getAttachments()
	{
        $model = $this->getObject('com:attachments.model.attachments');

        if(!$this->isNew())
        {
            $attachments = $model->row($this->id)
                ->table($this->getTable()->getBase())
                ->fetch();
        }
        else $attachments = $model->fetch();

        return $attachments;
	}
}