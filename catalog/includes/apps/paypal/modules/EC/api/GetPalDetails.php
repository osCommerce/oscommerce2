<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  function OSCOM_PayPal_EC_Api_GetPalDetails($OSCOM_PayPal, $server, $extra_params = null) {
    if ( $server == 'live' ) {
      $api_url = 'https://api-3t.paypal.com/nvp';
    } else {
      $api_url = 'https://api-3t.sandbox.paypal.com/nvp';
    }

    $params = array('VERSION' => $OSCOM_PayPal->getApiVersion(),
                    'METHOD' => 'GetPalDetails',
                    'USER' => $OSCOM_PayPal->getCredentials('EC', 'username'),
                    'PWD' => $OSCOM_PayPal->getCredentials('EC', 'password'),
                    'SIGNATURE' => $OSCOM_PayPal->getCredentials('EC', 'signature'));

    $post_string = '';

    foreach ( $params as $key => $value ) {
      $post_string .= $key . '=' . urlencode(utf8_encode(trim($value))) . '&';
    }

    $post_string = substr($post_string, 0, -1);

    $response = $OSCOM_PayPal->makeApiCall($api_url, $post_string);
    parse_str($response, $response_array);

    return array('res' => $response_array,
                 'success' => isset($response_array['PAL']),
                 'req' => $params);
  }
?>
