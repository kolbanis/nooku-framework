<?php
/**
 * @package		Koowa_Service
 * @subpackage 	Locator
 * @copyright	Copyright (C) 2007 - 2012 Johan Janssens. All rights reserved.
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 */

namespace Nooku\Framework;

/**
 * Locator Adapter for a component
 *
 * @author		Johan Janssens <johan@nooku.org>
 * @package     Koowa_Service
 * @subpackage 	Locator
 */
class ServiceLocatorComponent extends ServiceLocatorAbstract
{
    /**
     * The type
     *
     * @var string
     */
    protected $_type = 'com';

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   object  An optional Config object with configuration options.
     * @return  void
     */
    protected function _initialize(Config $config)
    {
        $config->append(array(
            'prefixes' => array('ComBase', 'Nooku\Framework\\'),
        ));
    }

    /**
     * Get the classname
     *
     * This locator will try to create an generic or default classname on the identifier information
     * if the actual class cannot be found using the configurable fallback sequence.
     *
     * Fallback sequence : -> Named Component Specific
     *                     -> Named Component Default
     *                     -> Default Component Specific
     *                     -> Default Component Default
     *                     -> Framework Specific
     *                     -> Framework Default
     *
     * @param mixed  		 An identifier object - com:[//application/]component.[path].name
     * @return string|false  Return object on success, returns FALSE on failure
     */
    public function findClass(ServiceIdentifier $identifier)
    {
        $classes   = array();
        $path      = Inflector::camelize(implode('_', $identifier->path));
        $classname = 'Com'.ucfirst($identifier->package).$path.ucfirst($identifier->name);

        //Manually load the class to set the basepath
        if (!$this->getService('loader')->loadClass($classname, $identifier->basepath))
        {
            $classpath = $identifier->path;
            $classtype = !empty($classpath) ? array_shift($classpath) : '';

            //Create the fallback path and make an exception for views and modules
            if(!in_array($classtype, array('view','module'))) {
                $path = ucfirst($classtype).Inflector::camelize(implode('_', $classpath));
            } else {
                $path = ucfirst($classtype);
            }

            /*
             * Fallback sequence : -> Named Component Specific
             *                     -> Named Component Default
             *                     -> Default Component Specific
             *                     -> Default Component Default
             *                     -> Framework Specific
             *                     -> Framework Default
             */

            //Add the classname to prevent re-look up
            $classes[] = $classname;

            //Add the package to look up defaults
            $prefixes = $this->_prefixes;
            array_unshift($prefixes, 'Com'.ucfirst($identifier->package));

            $classname = false;
            foreach($prefixes as $prefix)
            {
                foreach(array($identifier->name, 'default') as $name)
                {
                    $classname = $prefix.$path.ucfirst($name);

                    if(!in_array($classname, $classes))
                    {
                        if(class_exists($classname))
                        {
                            $classes[] = $classname;
                            break(2);
                        }

                        $classes[] = $classname;
                    }
                }
            }
        }
        else $classes[] = $classname;

        return $classname;
    }

    /**
     * Get the path
     *
     * @param  object  	An identifier object - com:[//application/]component.[path].name
     * @return string	Returns the path
     */
    public function findPath(ServiceIdentifier $identifier)
    {
        $path  = '';
        $parts = $identifier->path;

        $component = strtolower($identifier->package);

        if(!empty($identifier->name))
        {
            if(count($parts))
            {
                if(!in_array($parts[0], array('view', 'module')))
                {
                    foreach($parts as $key => $value) {
                        $parts[$key] = Inflector::pluralize($value);
                    }
                }
                else $parts[0] = Inflector::pluralize($parts[0]);

                $path = implode('/', $parts).'/'.strtolower($identifier->name);
            }
            else $path  = strtolower($identifier->name);
        }

        $path = 'component/'.$component.'/'.$path.'.php';

        if(file_exists($identifier->basepath.'/'.$path)) {
            $path = $identifier->basepath.'/'.$path;
        } else {
            $path = JPATH_ROOT.'/'.$path;
        }

        return $path;
    }
}