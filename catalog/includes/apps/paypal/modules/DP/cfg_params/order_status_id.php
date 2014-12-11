<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_DP_Cfg_order_status_id {
    var $default = '0';
    var $title;
    var $description;
    var $sort_order = 400;

    function OSCOM_PayPal_DP_Cfg_order_status_id() {
      global $OSCOM_PayPal;

      $this->title = $OSCOM_PayPal->getDef('cfg_dp_order_status_id_title');
      $this->description = $OSCOM_PayPal->getDef('cfg_dp_order_status_id_desc');
    }

    function getSetField() {
      global $OSCOM_PayPal, $languages_id;

      $statuses_array = array(array('id' => '0', 'text' => $OSCOM_PayPal->getDef('cfg_dp_order_status_id_default')));

      $statuses_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$languages_id . "' order by orders_status_name");
      while ($statuses = tep_db_fetch_array($statuses_query)) {
        $statuses_array[] = array('id' => $statuses['orders_status_id'],
                                  'text' => $statuses['orders_status_name']);
      }

      $input = tep_draw_pull_down_menu('order_status_id', $statuses_array, OSCOM_APP_PAYPAL_DP_ORDER_STATUS_ID, 'id="inputDpOrderStatusId"');

      $result = <<<EOT
<div>
  <p>
    <label for="inputDpOrderStatusId">{$this->title}</label>

    {$this->description}
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
