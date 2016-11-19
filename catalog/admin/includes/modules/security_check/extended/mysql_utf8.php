<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class securityCheckExtended_mysql_utf8 {
    var $type = 'warning';
    var $has_doc = true;

    protected $lang;

    function __construct() {
      $this->lang = Registry::get('Language');

      $this->lang->loadDefinitions('modules/security_check/extended/mysql_utf8');

      $this->title = OSCOM::getDef('module_security_check_extended_mysql_utf8_title');
    }

    function pass() {
      $OSCOM_Db = Registry::get('Db');

      $Qcheck = $OSCOM_Db->query('show table status');

      if ($Qcheck->fetch() !== false) {
        do {
          if ($Qcheck->hasValue('Collation') && ($Qcheck->value('Collation') != 'utf8_unicode_ci')) {
            return false;
          }
        } while ($Qcheck->fetch());
      }

      return true;
    }

    function getMessage() {
      return '<a href="' . OSCOM::link('database_tables.php') . '">' . OSCOM::getDef('module_security_check_extended_mysql_utf8_error') . '</a>';
    }
  }
?>
