<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;

  class cfgm_payment {
    var $code = 'payment';
    var $directory;
    var $language_directory;
    var $site = 'Shop';
    var $key = 'MODULE_PAYMENT_INSTALLED';
    var $title;
    var $template_integration = false;

    function __construct() {
      $this->directory = OSCOM::getConfig('dir_root', $this->site) . 'includes/modules/payment/';
      $this->language_directory = OSCOM::getConfig('dir_root', $this->site) . 'includes/languages/';
      $this->title = MODULE_CFG_MODULE_PAYMENT_TITLE;
    }
  }
?>
