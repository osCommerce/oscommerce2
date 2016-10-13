<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class securityCheckExtended_mysql_utf8 {
    var $type = 'warning';
    var $has_doc = true;

    function securityCheckExtended_mysql_utf8() {
      include(OSCOM::getConfig('dir_root') . 'includes/languages/' . $_SESSION['language'] . '/modules/security_check/extended/mysql_utf8.php');

      $this->title = MODULE_SECURITY_CHECK_EXTENDED_MYSQL_UTF8_TITLE;
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
      return '<a href="' . OSCOM::link('database_tables.php') . '">' . MODULE_SECURITY_CHECK_EXTENDED_MYSQL_UTF8_ERROR . '</a>';
    }
  }
?>
