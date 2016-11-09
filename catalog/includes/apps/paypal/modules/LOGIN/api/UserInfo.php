<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  function OSCOM_PayPal_LOGIN_Api_UserInfo($OSCOM_PayPal, $server, $params) {
    if ( $server == 'live' ) {
      $api_url = 'https://api.paypal.com/v1/identity/openidconnect/userinfo/?schema=openid&access_token=' . $params['access_token'];
    } else {
      $api_url = 'https://api.sandbox.paypal.com/v1/identity/openidconnect/userinfo/?schema=openid&access_token=' . $params['access_token'];
    }

    $response = $OSCOM_PayPal->makeApiCall($api_url);
    $response_array = json_decode($response, true);

    return array('res' => $response_array,
                 'success' => (is_array($response_array) && !isset($response_array['error'])),
                 'req' => $params);
  }
?>
