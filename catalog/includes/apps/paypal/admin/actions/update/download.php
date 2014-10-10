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

      $with_compress = array_search('GZ', Phar::getSupportedCompression()) !== false;

      $ppUpdateDownloadFile = $OSCOM_PayPal->makeApiCall('http://apps.oscommerce.com/index.php?Download&paypal&app&2_300&' . str_replace('.', '_', $HTTP_GET_VARS['v']) . '&update' . ($with_compress === true ? '&gz' : ''));

      $filepath = DIR_FS_CATALOG . 'includes/apps/paypal/work/update.phar';

      if ( file_exists($filepath) && is_writable($filepath) ) {
        unlink($filepath);
      }

      $save_result = @file_put_contents($filepath, $ppUpdateDownloadFile);

      if ( ($save_result !== false) && ($save_result > 0) ) {
        $ppUpdateDownloadResult['rpcStatus'] = 1;
      } else {
        $ppUpdateDownloadResult['error'] = 'Could not download the update package to the following location. Please delete the file if it exists.<br /><br />' . realpath($filepath);
      }
    } else {
      $ppUpdateDownloadResult['error'] = 'The required permissions on the following directory is not correctly set. Please update the permissions to allow write access.<br /><br />' . realpath(DIR_FS_CATALOG . 'includes/apps/paypal/work');
    }
  }

  echo json_encode($ppUpdateDownloadResult);

  exit;
?>