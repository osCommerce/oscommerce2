<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class cfgm_dashboard {
    var $code = 'dashboard';
    var $directory;
    var $language_directory;
    var $key = 'MODULE_ADMIN_DASHBOARD_INSTALLED';
    var $title;
    var $template_integration = false;

    function cfgm_dashboard() {
      $this->directory = DIR_FS_ADMIN . 'includes/modules/dashboard/';
      $this->language_directory = DIR_FS_ADMIN . 'includes/languages/';
      $this->title = MODULE_CFG_MODULE_DASHBOARD_TITLE;
    }
  }
?>
