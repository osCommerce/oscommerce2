<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

use OSC\OM\Registry;

abstract class ModuleAdminDashboardAbstract
{
    public $title;
    public $description;
    public $sort_order;
    public $enabled = false;

    abstract public function getOutput();
    abstract public function install();
    abstract public function keys();

    public function isEnabled() {
        return $this->enabled;
    }

    public function check() {
      return isset($this->sort_order);
    }

    public function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }
}
