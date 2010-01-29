<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class securityCheck_default_language {
    var $type = 'error';

    function securityCheck_default_language() {
      global $language;

      include(DIR_FS_ADMIN . 'includes/languages/' . $language . '/modules/security_check/default_language.php');
    }

    function pass() {
      return defined('DEFAULT_LANGUAGE');
    }

    function getMessage() {
      return ERROR_NO_DEFAULT_LANGUAGE_DEFINED;
    }
  }
?>
