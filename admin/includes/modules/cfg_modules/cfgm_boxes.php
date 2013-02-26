<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class cfgm_boxes {
    var $code = 'boxes';
    var $directory;
    var $language_directory = DIR_FS_CATALOG_LANGUAGES;
    var $key = 'MODULE_BOXES_INSTALLED';
    var $title;
    var $template_integration = true;

    function cfgm_boxes() {
      $this->directory = DIR_FS_CATALOG_MODULES . 'boxes/';
      $this->title = MODULE_CFG_MODULE_BOXES_TITLE;
    }
  }
?>
