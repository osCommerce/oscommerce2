<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_PS_Cfg_ewp_status {
    var $default = '-1';

    function getSetField() {
      $input = '<input type="radio" id="ewpStatusSelectionTrue" name="ewp_status" value="1"' . (OSCOM_APP_PAYPAL_PS_EWP_STATUS == '1' ? ' checked="checked"' : '') . '><label for="ewpStatusSelectionTrue">True</label>' .
               '<input type="radio" id="ewpStatusSelectionFalse" name="ewp_status" value="-1"' . (OSCOM_APP_PAYPAL_PS_EWP_STATUS == '-1' ? ' checked="checked"' : '') . '><label for="ewpStatusSelectionFalse">False</label>';

      $result = <<<EOT
<div>
  <p>
    <label>Encrypted Website Payments</label>

    Set this to True to encrypt the transaction parameters sent to PayPal with your private key and certificate.
  </p>

  <div id="ewpStatusSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#ewpStatusSelection').buttonset();
});
</script>
EOT;

      return $result;
    }
  }
?>
