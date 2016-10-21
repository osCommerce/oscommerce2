<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  $btUpdateLogResult = array('rpcStatus' => -1);

  if ( isset($HTTP_GET_VARS['v']) && is_numeric($HTTP_GET_VARS['v']) && file_exists(DIR_FS_CATALOG . 'includes/apps/braintree/work/update_log-' . basename($HTTP_GET_VARS['v']) . '.php') ) {
    $btUpdateLogResult['rpcStatus'] = 1;
    $btUpdateLogResult['log'] = file_get_contents(DIR_FS_CATALOG . 'includes/apps/braintree/work/update_log-' . basename($HTTP_GET_VARS['v']) . '.php');
  }

  echo json_encode($btUpdateLogResult);

  exit;
?>
