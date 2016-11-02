<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  function OSCOM_PayPal_DP_Api_PayflowPayment($OSCOM_PayPal, $server, $extra_params) {
    if ( $server == 'live' ) {
      $api_url = 'https://payflowpro.paypal.com';
    } else {
      $api_url = 'https://pilot-payflowpro.paypal.com';
    }

    $params = array('USER' => $OSCOM_PayPal->hasCredentials('DP', 'payflow_user') ? $OSCOM_PayPal->getCredentials('DP', 'payflow_user') : $OSCOM_PayPal->getCredentials('DP', 'payflow_vendor'),
                    'VENDOR' => $OSCOM_PayPal->getCredentials('DP', 'payflow_vendor'),
                    'PARTNER' => $OSCOM_PayPal->getCredentials('DP', 'payflow_partner'),
                    'PWD' => $OSCOM_PayPal->getCredentials('DP', 'payflow_password'),
                    'TENDER' => 'C',
                    'TRXTYPE' => (OSCOM_APP_PAYPAL_DP_TRANSACTION_METHOD == '1') ? 'S' : 'A',
                    'CUSTIP' => $OSCOM_PayPal->getIpAddress(),
                    'BUTTONSOURCE' => $OSCOM_PayPal->getIdentifier());

    if ( is_array($extra_params) && !empty($extra_params) ) {
      $params = array_merge($params, $extra_params);
    }

    $headers = array();

    if ( isset($params['_headers']) ) {
      $headers = $params['_headers'];

      unset($params['_headers']);
    }

    $post_string = '';

    foreach ($params as $key => $value) {
      $post_string .= $key . '[' . strlen(trim($value)) . ']=' . trim($value) . '&';
    }

    $post_string = substr($post_string, 0, -1);

    $response = $OSCOM_PayPal->makeApiCall($api_url, $post_string, $headers);
    parse_str($response, $response_array);

    return array('res' => $response_array,
                 'success' => ($response_array['RESULT'] == '0'),
                 'req' => $params);
  }
?>
