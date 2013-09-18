<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  class securityCheck_extended_last_run {
    var $type = 'warning';

    function securityCheck_extended_last_run() {
      global $language;

      include(DIR_FS_ADMIN . 'includes/languages/' . $language . '/modules/security_check/extended_last_run.php');
    }

    function pass() {
      global $PHP_SELF;

      if ( $PHP_SELF == 'security_checks.php' ) {
        if ( defined('MODULE_SECURITY_CHECK_EXTENDED_LAST_RUN_DATETIME') ) {
          tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . time() . "' where configuration_key = 'MODULE_SECURITY_CHECK_EXTENDED_LAST_RUN_DATETIME'");
        } else {
          tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, date_added) values ('Security Check Extended Last Run', 'MODULE_SECURITY_CHECK_EXTENDED_LAST_RUN_DATETIME', '" . time() . "', 'The date and time the last extended security check was performed.', '6', now())");
        }

        return true;
      }

      return defined('MODULE_SECURITY_CHECK_EXTENDED_LAST_RUN_DATETIME') && (MODULE_SECURITY_CHECK_EXTENDED_LAST_RUN_DATETIME > strtotime('-30 days'));
    }

    function getMessage() {
      return '<a href="' . tep_href_link('security_checks.php') . '">' . MODULE_SECURITY_CHECK_EXTENDED_LAST_RUN_OLD . '</a>';
    }
  }
?>
