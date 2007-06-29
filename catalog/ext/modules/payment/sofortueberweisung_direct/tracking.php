<?php
/**
 *
 *
 * @version Sofortüberweisung 1.9  27.06.2007
 * @author Henri Schmidhuber  info@in-solution.de
 * @copyright 2006 - 2007 Henri Schmidhuber
 * @link http://www.in-solution.de
 * @link http://www.oscommerce.com
 * @link http://www.sofort-ueberweisung.de
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 2 of the License
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 *
 ***********************************************************************************
 * this file contains code based on:
 * (c) 2000 - 2001 The Exchange Project
 * (c) 2001 - 2006 osCommerce, Open Source E-Commerce Solutions
 * Released under the GNU General Public License
 ***********************************************************************************
 *
 */


require ('includes/application_top.php');

// Check if Order exists
$order_id = tep_db_prepare_input($_REQUEST['kunden_var_0']);
$customer_id = tep_db_prepare_input($_REQUEST['kunden_var_1']);
$pw = tep_db_prepare_input($_REQUEST['pw']);

if (empty($order_id) || empty($customer_id) || empty($pw)) {
  exit();
}
$comment = '';
$error = false;

if (defined(MODULE_PAYMENT_SOFORTUEBERWEISUNGVORKASSE_BNA_PASSWORT) && strlen(MODULE_PAYMENT_SOFORTUEBERWEISUNGVORKASSE_BNA_PASSWORT) > 0) {
  if ($pw != MODULE_PAYMENT_SOFORTUEBERWEISUNGVORKASSE_BNA_PASSWORT) {
    $comment = 'ungültiges Benachrichtigung Passwort' . "\n";
    $error = true;
  }
}
if (defined(MODULE_PAYMENT_SOFORTUEBERWEISUNG_BNA_PASSWORT) &&  strlen(MODULE_PAYMENT_SOFORTUEBERWEISUNG_BNA_PASSWORT) > 0) {
  if($pw != MODULE_PAYMENT_SOFORTUEBERWEISUNG_BNA_PASSWORT) {
    $comment = 'ungültiges Benachrichtigung Passwort' . "\n";
    $error = true;
  }
}

// check if order exists
$order_query = tep_db_query("select * from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "' and customers_id = '" . (int)$customer_id . "'");
if (tep_db_num_rows($order_query) > 0) {
  $order = tep_db_fetch_array($order_query);
  // get total value
  $total_query = tep_db_query("select value from " . TABLE_ORDERS_TOTAL . " where orders_id = '" .  (int)$order_id . "' and class = 'ot_total' limit 1");
  $total = tep_db_fetch_array($total_query);
  // update order if total = received money

  // Valid returns in $_REQUEST['betrag'] are i.e. 13.12 or 13,12 changes sometimes
  if (number_format($total['value'] * $currencies->get_value('EUR'), 2, '.','') == $_REQUEST['betrag'] || number_format($total['value'] * $currencies->get_value('EUR'), 2, '.','') == $_REQUEST['betrag']) {
    $comment = 'Zahlung eingegangen';
  } else {
    $comment = "Sofortüberweisungs Transaktionscheck fehlgeschlagen. Bitte manuell überprüfen\n" . $_REQUEST['betrag'] .'!=' . $total['value'];
    $error = true;
  }

  if (MODULE_PAYMENT_SOFORTUEBERWEISUNG_STORE_TRANSACTION_DETAILS == 'True' || MODULE_PAYMENT_SOFORTUEBERWEISUNGVORKASSE_STORE_TRANSACTION_DETAILS == 'True') {
    $comment .= "\n" . serialize($_REQUEST);
  }

  $order_status = $order['orders_status'];
  if ((int)MODULE_PAYMENT_SOFORTUEBERWEISUNG_ORDER_STATUS_ID > 0 && $error == false) {
    $order_status = MODULE_PAYMENT_SOFORTUEBERWEISUNG_ORDER_STATUS_ID;
  } elseif ((int)MODULE_PAYMENT_SOFORTUEBERWEISUNGVORKASSE_ORDER_STATUS_ID > 0 && $error == false) {
    $order_status = MODULE_PAYMENT_SOFORTUEBERWEISUNGVORKASSE_ORDER_STATUS_ID;
  }

  $sql_data_array = array('orders_id' => (int)$order_id,
                          'orders_status_id' => $order_status,
                          'date_added' => 'now()',
                          'customer_notified' => '0',
                          'comments' => $comment);
  tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
  tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . $order_status . "', last_modified = now() where orders_id = '" . (int)$order_id . "'");

}
?>