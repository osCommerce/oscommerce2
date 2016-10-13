<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\FileSystem;
  use OSC\OM\OSCOM;

  class securityCheck_config_file_catalog {
    var $type = 'warning';

    function securityCheck_config_file_catalog() {
      include(OSCOM::getConfig('dir_root') . 'includes/languages/' . $_SESSION['language'] . '/modules/security_check/config_file_catalog.php');
    }

    function pass() {
      return !FileSystem::isWritable(OSCOM::getConfig('dir_root', 'Shop') . 'includes/configure.php');
    }

    function getMessage() {
      return WARNING_CONFIG_FILE_WRITEABLE;
    }
  }
?>
