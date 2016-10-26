<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class securityCheck_session_auto_start {
    var $type = 'warning';

    protected $lang;

    function __construct() {
      $this->lang = Registry::get('Language');

      $this->lang->loadDefinitions('modules/security_check/session_auto_start');
    }

    function pass() {
      return ((bool)ini_get('session.auto_start') == false);
    }

    function getMessage() {
      return WARNING_SESSION_AUTO_START;
    }
  }
?>
