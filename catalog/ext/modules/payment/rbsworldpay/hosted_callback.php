<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  if (isset($_POST['M_sid']) && !empty($_POST['M_sid'])) {
    chdir('../../../../');
    require ('includes/application_top.php');

    if ($_POST['transStatus'] == 'Y') {
      $pass = false;

      if (isset($_POST['M_hash']) && !empty($_POST['M_hash']) && ($_POST['M_hash'] == md5($_POST['M_sid'] . $_POST['M_cid'] . $_POST['cartId'] . $_POST['M_lang'] . number_format($_POST['amount'], 2) . MODULE_PAYMENT_RBSWORLDPAY_HOSTED_MD5_PASSWORD))) {
        $pass = true;
      }

      if (isset($_POST['callbackPW']) && ($_POST['callbackPW'] != MODULE_PAYMENT_RBSWORLDPAY_HOSTED_CALLBACK_PASSWORD)) {
        $pass = false;
      }

      if (osc_not_null(MODULE_PAYMENT_RBSWORLDPAY_HOSTED_CALLBACK_PASSWORD) && !isset($_POST['callbackPW'])) {
        $pass = false;
      }

      if ($pass == true) {
        include('includes/languages/' . basename($_POST['M_lang']) . '/modules/payment/rbsworldpay_hosted.php');

        $order_query = osc_db_query("select orders_status, currency, currency_value from " . TABLE_ORDERS . " where orders_id = '" . (int)$_POST['cartId'] . "' and customers_id = '" . (int)$_POST['M_cid'] . "'");
        if (osc_db_num_rows($order_query) > 0) {
          $order = osc_db_fetch_array($order_query);

          if ($order['orders_status'] == MODULE_PAYMENT_RBSWORLDPAY_HOSTED_PREPARE_ORDER_STATUS_ID) {
            $order_status_id = (MODULE_PAYMENT_RBSWORLDPAY_HOSTED_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_RBSWORLDPAY_HOSTED_ORDER_STATUS_ID : (int)DEFAULT_ORDERS_STATUS_ID);

            osc_db_query("update " . TABLE_ORDERS . " set orders_status = '" . $order_status_id . "', last_modified = now() where orders_id = '" . (int)$_POST['cartId'] . "'");

            $sql_data_array = array('orders_id' => $_POST['cartId'],
                                    'orders_status_id' => $order_status_id,
                                    'date_added' => 'now()',
                                    'customer_notified' => '0',
                                    'comments' => 'WorldPay: Transaction Verified');

            osc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

            if (MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TESTMODE == 'True') {
              $sql_data_array = array('orders_id' => $_POST['cartId'],
                                      'orders_status_id' => $order_status_id,
                                      'date_added' => 'now()',
                                      'customer_notified' => '0',
                                      'comments' => MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TEXT_WARNING_DEMO_MODE);

              osc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
            }
?>
<html>
<head>
<title><wpdisplay msg=result.success></title>
<style>
.pageHeading {
  font-family: Verdana, Arial, sans-serif;
  font-size: 20px;
  font-weight: bold;
  color: #9a9a9a;
}

.main {
  font-family: Verdana, Arial, sans-serif;
  font-size: 11px;
  line-height: 1.5;
}
</style>
</head>
<body>
<p class="pageHeading"><?php echo STORE_NAME; ?></p>

<p class="main" align="center"><?php echo MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TEXT_SUCCESSFUL_TRANSACTION; ?></p>

<form action="<?php echo osc_href_link('checkout', 'process&' . session_name() . '=' . $_POST['M_sid'] . '&hash=' . $_POST['hash'], 'SSL', false); ?>" method="post"><p align="center"><input type="submit" value="<?php echo sprintf(MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TEXT_CONTINUE_BUTTON, addslashes(STORE_NAME)); ?>" /></p></form>

<p>&nbsp;</p>

<WPDISPLAY ITEM=banner>

</body>
</html>
<?php
          }
        }
      }
    }
  }
?>
