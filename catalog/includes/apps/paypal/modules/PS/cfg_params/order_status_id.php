<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_PS_Cfg_order_status_id {
    var $default = '0';

    function getSetField() {
      global $languages_id;

      $statuses_array = array(array('id' => '0', 'text' => TEXT_DEFAULT));
      $statuses_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$languages_id . "' order by orders_status_name");
      while ($statuses = tep_db_fetch_array($statuses_query)) {
        $statuses_array[] = array('id' => $statuses['orders_status_id'],
                                  'text' => $statuses['orders_status_name']);
      }

      $input = tep_draw_pull_down_menu('order_status_id', $statuses_array, OSCOM_APP_PAYPAL_PS_ORDER_STATUS_ID, 'id="inputPsOrderStatusId"');

      $result = <<<EOT
<div>
  <p>
    <label for="inputPsOrderStatusId">Order Status</label>

    Set this to the order status level that is assigned to new orders.
  </p>

  <div>
    {$input}
  </div>
</div>
EOT;

      return $result;
    }
  }
?>
