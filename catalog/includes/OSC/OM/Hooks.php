<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\OM;

use OSC\OM\Apps;
use OSC\OM\OSCOM;

class Hooks
{
    protected $site;
    protected $hooks = [];
    protected $watches = [];

    public function __construct($site = null)
    {
        if (!isset($site)) {
            $site = OSCOM::getSite();
        }

        $this->site = basename($site);
    }

    public function call($group, $hook, $parameters = null, $action = null)
    {
        if (!isset($action)) {
            $action = 'execute';
        }

        if (!isset($this->hooks[$this->site][$group][$hook][$action])) {
            $this->register($group, $hook, $action);
        }

        $calls = [];

        if (isset($this->hooks[$this->site][$group][$hook][$action])) {
            $calls = $this->hooks[$this->site][$group][$hook][$action];
        }

        if (isset($this->watches[$this->site][$group][$hook][$action])) {
            $calls = array_merge($calls, $this->watches[$this->site][$group][$hook][$action]);
        }

        $result = [];

        foreach ($calls as $code) {
            $bait = null;

            if (is_string($code)) {
                $class = Apps::getModuleClass($code, 'Hooks');

                $obj = new $class();

                $bait = $obj->$action($parameters);
            } else {
                $ref = new \ReflectionFunction($code);

                if ($ref->isClosure()) {
                    $bait = $code($parameters);
                }
            }

            if (!empty($bait)) {
                $result[] = $bait;
            }
        }

        return $result;
    }

    public function output()
    {
        return implode('', call_user_func_array([$this, 'call'], func_get_args()));
    }

    public function watch($group, $hook, $action, $code)
    {
        $this->watches[$this->site][$group][$hook][$action][] = $code;
    }

    protected function register($group, $hook, $action)
    {
        $group = basename($group);

        $this->hooks[$this->site][$group][$hook][$action] = [];

        $directory = OSCOM::getConfig('dir_root', 'Shop') . 'includes/Module/Hooks/' . $this->site . '/' . $group;

        if (is_dir($directory)) {
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
