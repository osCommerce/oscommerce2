<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_EC_Cfg_instant_update {
    var $default = '1';

    function getSetField() {
      $input = '<input type="radio" id="instantUpdateSelectionEnabled" name="instant_update" value="1"' . (OSCOM_APP_PAYPAL_EC_INSTANT_UPDATE == '1' ? ' checked="checked"' : '') . '><label for="instantUpdateSelectionEnabled">Enabled</label>' .
               '<input type="radio" id="instantUpdateSelectionDisabled" name="instant_update" value="0"' . (OSCOM_APP_PAYPAL_EC_INSTANT_UPDATE == '0' ? ' checked="checked"' : '') . '><label for="instantUpdateSelectionDisabled">Disabled</label>';

      $result = <<<EOT
<div>
  <p>
    <label>Instant Update</label>

    Enable this to have PayPal retrieve a list of shipping rates after the customer has selected their shipping address during the Express Checkout flow.<br /><br /><em>Instant Update requires SSL to be enabled on your store.</em>
  </p>

  <div id="instantUpdateSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#instantUpdateSelection').buttonset();
});
</script>
EOT;

      return $result;
    }
  }
?>
