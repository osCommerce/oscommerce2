<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  class cfgm_content {
    var $code = 'content';
    var $directory;
    var $language_directory = DIR_FS_CATALOG_LANGUAGES;
    var $key = 'MODULE_CONTENT_INSTALLED';
    var $title;
    var $template_integration = true;

    function cfgm_content() {
      $this->directory = DIR_FS_CATALOG_MODULES . 'content/';
      $this->title = MODULE_CFG_MODULE_CONTENT_TITLE;
    }
  }
?>
