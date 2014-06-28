<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  if ( isset($HTTP_GET_VARS['type']) && in_array($HTTP_GET_VARS['type'], array('live', 'sandbox')) ) {
    $params = array('return_url' => tep_href_link('paypal.php', 'action=start', 'SSL'),
                    'type' => $HTTP_GET_VARS['type']);

    if ( defined('OSCOM_APP_PAYPAL_START_MERCHANT_ID') ) {
      $params['merchant_id'] = OSCOM_APP_PAYPAL_START_MERCHANT_ID;
    }

//    $result_string = $OSCOM_PayPal->makeApiCall('https://ssl.oscommerce.com/?RPC&Website&Index&PayPalStart', $params);
    $result_string = $OSCOM_PayPal->makeApiCall('https://localhost/Projects/osCommerce/haraldpdl/oscommerce/?RPC&Website&Index&PayPalStart', $params);
    $result = array();

    if ( !empty($result_string) && (substr($result_string, 0, 9) == 'rpcStatus') ) {
      $raw = explode("\n", $result_string, 3);

      if ( is_array($raw) && (count($raw) === 3) ) {
        foreach ( $raw as $r ) {
          $key = explode('=', $r, 2);

          if ( is_array($key) && (count($key) === 2) && !empty($key[0]) && !empty($key[1]) ) {
            $result[$key[0]] = $key[1];
          }
        }
      }
    }

    if ( isset($result['rpcStatus']) && ($result['rpcStatus'] === '1') && isset($result['merchant_id']) && (preg_match('/^[A-Za-z0-9]{32}$/', $result['merchant_id']) === 1) && isset($result['redirect_url']) ) {
      if ( !defined('OSCOM_APP_PAYPAL_START_MERCHANT_ID') || (OSCOM_APP_PAYPAL_START_MERCHANT_ID != $result['merchant_id']) ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_START_MERCHANT_ID', $result['merchant_id']);
      }

//      tep_redirect($result['redirect_url']);
      echo '<b>Redirect to PayPal:</b><br /><a href="' . $result['redirect_url'] . '">' . $result['redirect_url'] . '</a></p>';

      echo '<pre>';var_dump($result);
      exit;
    }
  }
?>
