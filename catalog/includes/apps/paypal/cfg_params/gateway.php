<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_Cfg_gateway {
    var $default = '1';
    var $sort_order = 100;

    function getSetField() {
      $input = '<input type="radio" id="gatewaySelectionPayPal" name="gateway" value="1"' . (OSCOM_APP_PAYPAL_GATEWAY == '1' ? ' checked="checked"' : '') . '><label for="gatewaySelectionPayPal">PayPal</label>' .
               '<input type="radio" id="gatewaySelectionPayflow" name="gateway" value="0"' . (OSCOM_APP_PAYPAL_GATEWAY == '0' ? ' checked="checked"' : '') . '><label for="gatewaySelectionPayflow">Payflow</label>';

      $result = <<<EOT
<div>
  <p>
    <label>Gateway</label>

    Set this to the gateway to use to process payments through.
  </p>

  <div id="gatewaySelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#gatewaySelection').buttonset();
});
</script>
EOT;

      return $result;
    }
  }
?>
