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

  class securityCheck_config_file_catalog {
    var $type = 'warning';

    protected $lang;

    function __construct() {
      $this->lang = Registry::get('Language');

      $this->lang->loadDefinitions('modules/security_check/config_file_catalog');
    }

    function pass() {
      return !FileSystem::isWritable(OSCOM::getConfig('dir_root', 'Shop') . 'includes/configure.php');
    }

    function getMessage() {
      return OSCOM::getDef('warning_config_file_writeable', [
        'configure_file_path' => OSCOM::getConfig('dir_root', 'Shop') . 'includes/configure.php'
      ]);
    }
  }
?>
