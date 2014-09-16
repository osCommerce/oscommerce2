<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_Cfg_proxy {
    var $default = '';
    var $sort_order = 400;

    function getSetField() {
      $input = tep_draw_input_field('proxy', OSCOM_APP_PAYPAL_PROXY, 'id="inputProxy"');

      $result = <<<EOT
<div>
  <p>
    <label for="inputProxy">Proxy</label>

    Send Curl API requests through this proxy server. (host:port, eg: 123.45.67.89:8080 or proxy.example.com:8080)
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
