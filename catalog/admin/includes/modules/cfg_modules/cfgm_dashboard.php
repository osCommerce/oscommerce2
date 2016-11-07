<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;

  class cfgm_dashboard {
    var $code = 'dashboard';
    var $directory;
    var $language_directory;
    var $site = 'Admin';
    var $key = 'MODULE_ADMIN_DASHBOARD_INSTALLED';
    var $title;
    var $template_integration = false;

    function __construct() {
      $this->directory = OSCOM::getConfig('dir_root', $this->site) . 'includes/modules/dashboard/';
      $this->language_directory = OSCOM::getConfig('dir_root', $this->site) . 'includes/languages/';

      $this->title = OSCOM::getDef('module_cfg_module_dashboard_title');
    }
  }
?>
