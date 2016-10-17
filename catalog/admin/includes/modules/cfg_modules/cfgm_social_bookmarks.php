<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;

  class cfgm_social_bookmarks {
    var $code = 'social_bookmarks';
    var $directory;
    var $language_directory;
    var $key = 'MODULE_SOCIAL_BOOKMARKS_INSTALLED';
    var $title;
    var $template_integration = false;

    function cfgm_social_bookmarks() {
      $this->directory = OSCOM::getConfig('dir_root', 'Shop') . 'includes/modules/social_bookmarks/';
      $this->language_directory = OSCOM::getConfig('dir_root', 'Shop') . 'includes/languages/';
      $this->title = MODULE_CFG_MODULE_SOCIAL_BOOKMARKS_TITLE;
    }
  }
?>
