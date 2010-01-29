<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class securityCheck_install_directory {
    var $type = 'warning';

    function securityCheck_install_directory() {
      global $language;

      include(DIR_FS_ADMIN . 'includes/languages/' . $language . '/modules/security_check/install_directory.php');
    }

    function pass() {
      return !file_exists(DIR_FS_CATALOG . 'install');
    }

    function getMessage() {
      return WARNING_INSTALL_DIRECTORY_EXISTS;
    }
  }
?>
