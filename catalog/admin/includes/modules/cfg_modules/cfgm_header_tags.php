<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;

  class cfgm_header_tags {
    var $code = 'header_tags';
    var $directory;
    var $language_directory;
    var $site = 'Shop';
    var $key = 'MODULE_HEADER_TAGS_INSTALLED';
    var $title;
    var $template_integration = true;

    function __construct() {
      $this->directory = OSCOM::getConfig('dir_root', $this->site) . 'includes/modules/header_tags/';
      $this->language_directory = OSCOM::getConfig('dir_root', $this->site) . 'includes/languages/';
      $this->title = MODULE_CFG_MODULE_HEADER_TAGS_TITLE;
    }
  }
?>
