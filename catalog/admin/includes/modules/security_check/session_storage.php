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
  use OSC\OM\Registry;

  class securityCheck_session_storage {
    var $type = 'warning';

    protected $lang;

    function __construct() {
      $this->lang = Registry::get('Language');

      $this->lang->loadDefinitions('modules/security_check/session_storage');
    }

    function pass() {
      return ((OSCOM::getConfig('store_sessions') != '') || FileSystem::isWritable(session_save_path()));
    }

    function getMessage() {
      if (OSCOM::getConfig('store_sessions') == '') {
        if (!is_dir(session_save_path())) {
          return OSCOM::getDef('warning_session_directory_non_existent');
        } elseif (!FileSystem::isWritable(session_save_path())) {
          return OSCOM::getDef('warning_session_directory_not_writeable');
        }
      }
    }
  }
?>
