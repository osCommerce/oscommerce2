<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license GPL; https://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

interface SitesInterface
{
    public function hasPage();
    public function getPage();
    public function setPage();
    public static function resolveRoute(array $route, array $routes);
}
