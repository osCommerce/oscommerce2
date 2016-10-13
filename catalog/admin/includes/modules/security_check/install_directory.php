<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;

  class securityCheck_install_directory {
    var $type = 'warning';

    function securityCheck_install_directory() {
      include(OSCOM::getConfig('dir_root') . 'includes/languages/' . $_SESSION['language'] . '/modules/security_check/install_directory.php');
    }

    function pass() {
      return !is_dir(OSCOM::getConfig('dir_root', 'Shop') . 'install');
    }

    function getMessage() {
      return WARNING_INSTALL_DIRECTORY_EXISTS;
    }
  }
?>
