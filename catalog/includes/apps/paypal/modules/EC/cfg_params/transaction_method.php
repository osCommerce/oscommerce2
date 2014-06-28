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

    function getSetField() {
      $input = '<input type="radio" id="transactionMethodSelectionAuthorize" name="transaction_method" value="0"' . (OSCOM_APP_PAYPAL_EC_TRANSACTION_METHOD == '0' ? ' checked="checked"' : '') . '><label for="transactionMethodSelectionAuthorize">Authorize</label>' .
               '<input type="radio" id="transactionMethodSelectionSale" name="transaction_method" value="1"' . (OSCOM_APP_PAYPAL_EC_TRANSACTION_METHOD == '1' ? ' checked="checked"' : '') . '><label for="transactionMethodSelectionSale">Sale</label>';

      $result = <<<EOT
<div>
  <p>
    <label>Transaction Method</label>

    Set this to Sale to immediately capture the funds for every order made.
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
