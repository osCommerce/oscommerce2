<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
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
      return OSCOM::getDef('warning_session_auto_start');
    }
  }
?>
