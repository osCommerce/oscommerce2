<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\OM;

abstract class SitesAbstract implements \OSC\OM\SitesInterface
{
    protected $code;
    protected $page;
    protected $app;
    protected $route;
    public $actions_index = 1;

    abstract protected function init();
    abstract public function setPage();

    final public function __construct()
    {
        $this->code = (new \ReflectionClass($this))->getShortName();

        return $this->init();
    }

    public function getCode()
    {
        return $this->code;
    }

    public function hasPage()
    {
        return isset($this->page);
    }

    public function getPage()
    {
        return $this->page;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public static function resolveRoute(array $route, array $routes)
    {
    }
}
