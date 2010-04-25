<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class securityCheck_config_file_catalog {
    var $type = 'warning';

    function securityCheck_config_file_catalog() {
      global $language;

      include(DIR_FS_ADMIN . 'includes/languages/' . $language . '/modules/security_check/config_file_catalog.php');
    }

    function pass() {
      return (file_exists(DIR_FS_CATALOG . 'includes/configure.php') && !tep_is_writable(DIR_FS_CATALOG . 'includes/configure.php'));
    }

    function getMessage() {
      return WARNING_CONFIG_FILE_WRITEABLE;
    }
  }
?>
