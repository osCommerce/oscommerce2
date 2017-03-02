<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2017 osCommerce

  Released under the GNU General Public License
*/

  class securityCheck_error_log_files {
    var $type = 'warning';

    function securityCheck_error_log_files() {
      global $language;

      include(DIR_FS_ADMIN . 'includes/languages/' . $language . '/modules/security_check/error_log_files.php');

      $this->title = MODULE_SECURITY_CHECK_ERROR_LOG_FILES_TITLE;
    }

    function pass() {
      if (is_dir(DIR_FS_CATALOG . 'includes/work/error_logs') && is_writable(DIR_FS_CATALOG . 'includes/work/error_logs')) {
        if (count(glob(DIR_FS_CATALOG . 'includes/work/error_logs/errors-*.txt')) > 0) {
          return false;
        }
      }

      return true;
    }

    function getMessage() {
      return WARNING_ERROR_LOG_FILES_EXIST;
    }
  }
?>
