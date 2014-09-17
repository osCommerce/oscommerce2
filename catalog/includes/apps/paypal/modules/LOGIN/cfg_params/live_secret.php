<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_LOGIN_Cfg_live_secret {
    var $default = '';
    var $sort_order = 300;

    function getSetField() {
      $input = tep_draw_input_field('live_secret', OSCOM_APP_PAYPAL_LOGIN_LIVE_SECRET, 'id="inputLogInLiveSecret"');

      $result = <<<EOT
<div>
  <p>
    <label for="inputLogInLiveSecret">Live Secret</label>

    The Secret of the PayPal REST App Live Credentials.
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
