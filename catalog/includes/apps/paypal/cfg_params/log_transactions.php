<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_Cfg_log_transactions {
    var $default = '1';
    var $sort_order = 500;

    function getSetField() {
      $input = '<input type="radio" id="logTransactionsSelectionAll" name="log_transactions" value="1"' . (OSCOM_APP_PAYPAL_LOG_TRANSACTIONS == '1' ? ' checked="checked"' : '') . '><label for="logTransactionsSelectionAll">All</label>' .
               '<input type="radio" id="logTransactionsSelectionErrors" name="log_transactions" value="0"' . (OSCOM_APP_PAYPAL_LOG_TRANSACTIONS == '0' ? ' checked="checked"' : '') . '><label for="logTransactionsSelectionErrors">Errors</label>' .
               '<input type="radio" id="logTransactionsSelectionDisabled" name="log_transactions" value="-1"' . (OSCOM_APP_PAYPAL_LOG_TRANSACTIONS == '-1' ? ' checked="checked"' : '') . '><label for="logTransactionsSelectionDisabled">Disabled</label>';

      $result = <<<EOT
<div>
  <p>
    <label>Log Transactions</label>

    Set this to the level of transactions that should be logged.
  </p>

  <div id="logSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#logSelection').buttonset();
});
</script>
EOT;

      return $result;
    }
  }
?>
