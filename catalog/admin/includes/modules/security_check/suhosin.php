<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class securityCheck_suhosin {
    var $type = 'warning';
    var $smax = 0;

    function securityCheck_suhosin() {
      global $language;

      include(DIR_FS_ADMIN . 'includes/languages/' . $language . '/modules/security_check/suhosin.php');
    }

    function pass() {
      if (extension_loaded('suhosin')) {
        $this->smax = (int)ini_get('suhosin.post.max_name_length');
        return ($this->smax < 255 ? false : true);
      }
      return true;
    }

    function getMessage() {
      return sprintf(WARNING_SUHOSIN_MISCONFIGURATION, $this->smax);
    }
  }
?>