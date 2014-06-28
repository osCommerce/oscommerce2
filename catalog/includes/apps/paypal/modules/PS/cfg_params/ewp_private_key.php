<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_PS_Cfg_ewp_private_key {
    var $default = '';

    function getSetField() {
      $input = tep_draw_input_field('ewp_private_key', OSCOM_APP_PAYPAL_PS_EWP_PRIVATE_KEY, 'id="inputPsEwpPrivateKey"');

      $result = <<<EOT
<div>
  <p>
    <label for="inputPsEwpPrivateKey">Your Private Key</label>

    The location and filename of your Private Key to use for encrypting the parameters. (*.pem)
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
