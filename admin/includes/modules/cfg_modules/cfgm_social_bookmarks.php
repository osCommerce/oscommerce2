<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class cfgm_social_bookmarks {
    var $code = 'social_bookmarks';
    var $directory;
    var $language_directory = DIR_FS_CATALOG_LANGUAGES;
    var $key = 'MODULE_SOCIAL_BOOKMARKS_INSTALLED';
    var $title;
    var $template_integration = false;

    function cfgm_social_bookmarks() {
      $this->directory = DIR_FS_CATALOG_MODULES . 'social_bookmarks/';
      $this->title = MODULE_CFG_MODULE_SOCIAL_BOOKMARKS_TITLE;
    }
  }
?>
