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
    var $title;
    var $description;
    var $sort_order = 300;

    function OSCOM_PayPal_LOGIN_Cfg_live_secret() {
      global $OSCOM_PayPal;

      $this->title = $OSCOM_PayPal->getDef('cfg_login_live_secret_title');
      $this->description = $OSCOM_PayPal->getDef('cfg_login_live_secret_desc');
    }

    function getSetField() {
      $input = tep_draw_input_field('live_secret', OSCOM_APP_PAYPAL_LOGIN_LIVE_SECRET, 'id="inputLogInLiveSecret"');

      $result = <<<EOT
<div>
  <p>
    <label for="inputLogInLiveSecret">{$this->title}</label>

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
