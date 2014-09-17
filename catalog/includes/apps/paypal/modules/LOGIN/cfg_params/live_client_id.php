<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_LOGIN_Cfg_live_client_id {
    var $default = '';
    var $sort_order = 200;

    function getSetField() {
      $input = tep_draw_input_field('live_client_id', OSCOM_APP_PAYPAL_LOGIN_LIVE_CLIENT_ID, 'id="inputLogInLiveClientId"');

      $result = <<<EOT
<div>
  <p>
    <label for="inputLogInLiveClientId">Live Client ID</label>

    The Client ID of the PayPal REST App Live Credentials.
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
