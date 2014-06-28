<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_PS_Cfg_ewp_openssl {
    var $default = '/usr/bin/openssl';

    function getSetField() {
      $input = tep_draw_input_field('ewp_openssl', OSCOM_APP_PAYPAL_PS_EWP_OPENSSL, 'id="inputPsEwpOpenSsl"');

      $result = <<<EOT
<div>
  <p>
    <label for="inputPsEwpOpenSsl">OpenSSL Location</label>

    The location and filename of the openssl binary file.
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
