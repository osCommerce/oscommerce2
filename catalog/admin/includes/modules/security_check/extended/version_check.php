<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  class securityCheckExtended_version_check {
    var $type = 'warning';
    var $has_doc = true;

    function securityCheckExtended_version_check() {
      global $language;

      include(DIR_FS_ADMIN . 'includes/languages/' . $language . '/modules/security_check/extended/version_check.php');

      $this->title = MODULE_SECURITY_CHECK_EXTENDED_VERSION_CHECK_TITLE;
    }

    function pass() {
      $cache_file = DIR_FS_CACHE . 'oscommerce_version_check.cache';

      return file_exists($cache_file) && (filemtime($cache_file) > strtotime('-30 days'));
    }

    function getMessage() {
      return '<a href="' . tep_href_link(FILENAME_VERSION_CHECK) . '">' . MODULE_SECURITY_CHECK_EXTENDED_VERSION_CHECK_ERROR . '</a>';
    }
  }
?>
