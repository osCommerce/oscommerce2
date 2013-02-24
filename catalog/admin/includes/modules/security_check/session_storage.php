<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class securityCheck_session_storage {
    var $type = 'warning';

    function securityCheck_session_storage() {
      include(DIR_FS_ADMIN . 'includes/languages/' . $_SESSION['language'] . '/modules/security_check/session_storage.php');
    }

    function pass() {
      return ((STORE_SESSIONS != '') || (is_dir(session_save_path()) && osc_is_writable(session_save_path())));
    }

    function getMessage() {
      if (STORE_SESSIONS == '') {
        if (!is_dir(session_save_path())) {
          return WARNING_SESSION_DIRECTORY_NON_EXISTENT;
        } elseif (!osc_is_writable(session_save_path())) {
          return WARNING_SESSION_DIRECTORY_NOT_WRITEABLE;
        }
      }
    }
  }
?>
