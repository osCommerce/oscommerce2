<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;

  class securityCheck_file_uploads {
    var $type = 'warning';

    function securityCheck_file_uploads() {
      include(OSCOM::getConfig('dir_root') . 'includes/languages/' . $_SESSION['language'] . '/modules/security_check/file_uploads.php');
    }

    function pass() {
      return (bool)ini_get('file_uploads');
    }

    function getMessage() {
      return WARNING_FILE_UPLOADS_DISABLED;
    }
  }
?>
