<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_PS_Cfg_ewp_public_cert_id {
    var $default = '';
    var $sort_order = 1000;

    function getSetField() {
      $input = tep_draw_input_field('ewp_public_cert_id', OSCOM_APP_PAYPAL_PS_EWP_PUBLIC_CERT_ID, 'id="inputPsEwpPublicCertId"');

      $result = <<<EOT
<div>
  <p>
    <label for="inputPsEwpPublicCertId">Your PayPal Public Certificate ID</label>

    The Certificate ID assigned to your certificate shown in your PayPal Encrypted Payment Settings Profile page.
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
