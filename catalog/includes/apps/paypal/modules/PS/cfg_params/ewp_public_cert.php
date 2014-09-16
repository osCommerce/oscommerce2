<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_PS_Cfg_ewp_public_cert {
    var $default = '';
    var $sort_order = 900;

    function getSetField() {
      $input = tep_draw_input_field('ewp_public_cert', OSCOM_APP_PAYPAL_PS_EWP_PUBLIC_CERT, 'id="inputPsEwpPublicCert"');

      $result = <<<EOT
<div>
  <p>
    <label for="inputPsEwpPublicCert">Your Public Certificate</label>

    The location and filename of your Public Certificate to use for encrpyting the parameters. (*.pem)
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
