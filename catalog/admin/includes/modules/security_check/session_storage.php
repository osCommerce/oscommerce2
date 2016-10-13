<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\FileSystem;
  use OSC\OM\OSCOM;

  class securityCheck_session_storage {
    var $type = 'warning';

    function securityCheck_session_storage() {
      include(OSCOM::getConfig('dir_root') . 'includes/languages/' . $_SESSION['language'] . '/modules/security_check/session_storage.php');
    }

    function pass() {
      return ((OSCOM::getConfig('store_sessions') != '') || FileSystem::isWritable(session_save_path()));
    }

    function getMessage() {
      if (OSCOM::getConfig('store_sessions') == '') {
        if (!is_dir(session_save_path())) {
          return WARNING_SESSION_DIRECTORY_NON_EXISTENT;
        } elseif (!FileSystem::isWritable(session_save_path())) {
          return WARNING_SESSION_DIRECTORY_NOT_WRITEABLE;
        }
      }
    }
  }
?>
