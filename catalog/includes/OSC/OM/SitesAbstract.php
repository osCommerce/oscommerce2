<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

use OSC\OM\HTML;

abstract class SitesAbstract implements \OSC\OM\SitesInterface
{
    protected $code;
    protected $default_page = 'Home';
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
