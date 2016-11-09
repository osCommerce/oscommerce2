<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_Braintree_CC_Cfg_payment_types {
    var $default = '';
    var $title;
    var $description;
    var $sort_order = 140;

    var $types;

    function OSCOM_Braintree_CC_Cfg_payment_types() {
      global $OSCOM_Braintree;

      $this->title = $OSCOM_Braintree->getDef('cfg_cc_payment_types_title');
      $this->description = $OSCOM_Braintree->getDef('cfg_cc_payment_types_desc');

      $this->types = array(
        'paypal' => 'PayPal'
      );
    }

    function getSetField() {
      global $OSCOM_Braintree;

      $active = explode(';', OSCOM_APP_PAYPAL_BRAINTREE_CC_PAYMENT_TYPES);

      $input = '';

      foreach ($this->types as $key => $value) {
        $input .= '<input type="checkbox" id="paymentTypesFormSelection' . $key . '" name="payment_types_cb" value="' . $key . '"' . (in_array($key, $active) ? ' checked="checked"' : '') . '><label for="paymentTypesFormSelection' . $key . '">' . $value . '</label>';
      }

      $input .= '<input type="hidden" name="payment_types" value="">';

      $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
  </p>

  <div id="paymentTypesFormSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#paymentTypesFormSelection').buttonset();

  $('#paymentTypesFormSelection input').closest('form').submit(function() {
    $('#paymentTypesFormSelection input[name="payment_types"]').val($('input[name="payment_types_cb"]:checked').map(function() {
      return this.value;
    }).get().join(';'));
  });
});
</script>
EOT;

      return $result;
    }
  }
?>
