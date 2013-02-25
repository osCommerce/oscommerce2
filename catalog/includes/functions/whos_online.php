<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  function osc_update_whos_online() {
    if (isset($_SESSION['customer_id'])) {
      $wo_customer_id = $_SESSION['customer_id'];

      $customer_query = osc_db_query("select customers_firstname, customers_lastname from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$_SESSION['customer_id'] . "'");
      $customer = osc_db_fetch_array($customer_query);

      $wo_full_name = $customer['customers_firstname'] . ' ' . $customer['customers_lastname'];
    } else {
      $wo_customer_id = '';
      $wo_full_name = 'Guest';
    }

    $wo_session_id = session_id();
    $wo_ip_address = getenv('REMOTE_ADDR');
    $wo_last_page_url = getenv('REQUEST_URI');

    $current_time = time();
    $xx_mins_ago = ($current_time - 900);

// remove entries that have expired
    osc_db_query("delete from " . TABLE_WHOS_ONLINE . " where time_last_click < '" . $xx_mins_ago . "'");

    $stored_customer_query = osc_db_query("select count(*) as count from " . TABLE_WHOS_ONLINE . " where session_id = '" . osc_db_input($wo_session_id) . "'");
    $stored_customer = osc_db_fetch_array($stored_customer_query);

    if ($stored_customer['count'] > 0) {
      osc_db_query("update " . TABLE_WHOS_ONLINE . " set customer_id = '" . (int)$wo_customer_id . "', full_name = '" . osc_db_input($wo_full_name) . "', ip_address = '" . osc_db_input($wo_ip_address) . "', time_last_click = '" . osc_db_input($current_time) . "', last_page_url = '" . osc_db_input($wo_last_page_url) . "' where session_id = '" . osc_db_input($wo_session_id) . "'");
    } else {
      osc_db_query("insert into " . TABLE_WHOS_ONLINE . " (customer_id, full_name, session_id, ip_address, time_entry, time_last_click, last_page_url) values ('" . (int)$wo_customer_id . "', '" . osc_db_input($wo_full_name) . "', '" . osc_db_input($wo_session_id) . "', '" . osc_db_input($wo_ip_address) . "', '" . osc_db_input($current_time) . "', '" . osc_db_input($current_time) . "', '" . osc_db_input($wo_last_page_url) . "')");
    }
  }
?>
