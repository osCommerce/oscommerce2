<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_Braintree_Cfg_transactions_order_status_id {
    var $default = '0';
    var $title;
    var $description;
    var $sort_order = 200;

    function OSCOM_Braintree_Cfg_transactions_order_status_id() {
      global $OSCOM_Braintree;

      $this->title = $OSCOM_Braintree->getDef('cfg_transactions_order_status_id_title');
      $this->description = $OSCOM_Braintree->getDef('cfg_transactions_order_status_id_desc');
    }

    function getSetField() {
      global $languages_id;

      $statuses_array = array();

      $flags_query = tep_db_query("describe " . TABLE_ORDERS_STATUS . " public_flag");

      if (tep_db_num_rows($flags_query) == 1) {
        $statuses_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$languages_id . "' and public_flag = '0' order by orders_status_name");
      } else {
        $statuses_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$languages_id . "' order by orders_status_name");
      }

      while ($statuses = tep_db_fetch_array($statuses_query)) {
        $statuses_array[] = array('id' => $statuses['orders_status_id'],
                                  'text' => $statuses['orders_status_name']);
      }

      $input = tep_draw_pull_down_menu('transactions_order_status_id', $statuses_array, OSCOM_APP_PAYPAL_BRAINTREE_TRANSACTIONS_ORDER_STATUS_ID, 'id="inputTransactionsOrderStatusId"');

      $result = <<<EOT
<div>
  <p>
    <label for="inputTransactionsOrderStatusId">{$this->title}</label>

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
