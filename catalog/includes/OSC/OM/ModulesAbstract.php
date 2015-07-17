<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

abstract class ModulesAbstract
{
    public $code;
    protected $interface;
    protected $ns = 'OSC\Apps\\';

    abstract public function getInfo($app, $key, $data);
    abstract public function getClass($module);

    final public function __construct()
    {
        $this->code = (new \ReflectionClass($this))->getShortName();

        $this->init();
    }

    protected function init()
    {
    }

    public function filter($modules, $filter)
    {
        return $modules;
    }
}
