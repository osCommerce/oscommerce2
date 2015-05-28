<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

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
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
}
