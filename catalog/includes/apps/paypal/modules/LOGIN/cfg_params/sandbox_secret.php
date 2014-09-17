<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_LOGIN_Cfg_sandbox_secret {
    var $default = '';
    var $sort_order = 500;

    function getSetField() {
      $input = tep_draw_input_field('sandbox_secret', OSCOM_APP_PAYPAL_LOGIN_SANDBOX_SECRET, 'id="inputLogInSandboxSecret"');

      $result = <<<EOT
<div>
  <p>
    <label for="inputLogInSandboxSecret">Test Secret</label>

    The Secret of the PayPal REST App Test Credentials.
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
