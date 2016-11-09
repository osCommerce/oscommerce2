<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_EC_Cfg_transaction_method {
    var $default = '1';
    var $title;
    var $description;
    var $sort_order = 700;

    function OSCOM_PayPal_EC_Cfg_transaction_method() {
      global $OSCOM_PayPal;

      $this->title = $OSCOM_PayPal->getDef('cfg_ec_transaction_method_title');
      $this->description = $OSCOM_PayPal->getDef('cfg_ec_transaction_method_desc');
    }

    function getSetField() {
      global $OSCOM_PayPal;

      $input = '<input type="radio" id="transactionMethodSelectionSale" name="transaction_method" value="1"' . (OSCOM_APP_PAYPAL_EC_TRANSACTION_METHOD == '1' ? ' checked="checked"' : '') . '><label for="transactionMethodSelectionSale">' . $OSCOM_PayPal->getDef('cfg_ec_transaction_method_sale') . '</label>' .
               '<input type="radio" id="transactionMethodSelectionAuthorize" name="transaction_method" value="0"' . (OSCOM_APP_PAYPAL_EC_TRANSACTION_METHOD == '0' ? ' checked="checked"' : '') . '><label for="transactionMethodSelectionAuthorize">' . $OSCOM_PayPal->getDef('cfg_ec_transaction_method_authorize') . '</label>';

      $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
  </p>

  <div id="transactionMethodSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#transactionMethodSelection').buttonset();
});
</script>
EOT;

      return $result;
    }
  }
?>
