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

  class securityCheckExtended_version_check {
    var $type = 'warning';
    var $has_doc = true;

    protected $lang;

    function __construct() {
      $this->lang = Registry::get('Language');

      $this->lang->loadDefinitions('modules/security_check/extended/version_check');

      $this->title = MODULE_SECURITY_CHECK_EXTENDED_VERSION_CHECK_TITLE;
    }

    function pass() {
      $OSCOM_Cache = Registry::get('Cache');

      return $OSCOM_Cache->exists('core_version_check') && ($OSCOM_Cache->getTime('core_version_check') > strtotime('-30 days'));
    }

    function getMessage() {
      return '<a href="' . OSCOM::link('online_update.php') . '">' . MODULE_SECURITY_CHECK_EXTENDED_VERSION_CHECK_ERROR . '</a>';
    }
  }
?>
