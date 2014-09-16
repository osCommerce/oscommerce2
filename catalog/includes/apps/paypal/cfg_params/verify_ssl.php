<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_Cfg_verify_ssl {
    var $default = '1';
    var $sort_order = 300;

    function getSetField() {
      $input = '<input type="radio" id="verifySslSelectionTrue" name="verify_ssl" value="1"' . (OSCOM_APP_PAYPAL_VERIFY_SSL == '1' ? ' checked="checked"' : '') . '><label for="verifySslSelectionTrue">True</label>' .
               '<input type="radio" id="verifySslSelectionFalse" name="verify_ssl" value="0"' . (OSCOM_APP_PAYPAL_VERIFY_SSL == '0' ? ' checked="checked"' : '') . '><label for="verifySslSelectionFalse">False</label>';

      $result = <<<EOT
<div>
  <p>
    <label>Verify SSL</label>

    Set this to True to verify the SSL certificate when making API calls to PayPal's servers.
  </p>

  <div id="verifySslSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#verifySslSelection').buttonset();
});
</script>
EOT;

      return $result;
    }
  }
?>
