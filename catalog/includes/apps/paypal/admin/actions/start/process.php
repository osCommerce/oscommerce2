<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  if ( isset($HTTP_GET_VARS['type']) && in_array($HTTP_GET_VARS['type'], array('live', 'sandbox')) ) {
    $params = array('return_url' => tep_href_link('paypal.php', 'action=start&subaction=retrieve', 'SSL'),
                    'type' => $HTTP_GET_VARS['type'],
                    'site_url' => tep_href_link(FILENAME_DEFAULT, '', 'SSL', false),
                    'site_currency' => DEFAULT_CURRENCY);

    if (tep_not_null(STORE_OWNER_EMAIL_ADDRESS) && function_exists('filter_var') && (filter_var(STORE_OWNER_EMAIL_ADDRESS, FILTER_VALIDATE_EMAIL) !== false)) {
      $params['email'] = STORE_OWNER_EMAIL_ADDRESS;
    }

    if (tep_not_null(STORE_OWNER)) {
      $name_array = explode(' ', STORE_OWNER, 2);

      $params['firstname'] = $name_array[0];
      $params['surname'] = isset($name_array[1]) ? $name_array[1] : '';
    }

    if (tep_not_null(STORE_NAME)) {
      $params['site_name'] = STORE_NAME;
    }

    $result_string = $OSCOM_PayPal->makeApiCall('https://www.oscommerce.com/index.php?RPC&Website&Index&PayPalStart', $params);
    $result = array();

    if ( !empty($result_string) && (substr($result_string, 0, 9) == 'rpcStatus') ) {
      $raw = explode("\n", $result_string);

      foreach ( $raw as $r ) {
        $key = explode('=', $r, 2);

        if ( is_array($key) && (count($key) === 2) && !empty($key[0]) && !empty($key[1]) ) {
          $result[$key[0]] = $key[1];
        }
      }

      if ( isset($result['rpcStatus']) && ($result['rpcStatus'] === '1') && isset($result['merchant_id']) && (preg_match('/^[A-Za-z0-9]{32}$/', $result['merchant_id']) === 1) && isset($result['redirect_url']) && isset($result['secret']) ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_START_MERCHANT_ID', $result['merchant_id']);
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_START_SECRET', $result['secret']);

        tep_redirect($result['redirect_url']);
      } else {
        $OSCOM_PayPal->addAlert($OSCOM_PayPal->getDef('alert_onboarding_initialization_error'), 'error');
      }
    } else {
      $OSCOM_PayPal->addAlert($OSCOM_PayPal->getDef('alert_onboarding_connection_error'), 'error');
    }
  } else {
    $OSCOM_PayPal->addAlert($OSCOM_PayPal->getDef('alert_onboarding_account_type_error'), 'error');
  }
?>
