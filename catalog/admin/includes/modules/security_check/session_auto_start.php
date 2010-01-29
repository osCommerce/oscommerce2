<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class securityCheck_session_auto_start {
    var $type = 'warning';

    function securityCheck_session_auto_start() {
      global $language;

      include(DIR_FS_ADMIN . 'includes/languages/' . $language . '/modules/security_check/session_auto_start.php');
    }

    function pass() {
      return ((bool)ini_get('session.auto_start') == false);
    }

    function getMessage() {
      return WARNING_SESSION_AUTO_START;
    }
  }
?>
