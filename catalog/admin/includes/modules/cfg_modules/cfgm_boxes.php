<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;

  class cfgm_boxes {
    var $code = 'boxes';
    var $directory;
    var $language_directory;
    var $key = 'MODULE_BOXES_INSTALLED';
    var $title;
    var $template_integration = true;

    function cfgm_boxes() {
      $this->directory = OSCOM::getConfig('dir_root', 'Shop') . 'includes/modules/boxes/';
      $this->language_directory = OSCOM::getConfig('dir_root', 'Shop') . 'includes/languages/';
      $this->title = MODULE_CFG_MODULE_BOXES_TITLE;
    }
  }
?>
