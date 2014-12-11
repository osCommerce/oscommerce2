<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_Cfg_verify_ssl {
    var $default = '1';
    var $title;
    var $description;
    var $sort_order = 300;

    function OSCOM_PayPal_Cfg_verify_ssl() {
      global $OSCOM_PayPal;

      $this->title = $OSCOM_PayPal->getDef('cfg_verify_ssl_title');
      $this->description = $OSCOM_PayPal->getDef('cfg_verify_ssl_desc');
    }

    function getSetField() {
      global $OSCOM_PayPal;

      $input = '<input type="radio" id="verifySslSelectionTrue" name="verify_ssl" value="1"' . (OSCOM_APP_PAYPAL_VERIFY_SSL == '1' ? ' checked="checked"' : '') . '><label for="verifySslSelectionTrue">' . $OSCOM_PayPal->getDef('cfg_verify_ssl_true') . '</label>' .
               '<input type="radio" id="verifySslSelectionFalse" name="verify_ssl" value="0"' . (OSCOM_APP_PAYPAL_VERIFY_SSL == '0' ? ' checked="checked"' : '') . '><label for="verifySslSelectionFalse">' . $OSCOM_PayPal->getDef('cfg_verify_ssl_false') . '</label>';

      $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
  </p>

  <div id="verifySslSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#verifySslSelection').buttonset();
});
</script>
EOT;

      return $result;
    }
  }
?>
