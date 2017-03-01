<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2017 osCommerce

  Released under the GNU General Public License
*/

  $curl_info = curl_version();

  $result = array(
    'rpcStatus' => 1,
    'curl_version' => isset($curl_info['version']) ? $curl_info['version'] : '',
    'curl_ssl_version' => isset($curl_info['ssl_version']) ? $curl_info['ssl_version'] : ''
  );

  $test = $OSCOM_PayPal->makeApiCall('https://tlstest.paypal.com', null, null, array('returnFull' => true, 'sslVersion' => 0));

  $result['default'] = (isset($test['info']['http_code']) && ((int)$test['info']['http_code'] === 200));

  $test = $OSCOM_PayPal->makeApiCall('https://tlstest.paypal.com', null, null, array('returnFull' => true, 'sslVersion' => 6));

  $result['tlsv12'] = (isset($test['info']['http_code']) && ((int)$test['info']['http_code'] === 200));

  echo json_encode($result);

  exit;
?>
