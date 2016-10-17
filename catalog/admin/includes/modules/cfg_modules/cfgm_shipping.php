<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;

  class cfgm_shipping {
    var $code = 'shipping';
    var $directory;
    var $language_directory;
    var $key = 'MODULE_SHIPPING_INSTALLED';
    var $title;
    var $template_integration = false;

    function cfgm_shipping() {
      $this->directory = OSCOM::getConfig('dir_root', 'Shop') . 'includes/modules/shipping/';
      $this->language_directory = OSCOM::getConfig('dir_root', 'Shop') . 'includes/languages/';
      $this->title = MODULE_CFG_MODULE_SHIPPING_TITLE;
    }
  }
?>
