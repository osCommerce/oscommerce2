<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

abstract class AppAbstract
{
    public $code;
    public $title;
    public $version;
    public $modules = [];

    abstract protected function init();

    final public function __construct() {
        $this->setInfo();

        $this->init();
    }

    final public function getCode()
    {
        return $this->code;
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
        $this->code = (new \ReflectionClass($this))->getShortName();

        if (!file_exists(OSCOM::BASE_DIR . 'apps/' . $this->code . '/oscommerce.json') || (($json = @json_decode(file_get_contents(OSCOM::BASE_DIR . 'apps/' . $this->code . '/oscommerce.json'), true)) === null)) {
            trigger_error('OSC\OM\AppAbstract::setInfo(): ' . $this->code . ' - Could not read App information in ' . OSCOM::BASE_DIR . 'apps/' . $this->code . '/oscommerce.json.');

            return false;
        }

        $this->title = $json['title'];
        $this->version = number_format($json['version'], 3);
        $this->modules = $json['modules'];
    }
}
