<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class securityCheck_install_directory {
    var $type = 'warning';

    protected $lang;

    function __construct() {
      $this->lang = Registry::get('Language');

      $this->lang->loadDefinitions('modules/security_check/install_directory');
    }

    function pass() {
      return !is_dir(OSCOM::getConfig('dir_root', 'Shop') . 'install');
    }

    function getMessage() {
      return WARNING_INSTALL_DIRECTORY_EXISTS;
    }
  }
?>
