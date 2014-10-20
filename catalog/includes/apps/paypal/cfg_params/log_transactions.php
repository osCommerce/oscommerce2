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

    function OSCOM_PayPal_Cfg_log_transactions() {
      global $OSCOM_PayPal;

      $this->title = $OSCOM_PayPal->getDef('cfg_log_transactions_title');
      $this->description = $OSCOM_PayPal->getDef('cfg_log_transactions_desc');
    }

    function getSetField() {
      global $OSCOM_PayPal;

      $input = '<input type="radio" id="logTransactionsSelectionAll" name="log_transactions" value="1"' . (OSCOM_APP_PAYPAL_LOG_TRANSACTIONS == '1' ? ' checked="checked"' : '') . '><label for="logTransactionsSelectionAll">' . $OSCOM_PayPal->getDef('cfg_log_transactions_all') . '</label>' .
               '<input type="radio" id="logTransactionsSelectionErrors" name="log_transactions" value="0"' . (OSCOM_APP_PAYPAL_LOG_TRANSACTIONS == '0' ? ' checked="checked"' : '') . '><label for="logTransactionsSelectionErrors">' . $OSCOM_PayPal->getDef('cfg_log_transactions_errors') . '</label>' .
               '<input type="radio" id="logTransactionsSelectionDisabled" name="log_transactions" value="-1"' . (OSCOM_APP_PAYPAL_LOG_TRANSACTIONS == '-1' ? ' checked="checked"' : '') . '><label for="logTransactionsSelectionDisabled">' . $OSCOM_PayPal->getDef('cfg_log_transactions_disabled') . '</label>';

      $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
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
