<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  $btUpdateDownloadResult = array('rpcStatus' => -1);

  if ( isset($HTTP_GET_VARS['v']) && is_numeric($HTTP_GET_VARS['v']) && ($HTTP_GET_VARS['v'] > $OSCOM_Braintree->getVersion()) ) {
    if ( $OSCOM_Braintree->isWritable(DIR_FS_CATALOG . 'includes/apps/braintree/work') ) {
      if ( !file_exists(DIR_FS_CATALOG . 'includes/apps/braintree/work') ) {
        mkdir(DIR_FS_CATALOG . 'includes/apps/braintree/work', 0777, true);
      }

      $filepath = DIR_FS_CATALOG . 'includes/apps/braintree/work/update.zip';

      if ( file_exists($filepath) && is_writable($filepath) ) {
        unlink($filepath);
      }

      $btUpdateDownloadFile = $OSCOM_Braintree->makeApiCall('https://apps.oscommerce.com/index.php?Download&braintree&app&2_300&' . str_replace('.', '_', $HTTP_GET_VARS['v']) . '&update');

      $save_result = @file_put_contents($filepath, $btUpdateDownloadFile);

      if ( ($save_result !== false) && ($save_result > 0) ) {
        $btUpdateDownloadResult['rpcStatus'] = 1;
      } else {
        $btUpdateDownloadResult['error'] = $OSCOM_Braintree->getDef('error_saving_download', array('filepath' => $OSCOM_Braintree->displayPath($filepath)));
      }
    } else {
      $btUpdateDownloadResult['error'] = $OSCOM_Braintree->getDef('error_download_directory_permissions', array('filepath' => $OSCOM_Braintree->displayPath(DIR_FS_CATALOG . 'includes/apps/braintree/work')));
    }
  }

  echo json_encode($btUpdateDownloadResult);

  exit;
?>
