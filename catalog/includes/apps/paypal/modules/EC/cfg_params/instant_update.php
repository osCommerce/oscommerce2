<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_EC_Cfg_instant_update {
    var $default = '1';
    var $title;
    var $description;
    var $sort_order = 400;

    function OSCOM_PayPal_EC_Cfg_instant_update() {
      global $OSCOM_PayPal;

      $this->title = $OSCOM_PayPal->getDef('cfg_ec_instant_update_title');
      $this->description = $OSCOM_PayPal->getDef('cfg_ec_instant_update_desc');
    }

    function getSetField() {
      global $OSCOM_PayPal;

      $input = '<input type="radio" id="instantUpdateSelectionEnabled" name="instant_update" value="1"' . (OSCOM_APP_PAYPAL_EC_INSTANT_UPDATE == '1' ? ' checked="checked"' : '') . '><label for="instantUpdateSelectionEnabled">' . $OSCOM_PayPal->getDef('cfg_ec_instant_update_enabled') . '</label>' .
               '<input type="radio" id="instantUpdateSelectionDisabled" name="instant_update" value="0"' . (OSCOM_APP_PAYPAL_EC_INSTANT_UPDATE == '0' ? ' checked="checked"' : '') . '><label for="instantUpdateSelectionDisabled">' . $OSCOM_PayPal->getDef('cfg_ec_instant_update_disabled') . '</label>';

      $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
  </p>

  <div id="instantUpdateSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#instantUpdateSelection').buttonset();
});
</script>
EOT;

      return $result;
    }
  }
?>
