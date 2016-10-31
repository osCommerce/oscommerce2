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

  class securityCheck_default_currency {
    var $type = 'error';

    protected $lang;

    function __construct() {
      $this->lang = Registry::get('Language');

      $this->lang->loadDefinitions('modules/security_check/default_currency');
    }

    function pass() {
      return defined('DEFAULT_CURRENCY');
    }

    function getMessage() {
      return ERROR_NO_DEFAULT_CURRENCY_DEFINED;
    }
  }
?>
