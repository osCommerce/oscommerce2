<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  $content = 'configure.php';

  $modules = array('EC', 'DP', 'HS', 'PS', 'G');
  $current_module = (isset($HTTP_GET_VARS['module']) && in_array($HTTP_GET_VARS['module'], $modules) ? $HTTP_GET_VARS['module'] : 'EC');

  if ( !defined('OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID') ) {
    $check_query = tep_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'PayPal [Transactions]' limit 1");

    if (tep_db_num_rows($check_query) < 1) {
      $status_query = tep_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
      $status = tep_db_fetch_array($status_query);

      $status_id = $status['status_id']+1;

      $languages = tep_get_languages();

      foreach ($languages as $lang) {
        tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id . "', '" . $lang['id'] . "', 'PayPal [Transactions]')");
      }

      $flags_query = tep_db_query("describe " . TABLE_ORDERS_STATUS . " public_flag");
      if (tep_db_num_rows($flags_query) == 1) {
        tep_db_query("update " . TABLE_ORDERS_STATUS . " set public_flag = 0 and downloads_flag = 0 where orders_status_id = '" . $status_id . "'");
      }
    } else {
      $check = tep_db_fetch_array($check_query);

      $status_id = $check['orders_status_id'];
    }

    $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID', $status_id);
  }

  if ( !defined('OSCOM_APP_PAYPAL_VERIFY_SSL') ) {
    $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_VERIFY_SSL', '1');
  }

  if ( !defined('OSCOM_APP_PAYPAL_PROXY') ) {
    $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PROXY', '');
  }
?>
