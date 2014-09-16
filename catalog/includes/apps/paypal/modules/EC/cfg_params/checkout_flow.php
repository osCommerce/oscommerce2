<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_EC_Cfg_checkout_flow {
    var $default = '0';
    var $sort_order = 200;

    function getSetField() {
      $input = '<input type="radio" id="checkoutFlowSelectionDefault" name="checkout_flow" value="0"' . (OSCOM_APP_PAYPAL_EC_CHECKOUT_FLOW == '0' ? ' checked="checked"' : '') . '><label for="checkoutFlowSelectionDefault">Default</label>' .
               '<input type="radio" id="checkoutFlowSelectionInContext" name="checkout_flow" value="1"' . (OSCOM_APP_PAYPAL_EC_CHECKOUT_FLOW == '1' ? ' checked="checked"' : '') . '><label for="checkoutFlowSelectionInContext">In-Context (Beta)</label>';

      $result = <<<EOT
<div>
  <p>
    <label>Checkout Flow</label>

    Set this to Default to have PayPal automatically choose the best checkout flow to use, or to use the new In-Context checkout flow (currently in beta).<br /><br /><em>In-Context only works in certain conditions and automatically disables Instant Update.</em>
  </p>

  <div id="checkoutFlowSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#checkoutFlowSelection').buttonset();
});
</script>
EOT;

      return $result;
    }
  }
?>
