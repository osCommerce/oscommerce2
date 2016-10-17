<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;

  class securityCheck_default_language {
    var $type = 'error';

    function securityCheck_default_language() {
      include(OSCOM::getConfig('dir_root') . 'includes/languages/' . $_SESSION['language'] . '/modules/security_check/default_language.php');
    }

    function pass() {
      return defined('DEFAULT_LANGUAGE');
    }

    function getMessage() {
      return ERROR_NO_DEFAULT_LANGUAGE_DEFINED;
    }
  }
?>
