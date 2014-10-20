<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_LOGIN_Cfg_sandbox_client_id {
    var $default = '';
    var $title;
    var $description;
    var $sort_order = 400;

    function OSCOM_PayPal_LOGIN_Cfg_sandbox_client_id() {
      global $OSCOM_PayPal;

      $this->title = $OSCOM_PayPal->getDef('cfg_login_sandbox_client_id_title');
      $this->description = $OSCOM_PayPal->getDef('cfg_login_sandbox_client_id_desc');
    }

    function getSetField() {
      $input = tep_draw_input_field('sandbox_client_id', OSCOM_APP_PAYPAL_LOGIN_SANDBOX_CLIENT_ID, 'id="inputLogInSandboxClientId"');

      $result = <<<EOT
<div>
  <p>
    <label for="inputLogInSandboxClientId">{$this->title}</label>

    {$this->description}
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
