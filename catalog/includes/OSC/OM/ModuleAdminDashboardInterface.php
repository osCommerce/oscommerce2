<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

interface ModuleAdminDashboardInterface
{
    public function getOutput();
    public function install();
    public function keys();
    public function isEnabled();
    public function check();
    public function remove();
}
