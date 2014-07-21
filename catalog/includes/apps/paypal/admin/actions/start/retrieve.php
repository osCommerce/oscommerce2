<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  $params = array('merchant_id' => OSCOM_APP_PAYPAL_START_MERCHANT_ID,
                  'secret' => OSCOM_APP_PAYPAL_START_SECRET);

  $result_string = $OSCOM_PayPal->makeApiCall('https://ssl.oscommerce.com/index.php?RPC&Website&Index&PayPalStart&action=retrieve', $params);
  $result = array();

  if ( !empty($result_string) && (substr($result_string, 0, 9) == 'rpcStatus') ) {
    $raw = explode("\n", $result_string);

    foreach ( $raw as $r ) {
      $key = explode('=', $r, 2);

      if ( is_array($key) && (count($key) === 2) && !empty($key[0]) && !empty($key[1]) ) {
        $result[$key[0]] = $key[1];
      }
    }
  }

  echo '<pre>';
  var_dump($result);
  echo '</pre>';
  echo '<p><a href="' . tep_href_link('paypal.php') . '">Back</a></p>';
  exit;
?>
