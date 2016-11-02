<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class securityCheckExtended_admin_http_authentication {
    var $type = 'warning';

    protected $lang;

    function __construct() {
      $this->lang = Registry::get('Language');

      $this->lang->loadDefinitions('modules/security_check/extended/admin_http_authentication');

      $this->title = OSCOM::getDef('module_security_check_extended_admin_http_authentication_title');
    }

    function pass() {

      return isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']);
    }

    function getMessage() {
      return OSCOM::getDef('module_security_check_extended_admin_http_authentication_error');
    }
  }
?>
