<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  function OSCOM_PayPal_Api_DoCapture($OSCOM_PayPal, $server, $extra_params) {
    if ( $server == 'live' ) {
      $api_url = 'https://api-3t.paypal.com/nvp';
    } else {
      $api_url = 'https://api-3t.sandbox.paypal.com/nvp';
    }

    $params = array('USER' => $OSCOM_PayPal->getApiCredentials($server, 'username'),
                    'PWD' => $OSCOM_PayPal->getApiCredentials($server, 'password'),
                    'SIGNATURE' => $OSCOM_PayPal->getApiCredentials($server, 'signature'),
                    'VERSION' => $OSCOM_PayPal->getApiVersion(),
                    'METHOD' => 'DoCapture');

    if ( is_array($extra_params) && !empty($extra_params) ) {
      $params = array_merge($params, $extra_params);
    }

    $post_string = '';

    foreach ( $params as $key => $value ) {
      $post_string .= $key . '=' . urlencode(utf8_encode(trim($value))) . '&';
    }

    $post_string = substr($post_string, 0, -1);

    $response = $OSCOM_PayPal->makeApiCall($api_url, $post_string);
    parse_str($response, $response_array);

    return array('res' => $response_array,
                 'success' => in_array($response_array['ACK'], array('Success', 'SuccessWithWarning')),
                 'req' => $params);
  }
?>
