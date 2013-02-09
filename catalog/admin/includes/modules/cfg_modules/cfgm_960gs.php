<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce

  Released under the GNU General Public License
*/

  class cfgm_960gs {
    var $code = '960gs';
    var $directory;
    var $language_directory = DIR_FS_CATALOG_LANGUAGES;
    var $key = 'MODULE_960GS_INSTALLED';
    var $title;
    var $template_integration = true;

    function cfgm_960gs() {
      $this->directory = DIR_FS_CATALOG_MODULES . '960gs/';
      $this->title = MODULE_CFG_MODULE_960GS_TITLE;
    }
  }
?>
