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
    var $site = 'Shop';
    var $key = 'MODULE_ACTION_RECORDER_INSTALLED';
    var $title;
    var $template_integration = false;

    function __construct() {
      $this->directory = OSCOM::getConfig('dir_root', $this->site) . 'includes/modules/action_recorder/';
      $this->language_directory = OSCOM::getConfig('dir_root', $this->site) . 'includes/languages/';
      $this->title = MODULE_CFG_MODULE_ACTION_RECORDER_TITLE;
    }
  }
?>
