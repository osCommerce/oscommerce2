<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  $ppUpdateLogResult = array('rpcStatus' => -1);

  if ( isset($HTTP_GET_VARS['v']) && is_numeric($HTTP_GET_VARS['v']) && file_exists(DIR_FS_CATALOG . 'includes/apps/paypal/work/update_log-' . basename($HTTP_GET_VARS['v']) . '.php') ) {
    $ppUpdateLogResult['rpcStatus'] = 1;
    $ppUpdateLogResult['log'] = file_get_contents(DIR_FS_CATALOG . 'includes/apps/paypal/work/update_log-' . basename($HTTP_GET_VARS['v']) . '.php');
  }

  echo json_encode($ppUpdateLogResult);

  exit;
?>
