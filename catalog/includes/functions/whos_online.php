<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTTP;
  use OSC\OM\Registry;

  function tep_update_whos_online() {
    $OSCOM_Db = Registry::get('Db');

    $wo_customer_id = 0;
    $wo_full_name = 'Guest';

    if (isset($_SESSION['customer_id'])) {
      $wo_customer_id = $_SESSION['customer_id'];

      $Qcustomer = $OSCOM_Db->prepare('select customers_firstname, customers_lastname from :table_customers where customers_id = :customers_id');
      $Qcustomer->bindInt(':customers_id', $_SESSION['customer_id']);
      $Qcustomer->execute();

      $wo_full_name = $Qcustomer->value('customers_firstname') . ' ' . $Qcustomer->value('customers_lastname');
    }

    $wo_session_id = session_id();
    $wo_ip_address = HTTP::getIpAddress();

    if (is_null($wo_ip_address)) { // database table field (ip_address) is not_null
      $wo_ip_address = '';
    }

    $wo_last_page_url = '';

    if (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI']) ) {
      $wo_last_page_url = $_SERVER['REQUEST_URI'];
    }

    $current_time = time();
    $xx_mins_ago = ($current_time - 900);

// remove entries that have expired
    $Qdel = $OSCOM_Db->prepare('delete from :table_whos_online where time_last_click < :time_last_click');
    $Qdel->bindInt(':time_last_click', $xx_mins_ago);
    $Qdel->execute();

    $Qsession = $OSCOM_Db->prepare('select session_id from :table_whos_online where session_id = :session_id limit 1');
    $Qsession->bindValue(':session_id', $wo_session_id);
    $Qsession->execute();

    if ($Qsession->fetch() !== false) {
      $OSCOM_Db->save('whos_online', ['customer_id' => $wo_customer_id, 'full_name' => $wo_full_name, 'ip_address' => $wo_ip_address, 'time_last_click' => $current_time, 'last_page_url' => $wo_last_page_url], ['session_id' => $wo_session_id]);
    } else {
      $OSCOM_Db->save('whos_online', ['customer_id' => $wo_customer_id, 'full_name' => $wo_full_name, 'session_id' => $wo_session_id, 'ip_address' => $wo_ip_address, 'time_entry' => $current_time, 'time_last_click' => $current_time, 'last_page_url' => $wo_last_page_url]);
    }
  }

  function tep_whos_online_update_session_id($old_id, $new_id) {
    $OSCOM_Db = Registry::get('Db');

    $OSCOM_Db->save('whos_online', ['session_id' => $new_id], ['session_id' => $old_id]);
  }
?>
