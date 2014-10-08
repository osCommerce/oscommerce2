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
      $with_compress = array_search('GZ', Phar::getSupportedCompression()) !== false;

      $ppUpdateDownloadFile = $OSCOM_PayPal->makeApiCall('http://apps.oscommerce.com/index.php?Download&paypal&app&2_300&' . str_replace('.', '_', $HTTP_GET_VARS['v']) . '&update' . ($with_compress === true ? '&gz' : ''));

      $filepath = DIR_FS_CATALOG . 'includes/apps/paypal/work/update.phar' . ($with_compress === true ? '.gz' : '');

      if ( file_exists($filepath) && is_writable($filepath) ) {
        unlink($filepath);
      }

      $save_result = @file_put_contents($filepath, $ppUpdateDownloadFile);

      $phar_can_open = false;

      if ( ($save_result !== false) && ($save_result > 0) ) {
        $phar_can_open = true;

        try {
          $phar = new Phar($filepath);
        } catch ( Exception $e ) {
          $phar_can_open = false;
        }

        unset($phar);

        if ( $phar_can_open === true ) {
          $ppUpdateDownloadResult['rpcStatus'] = 1;
        } else {
          if ( file_exists($filepath) && is_writable($filepath) ) {
            unlink($filepath);
          }

          $ppUpdateDownloadResult['error'] = 'Cannot verify the file downloaded. Please try again.';
        }
      } else {
        $ppUpdateDownloadResult['error'] = 'Cannot save the file to the following location. Please update the directory permissions to allow write access and delete the file if it exists.<br /><br />' . $filepath;
      }
    } else {
      $ppUpdateDownloadResult['error'] = 'Do not have the required permissions on the following directory. Please update the permissions to allow write access.<br /><br />' . DIR_FS_CATALOG . 'includes/apps/paypal/work';
    }
  }

  echo json_encode($ppUpdateDownloadResult);

  exit;
?>
