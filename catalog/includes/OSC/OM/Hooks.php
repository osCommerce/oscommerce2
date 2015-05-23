<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

use OSC\OM\OSCOM;
use OSC\OM\Registry;

class Hooks
{
    protected $site;
    protected $hooks = [];

    public function __construct($site)
    {
        $this->site = basename($site);

        $this->register('global');
    }

    public function register($group)
    {
        $group = basename($group);

        $directory = OSCOM::BASE_DIR . 'hooks/' . $this->site . '/' . $group;

        if (file_exists($directory)) {
            if ($dir = new \DirectoryIterator($directory)) {
                foreach ($dir as $file) {
                    if (!$file->isDot() && !$file->isDir() && ($file->getExtension() == 'php')) {
                        $code = $file->getBasename('.php');
                        $class = 'hook_' . $this->site . '_' . $group . '_' . $code;

                        include($directory . '/' . $file);
                        Registry::set($class, new $class());

                        foreach (get_class_methods(Registry::get($class)) as $method) {
                            if (substr($method, 0, 7) == 'listen_') {
                                $this->hooks[$this->site][$group][substr($method, 7)][] = $code;
                            }
                        }
                    }
                }
            }
        }
    }

    public function call($group, $action)
    {
        $result = '';

        foreach ($this->hooks[$this->site][$group][$action] as $hook) {
            $result .= call_user_func(array(Registry::get('hook_' . $this->site . '_' . $group . '_' . $hook), 'listen_' . $action));
        }

        if (!empty($result)) {
            return $result;
        }
    }
}
