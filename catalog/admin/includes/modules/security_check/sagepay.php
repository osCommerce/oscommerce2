<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class securityCheck_sagepay {
    var $type = 'warning';
    var $smax = 0;

    function securityCheck_sagepay() {
      global $language;

      include(DIR_FS_ADMIN . 'includes/languages/' . $language . '/modules/security_check/sagepay.php');
    }

    function pass() {

      if (extension_loaded('suhosin')) {
        $this->smax = (int)ini_get('suhosin.get.max_value_length');
      }

      if (($this->smax > 0) && ($this->smax < 1000)) {
        return false;
      }
      return true;
    }

    function getMessage() {
      return sprintf(WARNING_SAGEPAY_MISCONFIGURATION, $this->smax);
    }
  }
?>