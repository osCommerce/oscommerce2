<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  function OSCOM_PayPal_LOGIN_Api_GrantToken($OSCOM_PayPal, $server, $extra_params) {
    if ( $server == 'live' ) {
      $api_url = 'https://api.paypal.com/v1/identity/openidconnect/tokenservice';
    } else {
      $api_url = 'https://api.sandbox.paypal.com/v1/identity/openidconnect/tokenservice';
    }

    $params = array('client_id' => (OSCOM_APP_PAYPAL_LOGIN_STATUS == '1') ? OSCOM_APP_PAYPAL_LOGIN_LIVE_CLIENT_ID : OSCOM_APP_PAYPAL_LOGIN_SANDBOX_CLIENT_ID,
                    'client_secret' => (OSCOM_APP_PAYPAL_LOGIN_STATUS == '1') ? OSCOM_APP_PAYPAL_LOGIN_LIVE_SECRET : OSCOM_APP_PAYPAL_LOGIN_SANDBOX_SECRET,
                    'grant_type' => 'authorization_code');

    if ( is_array($extra_params) && !empty($extra_params) ) {
      $params = array_merge($params, $extra_params);
    }

    $post_string = '';

    foreach ( $params as $key => $value ) {
      $post_string .= $key . '=' . urlencode(utf8_encode(trim($value))) . '&';
    }

    $post_string = substr($post_string, 0, -1);

    $response = $OSCOM_PayPal->makeApiCall($api_url, $post_string);
    $response_array = json_decode($response, true);

    return array('res' => $response_array,
                 'success' => (is_array($response_array) && !isset($response_array['error'])),
                 'req' => $params);
  }
?>
