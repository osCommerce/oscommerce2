<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_LOGIN_Cfg_log {
    var $default = '1';
    var $sort_order = 800;

    function getSetField() {
      $input = '<input type="radio" id="logSelectionAll" name="log" value="1"' . (OSCOM_APP_PAYPAL_LOGIN_LOG == '1' ? ' checked="checked"' : '') . '><label for="logSelectionAll">All</label>' .
               '<input type="radio" id="logSelectionErrors" name="log" value="0"' . (OSCOM_APP_PAYPAL_LOGIN_LOG == '0' ? ' checked="checked"' : '') . '><label for="logSelectionErrors">Errors</label>' .
               '<input type="radio" id="logSelectionDisabled" name="log" value="-1"' . (OSCOM_APP_PAYPAL_LOGIN_LOG == '-1' ? ' checked="checked"' : '') . '><label for="logSelectionDisabled">Disabled</label>';

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
