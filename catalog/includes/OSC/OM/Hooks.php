<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

use OSC\OM\Apps;
use OSC\OM\OSCOM;
use OSC\OM\Registry;

class Hooks
{
    protected $site;
    protected $hooks = [];

    public function __construct($site = null)
    {
        if (!isset($site)) {
            $site = OSCOM::getSite();
        }

        $this->site = basename($site);
    }

    public function call($group, $hook, $action = 'execute')
    {
        if (!isset($this->hooks[$this->site][$group][$hook][$action])) {
            $this->register($group, $hook, $action);
        }

        $result = [];

        foreach ($this->hooks[$this->site][$group][$hook][$action] as $code) {
            $class = Apps::getModuleClass($code, 'Hooks');
            $regclass = 'Hook_' . str_replace(['/', '\\'], '_', $code);

            if (!Registry::exists($regclass)) {
                Registry::set($regclass, new $class());
            }

            $bait = Registry::get($regclass)->$action();

            if (!empty($bait)) {
                $result[] = $bait;
            }
        }

        return $result;
    }

    protected function register($group, $hook, $action)
    {
        $group = basename($group);

        $this->hooks[$this->site][$group][$hook][$action] = [];

        $directory = OSCOM::BASE_DIR . 'Module/Hooks/' . $this->site . '/' . $group;

        if (file_exists($directory)) {
            if ($dir = new \DirectoryIterator($directory)) {
                foreach ($dir as $file) {
                    if (!$file->isDot() && !$file->isDir() && ($file->getExtension() == 'php') && ($file->getBasename('.php') == $hook)) {
                        $class = 'OSC\OM\Module\Hooks\\' . $this->site . '\\' . $group . '\\' . $hook;

                        if (method_exists($class, $action)) {
                            $this->hooks[$this->site][$group][$hook][$action][] = $class;
                        }
                    }
                }
            }
        }

        $filter = [
            'site' => $this->site,
            'group' => $group,
            'hook' => $hook
        ];

        foreach (Apps::getModules('Hooks', null, $filter) as $k => $class) {
            if (method_exists($class, $action)) {
                $this->hooks[$this->site][$group][$hook][$action][] = $k;
            }
        }
    }
}
