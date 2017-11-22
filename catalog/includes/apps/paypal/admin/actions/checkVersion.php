<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  if ( class_exists('ZipArchive') && function_exists('json_encode') && function_exists('openssl_verify') ) {
    $ppUpdateReleasesResult = array('rpcStatus' => -1);

    $ppUpdateReleasesResponse = @json_decode($OSCOM_PayPal->makeApiCall('https://apps.oscommerce.com/index.php?RPC&GetUpdates&paypal&app&2_3&' . str_replace('.', '_', number_format($OSCOM_PayPal->getVersion(), 3))), true);

    if ( is_array($ppUpdateReleasesResponse) && isset($ppUpdateReleasesResponse['rpcStatus']) && ($ppUpdateReleasesResponse['rpcStatus'] === 1) ) {
      $ppUpdateReleasesResult['rpcStatus'] = 1;

      if ( isset($ppUpdateReleasesResponse['app']['releases']) ) {
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
    }

    echo json_encode($ppUpdateReleasesResult);
  } else {
    $ppUpdateReleasesResult = 'rpcStatus=-1';

    $ppUpdateReleasesResponse = $OSCOM_PayPal->makeApiCall('https://apps.oscommerce.com/index.php?RPC&GetUpdates&paypal&app&2_3&' . str_replace('.', '_', number_format($OSCOM_PayPal->getVersion(), 3)) . '&format=simple');

    if ( !empty($ppUpdateReleasesResponse) && (strpos($ppUpdateReleasesResponse, 'rpcStatus') !== false) ) {
      parse_str($ppUpdateReleasesResponse, $ppUpdateRelease);

      if ( isset($ppUpdateRelease['rpcStatus']) && ($ppUpdateRelease['rpcStatus'] == '1') ) {
        $ppUpdateReleasesResult = 'rpcStatus=1' . "\n";

        if ( isset($ppUpdateRelease['version']) && is_numeric($ppUpdateRelease['version']) ) {
          $ppUpdateReleasesResult .= 'release=' . $ppUpdateRelease['version'];

          $ppMaxVersion = $ppUpdateRelease['version'];
        }
      }
    }

    echo $ppUpdateReleasesResult;
  }

  $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_VERSION_CHECK', date('j') . (isset($ppMaxVersion) && ($ppMaxVersion > 0) ? '-' . $ppMaxVersion : ''));

  exit;
?>
