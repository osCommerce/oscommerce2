<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;

  class cfgm_order_total {
    var $code = 'order_total';
    var $directory;
    var $language_directory;
    var $site = 'Shop';
    var $key = 'MODULE_ORDER_TOTAL_INSTALLED';
    var $title;
    var $template_integration = false;

    function __construct() {
      $this->directory = OSCOM::getConfig('dir_root', $this->site) . 'includes/modules/order_total/';
      $this->language_directory = OSCOM::getConfig('dir_root', $this->site) . 'includes/languages/';
      $this->title = OSCOM::getDef('module_cfg_module_order_total_title');
    }
  }
?>
