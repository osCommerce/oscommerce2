<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_Braintree_CC_Cfg_paypal_button_size {
    var $default = '2';
    var $title;
    var $description;
    var $sort_order = 161;

    function OSCOM_Braintree_CC_Cfg_paypal_button_size() {
      global $OSCOM_Braintree;

      $this->title = $OSCOM_Braintree->getDef('cfg_cc_paypal_button_size_title');
      $this->description = $OSCOM_Braintree->getDef('cfg_cc_paypal_button_size_desc');
    }

    function getSetField() {
      global $OSCOM_Braintree;

      $input = '<input type="radio" id="paypalButtonSizeSelectionSmall" name="paypal_button_size" value="2"' . (OSCOM_APP_PAYPAL_BRAINTREE_CC_PAYPAL_BUTTON_SIZE == '2' ? ' checked="checked"' : '') . '><label for="paypalButtonSizeSelectionSmall">' . $OSCOM_Braintree->getDef('cfg_cc_paypal_button_size_small') . '</label>' .
               '<input type="radio" id="paypalButtonSizeSelectionTiny" name="paypal_button_size" value="1"' . (OSCOM_APP_PAYPAL_BRAINTREE_CC_PAYPAL_BUTTON_SIZE == '1' ? ' checked="checked"' : '') . '><label for="paypalButtonSizeSelectionTiny">' . $OSCOM_Braintree->getDef('cfg_cc_paypal_button_size_tiny') . '</label>' .
               '<input type="radio" id="paypalButtonSizeSelectionMedium" name="paypal_button_size" value="3"' . (OSCOM_APP_PAYPAL_BRAINTREE_CC_PAYPAL_BUTTON_SIZE == '3' ? ' checked="checked"' : '') . '><label for="paypalButtonSizeSelectionMedium">' . $OSCOM_Braintree->getDef('cfg_cc_paypal_button_size_medium') . '</label>';

      $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
  </p>

  <div id="paypalButtonSizeSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#paypalButtonSizeSelection').buttonset();
});
</script>
EOT;

      return $result;
    }
  }
?>
