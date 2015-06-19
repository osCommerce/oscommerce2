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
    protected $code;
    protected $title;
    protected $version;

    final public function __construct() {
        $this->code = (new \ReflectionClass($this))->getShortName();

        $this->init();
    }

    protected function init()
    {
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getVersion()
    {
        if (!isset($this->version)) {
            $version = trim(file_get_contents(OSCOM::BASE_DIR . 'apps/' . $this->code . '/version.txt'));

            if (is_numeric($version)) {
                $this->version = $version;
            } else {
                trigger_error('OSC\OM\AppAbstract::getVersion(): ' . $this->code . ' - Could not read App version number.');
            }
        }

        return $this->version;
    }
}
