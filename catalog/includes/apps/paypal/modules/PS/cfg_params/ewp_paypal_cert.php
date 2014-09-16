<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_PS_Cfg_ewp_paypal_cert {
    var $default = '';
    var $sort_order = 1100;

    function getSetField() {
      $input = tep_draw_input_field('ewp_paypal_cert', OSCOM_APP_PAYPAL_PS_EWP_PAYPAL_CERT, 'id="inputPsEwpPayPalCert"');

      $result = <<<EOT
<div>
  <p>
    <label for="inputPsEwpPayPalCert">PayPal Public Certificate</label>

    The location and filename of the PayPal Public Certificate to use for encrypting the parameters.
  </p>

  <div>
    {$input}
  </div>
</div>
EOT;

      return $result;
    }
  }
?>
