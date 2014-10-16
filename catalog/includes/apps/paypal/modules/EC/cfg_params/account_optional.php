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
    var $title;
    var $description;
    var $sort_order = 300;

    function OSCOM_PayPal_EC_Cfg_account_optional() {
      global $OSCOM_PayPal;

      $this->title = $OSCOM_PayPal->getDef('cfg_ec_account_optional_title');
      $this->description = $OSCOM_PayPal->getDef('cfg_ec_account_optional_desc');
    }

    function getSetField() {
      global $OSCOM_PayPal;

      $input = '<input type="radio" id="accountOptionalSelectionTrue" name="account_optional" value="1"' . (OSCOM_APP_PAYPAL_EC_ACCOUNT_OPTIONAL == '1' ? ' checked="checked"' : '') . '><label for="accountOptionalSelectionTrue">' . $OSCOM_PayPal->getDef('cfg_ec_account_optional_true') . '</label>' .
               '<input type="radio" id="accountOptionalSelectionFalse" name="account_optional" value="0"' . (OSCOM_APP_PAYPAL_EC_ACCOUNT_OPTIONAL == '0' ? ' checked="checked"' : '') . '><label for="accountOptionalSelectionFalse">' . $OSCOM_PayPal->getDef('cfg_ec_account_optional_false') . '</label>';

      $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
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
