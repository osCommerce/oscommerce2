<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class cfgm_shipping {
    var $code = 'shipping';
    var $directory;
    var $language_directory = DIR_FS_CATALOG_LANGUAGES;
    var $key = 'MODULE_SHIPPING_INSTALLED';
    var $title;
    var $template_integration = false;

    function cfgm_shipping() {
      $this->directory = DIR_FS_CATALOG_MODULES . 'shipping/';
      $this->title = MODULE_CFG_MODULE_SHIPPING_TITLE;
    }
  }
?>
