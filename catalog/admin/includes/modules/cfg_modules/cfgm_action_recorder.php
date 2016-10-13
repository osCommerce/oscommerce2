<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;

  class cfgm_action_recorder {
    var $code = 'action_recorder';
    var $directory;
    var $language_directory;
    var $key = 'MODULE_ACTION_RECORDER_INSTALLED';
    var $title;
    var $template_integration = false;

    function cfgm_action_recorder() {
      $this->directory = OSCOM::getConfig('dir_root', 'Shop') . 'includes/modules/action_recorder/';
      $this->language_directory = OSCOM::getConfig('dir_root', 'Shop') . 'includes/languages/';
      $this->title = MODULE_CFG_MODULE_ACTION_RECORDER_TITLE;
    }
  }
?>
