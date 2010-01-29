<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class securityCheck_download_directory {
    var $type = 'warning';

    function securityCheck_download_directory() {
      global $language;

      include(DIR_FS_ADMIN . 'includes/languages/' . $language . '/modules/security_check/download_directory.php');
    }

    function pass() {
      if (DOWNLOAD_ENABLED != 'true') {
        return true;
      }

//backwards compatibility <2.2RC3; DIR_FS_DOWNLOAD not in configure.php
      if (!defined('DIR_FS_DOWNLOAD')) {
        return true;
      }

      return is_dir(DIR_FS_DOWNLOAD);
    }

    function getMessage() {
      return WARNING_DOWNLOAD_DIRECTORY_NON_EXISTENT;
    }
  }
?>
