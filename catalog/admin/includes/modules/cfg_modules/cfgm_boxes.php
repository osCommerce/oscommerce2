<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;

  class cfgm_boxes {
    var $code = 'boxes';
    var $directory;
    var $language_directory;
    var $site = 'Shop';
    var $key = 'MODULE_BOXES_INSTALLED';
    var $title;
    var $template_integration = true;

    function __construct() {
      $this->directory = OSCOM::getConfig('dir_root', $this->site) . 'includes/modules/boxes/';
      $this->language_directory = OSCOM::getConfig('dir_root', $this->site) . 'includes/languages/';
      $this->title = OSCOM::getDef('module_cfg_module_boxes_title');
    }
  }
?>
