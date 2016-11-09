<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_EC_Cfg_incontext_button_shape {
    var $default = '1';
    var $title;
    var $description;
    var $sort_order = 230;

    function OSCOM_PayPal_EC_Cfg_incontext_button_shape() {
      global $OSCOM_PayPal;

      $this->title = $OSCOM_PayPal->getDef('cfg_ec_incontext_button_shape_title');
      $this->description = $OSCOM_PayPal->getDef('cfg_ec_incontext_button_shape_desc');
    }

    function getSetField() {
      global $OSCOM_PayPal;

      $input = '<input type="radio" id="incontextButtonShapePill" name="incontext_button_shape" value="1"' . (OSCOM_APP_PAYPAL_EC_INCONTEXT_BUTTON_SHAPE == '1' ? ' checked="checked"' : '') . '><label for="incontextButtonShapePill">' . $OSCOM_PayPal->getDef('cfg_ec_incontext_button_shape_pill') . '</label>' .
               '<input type="radio" id="incontextButtonShapeRect" name="incontext_button_shape" value="2"' . (OSCOM_APP_PAYPAL_EC_INCONTEXT_BUTTON_SHAPE == '2' ? ' checked="checked"' : '') . '><label for="incontextButtonShapeRect">' . $OSCOM_PayPal->getDef('cfg_ec_incontext_button_shape_rect') . '</label>';

      $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
  </p>

  <div id="incontextButtonShapeSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#incontextButtonShapeSelection').buttonset();
});
</script>
EOT;

      return $result;
    }
  }
?>
