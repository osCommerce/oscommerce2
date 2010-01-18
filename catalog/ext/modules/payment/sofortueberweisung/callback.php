<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2006 - 2007 Henri Schmidhuber (http://www.in-solution.de)
  Copyright (c) 2007 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../');
  require ('includes/application_top.php');

  if (!defined('MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_STATUS') || (MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_STATUS  != 'True')) {
    exit;
  }

  $kunden_var_0 = $kunden_var_1 = $pw = $betrag_integer = '';

  if (isset($HTTP_GET_VARS['kunden_var_0'])) {
    $kunden_var_0 = $HTTP_GET_VARS['kunden_var_0'];
  } elseif (isset($HTTP_POST_VARS['kunden_var_0'])) {
    $kunden_var_0 = $HTTP_POST_VARS['kunden_var_0'];
  }

  if (isset($HTTP_GET_VARS['kunden_var_1'])) {
    $kunden_var_1 = $HTTP_GET_VARS['kunden_var_1'];
  } elseif (isset($HTTP_POST_VARS['kunden_var_1'])) {
    $kunden_var_1 = $HTTP_POST_VARS['kunden_var_1'];
  }

  if (isset($HTTP_GET_VARS['pw'])) {
    $pw = $HTTP_GET_VARS['pw'];
  } elseif (isset($HTTP_POST_VARS['pw'])) {
    $pw = $HTTP_POST_VARS['pw'];
  }

  if (isset($HTTP_GET_VARS['betrag_integer'])) {
    $betrag_integer = $HTTP_GET_VARS['betrag_integer'];
  } elseif (isset($HTTP_POST_VARS['betrag_integer'])) {
    $betrag_integer = $HTTP_POST_VARS['betrag_integer'];
  }

  // Check if Order exists
  if (empty($kunden_var_0) || empty($kunden_var_1) || empty($pw)) {
    exit();
  }

  $comment = '';

  if ($pw != MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_BNA_PASSWORT) {
    $comment = 'ungültiges Benachrichtigung Passwort' . "\n";
  }

  // check if order exists
  $order_query = tep_db_query("select * from " . TABLE_ORDERS . " where orders_id = '" . (int)$kunden_var_0 . "' and customers_id = '" . (int)$kunden_var_1 . "'");
  if (tep_db_num_rows($order_query) > 0) {
    $order = tep_db_fetch_array($order_query);

    if ($order['orders_status'] == MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_PREPARE_ORDER_STATUS_ID) {
      $total_query = tep_db_query("select value from " . TABLE_ORDERS_TOTAL . " where orders_id = '" .  (int)$kunden_var_0 . "' and class = 'ot_total' limit 1");
      $total = tep_db_fetch_array($total_query);

      $order_total_integer = number_format($total['value'] * $currencies->get_value('EUR'), 2, '.','')*100;
      if ($order_total_integer < 1) {
        $order_total_integer = '000';
      } elseif ($order_total_integer < 10) {
        $order_total_integer = '00' . $order_total_integer;
      } elseif ($order_total_integer < 100) {
        $order_total_integer = '0' . $order_total_integer;
      }

      if ((int)$betrag_integer == (int)$order_total_integer) {
        $comment = 'Zahlung durch Sofortüberweisung Benachrichtigung bestätigt!';
      } else {
        $comment = "Sofortüberweisungs Transaktionscheck fehlgeschlagen. Bitte manuell überprüfen\n" . ($betrag_integer/100) . '!=' . ($order_total_integer/100);
      }

      if (MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_STORE_TRANSACTION_DETAILS == 'True') {
        $comment .= "\n" . serialize($HTTP_GET_VARS) . "\n" . serialize($HTTP_POST_VARS);
      }

      $order_status = (MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_ORDER_STATUS_ID : (int)DEFAULT_ORDERS_STATUS_ID);

      $sql_data_array = array('orders_id' => (int)$kunden_var_0,
                              'orders_status_id' => $order_status,
                              'date_added' => 'now()',
                              'customer_notified' => '0',
                              'comments' => $comment);

      tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

      tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . $order_status . "', last_modified = now() where orders_id = '" . (int)$kunden_var_0 . "'");
    }
  }
?>
