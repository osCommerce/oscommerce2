<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

use OSC\OM\OSCOM;

class Hooks
{
    protected $site;
    protected $hooks = [];

    public function __construct($site)
    {
        $this->site = basename($site);
    }

    public function call($group, $hook, $action)
    {
        if (!isset($this->hooks[$this->site][$group][$hook][$action])) {
            $this->register($group, $hook, $action);
        }

        $result = [];

        foreach ($this->hooks[$this->site][$group][$hook][$action] as $ns) {
            $bait = call_user_func(array($ns . '\\' . $this->site . '\\' . $group . '\\' . $hook, $action));

            if (!empty($bait)) {
                $result[] = $bait;
            }
        }

        if (!empty($result)) {
            return $result;
        }
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
                        $ns = 'OSC\OM\Module\Hooks';
                        $class = $ns . '\\' . $this->site . '\\' . $group . '\\' . $hook;

                        if (method_exists($class, $action)) {
                            $this->hooks[$this->site][$group][$hook][$action][] = $ns;
                        }
                    }
                }
            }
        }

        $directory = OSCOM::BASE_DIR . 'apps';

        if (file_exists($directory)) {
            if ($dir = new \DirectoryIterator($directory)) {
                foreach ($dir as $file) {
                    if (!$file->isDot() && $file->isDir() && file_exists($directory . '/' . $file->getFilename() . '/Module/Hooks/' . $this->site . '/' . $group . '/' .  $hook . '.php')) {
                        $ns = 'OSC\OM\Apps\\' . $file->getFilename() . '\Module\Hooks';
                        $class = $ns . '\\' . $this->site . '\\' . $group . '\\' . $hook;

                        if (method_exists($class, $action)) {
                            $this->hooks[$this->site][$group][$hook][$action][] = $ns;
                        }
                    }
                }
            }
        }
    }
}
