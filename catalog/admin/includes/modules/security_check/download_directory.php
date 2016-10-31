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

  class securityCheck_download_directory {
    var $type = 'warning';

    protected $lang;

    function __construct() {
      $this->lang = Registry::get('Language');

      $this->lang->loadDefinitions('modules/security_check/download_directory');
    }

    function pass() {
      if (DOWNLOAD_ENABLED != 'true') {
        return true;
      }

      return is_dir(OSCOM::getConfig('dir_root', 'Shop') . 'download/');
    }

    function getMessage() {
      return WARNING_DOWNLOAD_DIRECTORY_NON_EXISTENT;
    }
  }
?>
