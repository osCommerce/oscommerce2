<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_EC_Cfg_account_optional {
    var $default = '0';

    function getSetField() {
      $input = '<input type="radio" id="accountOptionalSelectionTrue" name="account_optional" value="1"' . (OSCOM_APP_PAYPAL_EC_ACCOUNT_OPTIONAL == '1' ? ' checked="checked"' : '') . '><label for="accountOptionalSelectionTrue">True</label>' .
               '<input type="radio" id="accountOptionalSelectionFalse" name="account_optional" value="0"' . (OSCOM_APP_PAYPAL_EC_ACCOUNT_OPTIONAL == '0' ? ' checked="checked"' : '') . '><label for="accountOptionalSelectionFalse">False</label>';

      $result = <<<EOT
<div>
  <p>
    <label>PayPal Account Optional</label>

    Set this to True to allow customers to purchase through PayPal without requiring a PayPal account.
  </p>

  <div id="accountOptionalSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#accountOptionalSelection').buttonset();
});
</script>
EOT;

      return $result;
    }
  }
?>
