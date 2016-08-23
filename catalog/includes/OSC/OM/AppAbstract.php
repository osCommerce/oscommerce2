<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

use OSC\OM\OSCOM;

abstract class AppAbstract
{
    public $code;
    public $title;
    public $vendor;
    public $version;
    public $modules = [];

    abstract protected function init();

    final public function __construct() {
        $this->setInfo();

        $this->init();
    }

    final public function link()
    {
        $args = func_get_args();

        $parameters = 'A&' . $this->vendor . '\\' . $this->code;

        if (isset($args[0])) {
            $args[0] = $parameters .= '&' . $args[0];
        } else {
            $args[0] = $parameters;
        }

        array_unshift($args, 'index.php');

        return forward_static_call_array([
            'OSC\OM\OSCOM',
            'link'
        ], $args);
    }

    final public function redirect()
    {
        $args = func_get_args();

        $parameters = 'A&' . $this->vendor . '\\' . $this->code;

        if (isset($args[0])) {
            $args[0] = $parameters .= '&' . $args[0];
        } else {
            $args[0] = $parameters;
        }

        array_unshift($args, 'index.php');

        return forward_static_call_array([
            'OSC\OM\OSCOM',
            'redirect'
        ], $args);
    }

    final public function getCode()
    {
        return $this->code;
    }

    final public function getVendor()
    {
        return $this->vendor;
    }

    final public function getTitle()
    {
        return $this->title;
    }

    final public function getVersion()
    {
        return $this->version;
    }

    final public function getModules()
    {
        return $this->modules;
    }

    final public function hasModule($module, $type)
    {
    }

    final private function setInfo()
    {
        $r = new \ReflectionClass($this);

        $this->code = $r->getShortName();
        $this->vendor = array_slice(explode('\\', $r->getNamespaceName()), -2, 1)[0];

        $metafile = OSCOM::BASE_DIR . 'OSC/Apps/' . $this->vendor . '/' . $this->code . '/oscommerce.json';

        if (!file_exists($metafile) || (($json = json_decode(file_get_contents($metafile), true)) === null)) {
            trigger_error('OSC\OM\AppAbstract::setInfo(): ' . $this->vendor . '\\' . $this->code . ' - Could not read App information in ' . $metafile . '.');

            return false;
        }

        $this->title = $json['title'];
        $this->version = $json['version'];
        $this->modules = $json['modules'];
    }
}
