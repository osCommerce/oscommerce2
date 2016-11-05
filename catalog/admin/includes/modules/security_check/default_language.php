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

  class securityCheck_default_language {
    var $type = 'error';

    protected $lang;

    function __construct() {
      $this->lang = Registry::get('Language');

      $this->lang->loadDefinitions('modules/security_check/default_language');
    }

    function pass() {
      return defined('DEFAULT_LANGUAGE');
    }

    function getMessage() {
      return OSCOM::getDef('error_no_default_language_defined');
    }
  }
?>
