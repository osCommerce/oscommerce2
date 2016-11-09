<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_EC_Cfg_incontext_button_size {
    var $default = '2';
    var $title;
    var $description;
    var $sort_order = 220;

    function OSCOM_PayPal_EC_Cfg_incontext_button_size() {
      global $OSCOM_PayPal;

      $this->title = $OSCOM_PayPal->getDef('cfg_ec_incontext_button_size_title');
      $this->description = $OSCOM_PayPal->getDef('cfg_ec_incontext_button_size_desc');
    }

    function getSetField() {
      global $OSCOM_PayPal;

      $input = '<input type="radio" id="incontextButtonSizeSmall" name="incontext_button_size" value="2"' . (OSCOM_APP_PAYPAL_EC_INCONTEXT_BUTTON_SIZE == '2' ? ' checked="checked"' : '') . '><label for="incontextButtonSizeSmall">' . $OSCOM_PayPal->getDef('cfg_ec_incontext_button_size_small') . '</label>' .
               '<input type="radio" id="incontextButtonSizeTiny" name="incontext_button_size" value="1"' . (OSCOM_APP_PAYPAL_EC_INCONTEXT_BUTTON_SIZE == '1' ? ' checked="checked"' : '') . '><label for="incontextButtonSizeTiny">' . $OSCOM_PayPal->getDef('cfg_ec_incontext_button_size_tiny') . '</label>' .
               '<input type="radio" id="incontextButtonSizeMedium" name="incontext_button_size" value="3"' . (OSCOM_APP_PAYPAL_EC_INCONTEXT_BUTTON_SIZE == '3' ? ' checked="checked"' : '') . '><label for="incontextButtonSizeMedium">' . $OSCOM_PayPal->getDef('cfg_ec_incontext_button_size_medium') . '</label>';

      $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
  </p>

  <div id="incontextButtonSizeSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#incontextButtonSizeSelection').buttonset();
});
</script>
EOT;

      return $result;
    }
  }
?>
