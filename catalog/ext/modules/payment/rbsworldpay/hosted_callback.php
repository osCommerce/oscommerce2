<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../');
  require ('includes/application_top.php');

  if ( !defined('MODULE_PAYMENT_RBSWORLDPAY_HOSTED_STATUS') || (MODULE_PAYMENT_RBSWORLDPAY_HOSTED_STATUS  != 'True') ) {
    exit;
  }

  include('includes/languages/' . basename($HTTP_POST_VARS['M_lang']) . '/modules/payment/rbsworldpay_hosted.php');
  include('includes/modules/payment/rbsworldpay_hosted.php');

  $rbsworldpay_hosted = new rbsworldpay_hosted();

  $error = false;

  if ( !isset($HTTP_GET_VARS['installation']) || ($HTTP_GET_VARS['installation'] != MODULE_PAYMENT_RBSWORLDPAY_HOSTED_INSTALLATION_ID) ) {
    $error = true;
  } elseif ( !isset($HTTP_POST_VARS['installation']) || ($HTTP_POST_VARS['installation'] != MODULE_PAYMENT_RBSWORLDPAY_HOSTED_INSTALLATION_ID) ) {
    $error = true;
  } elseif ( tep_not_null(MODULE_PAYMENT_RBSWORLDPAY_HOSTED_CALLBACK_PASSWORD) && (!isset($HTTP_POST_VARS['callbackPW']) || ($HTTP_POST_VARS['callbackPW'] != MODULE_PAYMENT_RBSWORLDPAY_HOSTED_CALLBACK_PASSWORD)) ) {
    $error = true;
  } elseif ( !isset($HTTP_POST_VARS['transStatus']) || ($HTTP_POST_VARS['transStatus'] != 'Y') ) {
    $error = true;
  } elseif ( !isset($HTTP_POST_VARS['M_hash']) || !isset($HTTP_POST_VARS['M_sid']) || !isset($HTTP_POST_VARS['M_cid']) || !isset($HTTP_POST_VARS['cartId']) || !isset($HTTP_POST_VARS['M_lang']) || !isset($HTTP_POST_VARS['amount']) || ($HTTP_POST_VARS['M_hash'] != md5($HTTP_POST_VARS['M_sid'] . $HTTP_POST_VARS['M_cid'] . $HTTP_POST_VARS['cartId'] . $HTTP_POST_VARS['M_lang'] . number_format($HTTP_POST_VARS['amount'], 2) . MODULE_PAYMENT_RBSWORLDPAY_HOSTED_MD5_PASSWORD)) ) {
    $error = true;
  }

  if ( $error == false ) {
    $order_query = tep_db_query("select orders_id, orders_status, currency, currency_value from " . TABLE_ORDERS . " where orders_id = '" . (int)$HTTP_POST_VARS['cartId'] . "' and customers_id = '" . (int)$HTTP_POST_VARS['M_cid'] . "'");

    if (!tep_db_num_rows($order_query)) {
      $error = true;
    }
  }

  if ( $error == true ) {
    $rbsworldpay_hosted->sendDebugEmail();

    exit;
  }

  $order = tep_db_fetch_array($order_query);

  if ($order['orders_status'] == MODULE_PAYMENT_RBSWORLDPAY_HOSTED_PREPARE_ORDER_STATUS_ID) {
    $order_status_id = (MODULE_PAYMENT_RBSWORLDPAY_HOSTED_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_RBSWORLDPAY_HOSTED_ORDER_STATUS_ID : (int)DEFAULT_ORDERS_STATUS_ID);

    tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . $order_status_id . "', last_modified = now() where orders_id = '" . (int)$order['orders_id'] . "'");

    $sql_data_array = array('orders_id' => $order['orders_id'],
                            'orders_status_id' => $order_status_id,
                            'date_added' => 'now()',
                            'customer_notified' => '0',
                            'comments' => '');

    tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
  }

  $trans_result = 'WorldPay: Transaction Verified (Callback)' . "\n" .
                  'Transaction ID: ' . $HTTP_POST_VARS['transId'];

  if (MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TESTMODE == 'True') {
    $trans_result .= "\n" . MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TEXT_WARNING_DEMO_MODE;
  }

  $sql_data_array = array('orders_id' => $order['orders_id'],
                          'orders_status_id' => MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TRANSACTIONS_ORDER_STATUS_ID,
                          'date_added' => 'now()',
                          'customer_notified' => '0',
                          'comments' => $trans_result);

  tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
?>
<!DOCTYPE html>
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" />
<title><?php echo tep_output_string_protected($oscTemplate->getTitle()); ?></title>
<meta http-equiv="refresh" content="3; URL=<?php echo tep_href_link(FILENAME_CHECKOUT_PROCESS, tep_session_name() . '=' . $HTTP_POST_VARS['M_sid'] . '&hash=' . $HTTP_POST_VARS['M_hash'], 'SSL', false); ?>">
</head>
<body>
<h1><?php echo STORE_NAME; ?></h1>

<p><?php echo MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TEXT_SUCCESSFUL_TRANSACTION; ?></p>

<form action="<?php echo tep_href_link(FILENAME_CHECKOUT_PROCESS, tep_session_name() . '=' . $HTTP_POST_VARS['M_sid'] . '&hash=' . $HTTP_POST_VARS['M_hash'], 'SSL', false); ?>" method="post" target="_top">
  <p><input type="submit" value="<?php echo sprintf(MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TEXT_CONTINUE_BUTTON, addslashes(STORE_NAME)); ?>" /></p>
</form>

<p>&nbsp;</p>

<WPDISPLAY ITEM=banner>

</body>
</html>

<?php
  tep_session_destroy();
?>
