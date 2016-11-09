<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
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
