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
    var $title;
    var $description;
    var $sort_order = 100;

    function OSCOM_PayPal_Cfg_gateway() {
      global $OSCOM_PayPal;

      $this->title = $OSCOM_PayPal->getDef('cfg_gateway_title');
      $this->description = $OSCOM_PayPal->getDef('cfg_gateway_desc');
    }

    function getSetField() {
      global $OSCOM_PayPal;

      $input = '<input type="radio" id="gatewaySelectionPayPal" name="gateway" value="1"' . (OSCOM_APP_PAYPAL_GATEWAY == '1' ? ' checked="checked"' : '') . '><label for="gatewaySelectionPayPal">' . $OSCOM_PayPal->getDef('cfg_gateway_paypal') . '</label>' .
               '<input type="radio" id="gatewaySelectionPayflow" name="gateway" value="0"' . (OSCOM_APP_PAYPAL_GATEWAY == '0' ? ' checked="checked"' : '') . '><label for="gatewaySelectionPayflow">' . $OSCOM_PayPal->getDef('cfg_gateway_payflow') . '</label>';

      $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
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
