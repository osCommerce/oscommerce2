<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2008 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../');
  require('includes/application_top.php');

  $ip_address = tep_get_ip_address();

  if ( ($ip_address == '69.20.58.35') || ($ip_address == '207.97.201.192') ) {
    if (isset($_POST['cs1']) && is_numeric($_POST['cs1']) && isset($_POST['cs2']) && is_numeric($_POST['cs2']) && isset($_POST['cs3']) && !empty($_POST['cs3']) && isset($_POST['product_id']) && ($_POST['product_id'] == MODULE_PAYMENT_CHRONOPAY_PRODUCT_ID) && isset($_POST['total']) && !empty($_POST['total']) && isset($_POST['transaction_type']) && !empty($_POST['transaction_type'])) {
      if ($_POST['cs3'] == md5(MODULE_PAYMENT_CHRONOPAY_PRODUCT_ID . $_POST['cs2'] . $_POST['cs1'] . $_POST['total'] . MODULE_PAYMENT_CHRONOPAY_MD5_HASH)) {
        $order_query = tep_db_query("select order_status, currency, currency_value from " . TABLE_ORDERS . " where orders_id = '" . (int)$_POST['cs2'] . "' and customers_id = '" . (int)$_POST['cs1'] . "'");

        if (tep_db_num_rows($order_query) > 0) {
          $order = tep_db_fetch_array($order_query);

          if ($order['order_status'] == MODULE_PAYMENT_CHRONOPAY_PREPARE_ORDER_STATUS_ID) {
            $total_query = tep_db_query("select value from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$_POST['cs2'] . "' and class = 'ot_total' limit 1");
            $total = tep_db_fetch_array($total_query);

            $comment_status = $_POST['transaction_type'] . ' (' . $_POST['transaction_id'] . '; ' . $currencies->format($_POST['total'], false, $_POST['currency']) . ')';

            $order_status_id = (MODULE_PAYMENT_CHRONOPAY_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_CHRONOPAY_ORDER_STATUS_ID : (int)DEFAULT_ORDERS_STATUS_ID);

            tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . $order_status_id . "', last_modified = now() where orders_id = '" . (int)$_POST['cs2'] . "'");

            $sql_data_array = array('orders_id' => $_POST['cs2'],
                                    'orders_status_id' => $order_status_id,
                                    'date_added' => 'now()',
                                    'customer_notified' => '0',
                                    'comments' => 'ChronoPay Verified [' . $comment_status . ']');

            tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
          }
        }
      }
    }
  }

  require('includes/application_bottom.php');
?>
