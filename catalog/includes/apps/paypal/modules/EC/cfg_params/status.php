<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_EC_Cfg_status {
    var $default = '1';

    function getSetField() {
      $input = '<input type="radio" id="statusSelectionLive" name="status" value="1"' . (OSCOM_APP_PAYPAL_EC_STATUS == '1' ? ' checked="checked"' : '') . '><label for="statusSelectionLive">Live</label>' .
               '<input type="radio" id="statusSelectionSandbox" name="status" value="0"' . (OSCOM_APP_PAYPAL_EC_STATUS == '0' ? ' checked="checked"' : '') . '><label for="statusSelectionSandbox">Sandbox</label>' .
               '<input type="radio" id="statusSelectionDisabled" name="status" value="-1"' . (OSCOM_APP_PAYPAL_EC_STATUS == '-1' ? ' checked="checked"' : '') . '><label for="statusSelectionDisabled">Disabled</label>';

      $result = <<<EOT
<div>
  <p>
    <label>Status</label>

    Set this to Live to start accepting payments or to Sandbox to perform test orders.
  </p>

  <div id="statusSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#statusSelection').buttonset();
});
</script>
EOT;

      return $result;
    }
  }
?>
