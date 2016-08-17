<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

interface SitesInterface
{
    public function getPage();
    public function setPage();
    public static function resolveRoute(array $route, array $routes);
}
