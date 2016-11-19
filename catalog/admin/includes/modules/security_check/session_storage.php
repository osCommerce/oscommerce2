<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
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
          return OSCOM::getDef('warning_session_directory_non_existent', [
            'session_path' => session_save_path()
          ]);
        } elseif (!FileSystem::isWritable(session_save_path())) {
          return OSCOM::getDef('warning_session_directory_not_writeable', [
            'session_path' => session_save_path()
          ]);
        }
      }
    }
  }
?>
