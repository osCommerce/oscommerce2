<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  $ppUpdateReleasesResult = array('rpcStatus' => -1);

  if ( function_exists('json_encode') ) {
    $ppUpdateReleasesResponse = @json_decode($OSCOM_PayPal->makeApiCall('http://apps.oscommerce.com/index.php?RPC&GetUpdates&paypal&app&2_3&' . str_replace('.', '_', number_format($OSCOM_PayPal->getVersion(), 3))), true);

    if ( is_array($ppUpdateReleasesResponse) && isset($ppUpdateReleasesResponse['rpcStatus']) && ($ppUpdateReleasesResponse['rpcStatus'] === 1) && isset($ppUpdateReleasesResponse['app']['releases']) ) {
      $ppUpdateReleasesResult['rpcStatus'] = 1;

      $ppMaxVersion = 0;

      foreach ( $ppUpdateReleasesResponse['app']['releases'] as $ppUpdateRelease ) {
        if ( is_numeric($ppUpdateRelease['version']) ) {
          $ppUpdateReleasesResult['releases'][] = $ppUpdateRelease;

          if ( $ppUpdateRelease['version'] > $ppMaxVersion ) {
            $ppMaxVersion = $ppUpdateRelease['version'];
          }
       }
      }
    }

    echo json_encode($ppUpdateReleasesResult);
  }

  $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_VERSION_CHECK', date('j') . (isset($ppUpdateReleasesResult['releases']) && isset($ppMaxVersion) ? '-' . $ppMaxVersion : ''));

  exit;
?>
