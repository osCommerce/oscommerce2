<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;

  class securityCheck_default_currency {
    var $type = 'error';

    function securityCheck_default_currency() {
      include(OSCOM::getConfig('dir_root') . 'includes/languages/' . $_SESSION['language'] . '/modules/security_check/default_currency.php');
    }

    function pass() {
      return defined('DEFAULT_CURRENCY');
    }

    function getMessage() {
      return ERROR_NO_DEFAULT_CURRENCY_DEFINED;
    }
  }
?>
