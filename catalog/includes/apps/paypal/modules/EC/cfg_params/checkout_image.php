<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_EC_Cfg_checkout_image {
    var $default = '0';
    var $sort_order = 500;

    function getSetField() {
      $input = '<input type="radio" id="checkoutImageSelectionStatic" name="checkout_image" value="0"' . (OSCOM_APP_PAYPAL_EC_CHECKOUT_IMAGE == '0' ? ' checked="checked"' : '') . '><label for="checkoutImageSelectionStatic">Static</label>' .
               '<input type="radio" id="checkoutImageSelectionDynamic" name="checkout_image" value="1"' . (OSCOM_APP_PAYPAL_EC_CHECKOUT_IMAGE == '1' ? ' checked="checked"' : '') . '><label for="checkoutImageSelectionDynamic">Dynamic</label>';

      $result = <<<EOT
<div>
  <p>
    <label>PayPal Express Checkout Button</label>

    Set this to Dynamic if the Express Checkout button is used with a campaign.
  </p>

  <div id="checkoutImageSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#checkoutImageSelection').buttonset();
});
</script>
EOT;

      return $result;
    }
  }
?>
