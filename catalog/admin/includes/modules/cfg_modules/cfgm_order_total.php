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
    var $key = 'MODULE_ORDER_TOTAL_INSTALLED';
    var $title;
    var $template_integration = false;

    function cfgm_order_total() {
      $this->directory = OSCOM::getConfig('dir_root', 'Shop') . 'includes/modules/order_total/';
      $this->language_directory = OSCOM::getConfig('dir_root', 'Shop') . 'includes/languages/';
      $this->title = MODULE_CFG_MODULE_ORDER_TOTAL_TITLE;
    }
  }
?>
