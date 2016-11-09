<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_Braintree_CC_Cfg_paypal_button_color {
    var $default = '1';
    var $title;
    var $description;
    var $sort_order = 160;

    function OSCOM_Braintree_CC_Cfg_paypal_button_color() {
      global $OSCOM_Braintree;

      $this->title = $OSCOM_Braintree->getDef('cfg_cc_paypal_button_color_title');
      $this->description = $OSCOM_Braintree->getDef('cfg_cc_paypal_button_color_desc');
    }

    function getSetField() {
      global $OSCOM_Braintree;

      $input = '<input type="radio" id="paypalButtonColorSelectionGold" name="paypal_button_color" value="1"' . (OSCOM_APP_PAYPAL_BRAINTREE_CC_PAYPAL_BUTTON_COLOR == '1' ? ' checked="checked"' : '') . '><label for="paypalButtonColorSelectionGold">' . $OSCOM_Braintree->getDef('cfg_cc_paypal_button_color_gold') . '</label>' .
               '<input type="radio" id="paypalButtonColorSelectionBlue" name="paypal_button_color" value="2"' . (OSCOM_APP_PAYPAL_BRAINTREE_CC_PAYPAL_BUTTON_COLOR == '2' ? ' checked="checked"' : '') . '><label for="paypalButtonColorSelectionBlue">' . $OSCOM_Braintree->getDef('cfg_cc_paypal_button_color_blue') . '</label>' .
               '<input type="radio" id="paypalButtonColorSelectionSilver" name="paypal_button_color" value="3"' . (OSCOM_APP_PAYPAL_BRAINTREE_CC_PAYPAL_BUTTON_COLOR == '3' ? ' checked="checked"' : '') . '><label for="paypalButtonColorSelectionSilver">' . $OSCOM_Braintree->getDef('cfg_cc_paypal_button_color_silver') . '</label>';

      $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
  </p>

  <div id="paypalButtonColorSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#paypalButtonColorSelection').buttonset();
});
</script>
EOT;

      return $result;
    }
  }
?>
