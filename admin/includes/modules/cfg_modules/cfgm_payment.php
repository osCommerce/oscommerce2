<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class cfgm_payment {
    var $code = 'payment';
    var $directory;
    var $language_directory = DIR_FS_CATALOG_LANGUAGES;
    var $key = 'MODULE_PAYMENT_INSTALLED';
    var $title;
    var $template_integration = false;

    function cfgm_payment() {
      $this->directory = DIR_FS_CATALOG_MODULES . 'payment/';
      $this->title = MODULE_CFG_MODULE_PAYMENT_TITLE;
    }
  }
?>
