<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  $ppUpdateDownloadResult = array('rpcStatus' => -1);

  if ( isset($HTTP_GET_VARS['v']) && is_numeric($HTTP_GET_VARS['v']) && ($HTTP_GET_VARS['v'] > $OSCOM_PayPal->getVersion()) ) {
    if ( $OSCOM_PayPal->isWritable(DIR_FS_CATALOG . 'includes/apps/paypal/work') ) {
      if ( !file_exists(DIR_FS_CATALOG . 'includes/apps/paypal/work') ) {
        mkdir(DIR_FS_CATALOG . 'includes/apps/paypal/work', 0777, true);
      }

      $filepath = DIR_FS_CATALOG . 'includes/apps/paypal/work/update.zip';

      if ( file_exists($filepath) && is_writable($filepath) ) {
        unlink($filepath);
      }

      $ppUpdateDownloadFile = $OSCOM_PayPal->makeApiCall('https://apps.oscommerce.com/index.php?Download&paypal&app&2_300&' . str_replace('.', '_', $HTTP_GET_VARS['v']) . '&update');

      $save_result = @file_put_contents($filepath, $ppUpdateDownloadFile);

      if ( ($save_result !== false) && ($save_result > 0) ) {
        $ppUpdateDownloadResult['rpcStatus'] = 1;
      } else {
        $ppUpdateDownloadResult['error'] = $OSCOM_PayPal->getDef('error_saving_download', array('filepath' => $OSCOM_PayPal->displayPath($filepath)));
      }
    } else {
      $ppUpdateDownloadResult['error'] = $OSCOM_PayPal->getDef('error_download_directory_permissions', array('filepath' => $OSCOM_PayPal->displayPath(DIR_FS_CATALOG . 'includes/apps/paypal/work')));
    }
  }

  echo json_encode($ppUpdateDownloadResult);

  exit;
?>
