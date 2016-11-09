<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_Braintree_CC_Cfg_paypal_button_shape {
    var $default = '1';
    var $title;
    var $description;
    var $sort_order = 162;

    function OSCOM_Braintree_CC_Cfg_paypal_button_shape() {
      global $OSCOM_Braintree;

      $this->title = $OSCOM_Braintree->getDef('cfg_cc_paypal_button_shape_title');
      $this->description = $OSCOM_Braintree->getDef('cfg_cc_paypal_button_shape_desc');
    }

    function getSetField() {
      global $OSCOM_Braintree;

      $input = '<input type="radio" id="paypalButtonShapeSelectionPill" name="paypal_button_shape" value="1"' . (OSCOM_APP_PAYPAL_BRAINTREE_CC_PAYPAL_BUTTON_SHAPE == '1' ? ' checked="checked"' : '') . '><label for="paypalButtonShapeSelectionPill">' . $OSCOM_Braintree->getDef('cfg_cc_paypal_button_shape_pill') . '</label>' .
               '<input type="radio" id="paypalButtonShapeSelectionRect" name="paypal_button_shape" value="2"' . (OSCOM_APP_PAYPAL_BRAINTREE_CC_PAYPAL_BUTTON_SHAPE == '2' ? ' checked="checked"' : '') . '><label for="paypalButtonShapeSelectionRect">' . $OSCOM_Braintree->getDef('cfg_cc_paypal_button_shape_rect') . '</label>';

      $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
  </p>

  <div id="paypalButtonShapeSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#paypalButtonShapeSelection').buttonset();
});
</script>
EOT;

      return $result;
    }
  }
?>
