<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2008 osCommerce

  Released under the GNU General Public License
*/

  class pm2checkout {
    var $code, $title, $description, $enabled;

// class constructor
    function pm2checkout() {
      global $order;

      $this->signature = '2checkout|pm2checkout|1.2|2.2';

      $this->code = 'pm2checkout';
      $this->title = MODULE_PAYMENT_2CHECKOUT_TEXT_TITLE;
      $this->public_title = MODULE_PAYMENT_2CHECKOUT_TEXT_PUBLIC_TITLE;
      $this->description = MODULE_PAYMENT_2CHECKOUT_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_2CHECKOUT_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_2CHECKOUT_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_2CHECKOUT_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_2CHECKOUT_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();

      $this->form_action_url = 'https://www.2checkout.com/2co/buyer/purchase';
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_2CHECKOUT_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_2CHECKOUT_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
        while ($check = tep_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $order->billing['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }
    }

    function javascript_validation() {
      return false;
    }

    function selection() {
      return array('id' => $this->code,
                   'module' => $this->public_title . (strlen(MODULE_PAYMENT_2CHECKOUT_TEXT_PUBLIC_DESCRIPTION) > 0 ? ' (' . MODULE_PAYMENT_2CHECKOUT_TEXT_PUBLIC_DESCRIPTION . ')' : ''));
    }

    function pre_confirmation_check() {
      if (MODULE_PAYMENT_2CHECKOUT_ROUTINE == 'Single-Page') {
        $this->form_action_url = 'https://www.2checkout.com/checkout/spurchase';
      }
    }

    function confirmation() {
      return false;
    }

    function process_button() {
      global $HTTP_POST_VARS, $customer_id, $currencies, $currency, $order, $languages_id, $cartID;

      $process_button_string = tep_draw_hidden_field('sid', MODULE_PAYMENT_2CHECKOUT_LOGIN) .
                               tep_draw_hidden_field('total', $this->format_raw($order->info['total'], MODULE_PAYMENT_2CHECKOUT_CURRENCY)) .
                               tep_draw_hidden_field('cart_order_id', date('YmdHis') . '-' . $customer_id . '-' . $cartID) .
                               tep_draw_hidden_field('fixed', 'Y') .
                               tep_draw_hidden_field('first_name', $order->billing['firstname']) .
                               tep_draw_hidden_field('last_name', $order->billing['lastname']) .
                               tep_draw_hidden_field('street_address', $order->billing['street_address']) .
                               tep_draw_hidden_field('city', $order->billing['city']) .
                               tep_draw_hidden_field('state', $order->billing['state']) .
                               tep_draw_hidden_field('zip', $order->billing['postcode']) .
                               tep_draw_hidden_field('country', $order->billing['country']['title']) .
                               tep_draw_hidden_field('email', $order->customer['email_address']) .
                               tep_draw_hidden_field('phone', $order->customer['telephone']) .
                               tep_draw_hidden_field('ship_name', $order->delivery['firstname'] . ' ' . $order->delivery['lastname']) .
                               tep_draw_hidden_field('ship_street_address', $order->delivery['street_address']) .
                               tep_draw_hidden_field('ship_city', $order->delivery['city']) .
                               tep_draw_hidden_field('ship_state', $order->delivery['state']) .
                               tep_draw_hidden_field('ship_zip', $order->delivery['postcode']) .
                               tep_draw_hidden_field('ship_country', $order->delivery['country']['title']);

      for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
        $process_button_string .= tep_draw_hidden_field('c_prod_' . ($i+1), (int)$order->products[$i]['id'] . ',' . (int)$order->products[$i]['qty']) .
                                  tep_draw_hidden_field('c_name_' . ($i+1), $order->products[$i]['name']) .
                                  tep_draw_hidden_field('c_description_' . ($i+1), $order->products[$i]['name']) .
                                  tep_draw_hidden_field('c_price_' . ($i+1), $this->format_raw(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), MODULE_PAYMENT_2CHECKOUT_CURRENCY));
      }

      $process_button_string .= tep_draw_hidden_field('id_type', '1') .
                                tep_draw_hidden_field('skip_landing', '1');

      if (MODULE_PAYMENT_2CHECKOUT_TESTMODE == 'Test') {
        $process_button_string .= tep_draw_hidden_field('demo', 'Y');
      }

      $process_button_string .= tep_draw_hidden_field('return_url', tep_href_link(FILENAME_SHOPPING_CART));

      $lang_query = tep_db_query("select code from " . TABLE_LANGUAGES . " where languages_id = '" . (int)$languages_id . "'");
      $lang = tep_db_fetch_array($lang_query);

      switch (strtolower($lang['code'])) {
        case 'es':
          $process_button_string .= tep_draw_hidden_field('lang', 'sp');
          break;
      }

      $process_button_string .= tep_draw_hidden_field('cart_brand_name', 'oscommerce') .
                                tep_draw_hidden_field('cart_version_name', PROJECT_VERSION);

      return $process_button_string;
    }

    function before_process() {
      global $HTTP_POST_VARS;

      if ( ($HTTP_POST_VARS['credit_card_processed'] != 'Y') && ($HTTP_POST_VARS['credit_card_processed'] != 'K') ){
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code, 'SSL', true, false));
      }
    }

    function after_process() {
      global $HTTP_POST_VARS, $order, $insert_id;

      if (MODULE_PAYMENT_2CHECKOUT_TESTMODE == 'Test') {
        $sql_data_array = array('orders_id' => (int)$insert_id, 
                                'orders_status_id' => (int)$order->info['order_status'], 
                                'date_added' => 'now()', 
                                'customer_notified' => '0',
                                'comments' => MODULE_PAYMENT_2CHECKOUT_TEXT_WARNING_DEMO_MODE);

        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
      }

// The KEY value returned from the gateway is intentionally broken for Test transactions so it is only checked in Production mode
      if (tep_not_null(MODULE_PAYMENT_2CHECKOUT_SECRET_WORD) && (MODULE_PAYMENT_2CHECKOUT_TESTMODE == 'Production')) {
        if (strtoupper(md5(MODULE_PAYMENT_2CHECKOUT_SECRET_WORD . MODULE_PAYMENT_2CHECKOUT_LOGIN . $HTTP_POST_VARS['order_number'] . $this->order_format($order->info['total'], MODULE_PAYMENT_2CHECKOUT_CURRENCY))) != strtoupper($HTTP_POST_VARS['key'])) {
          $sql_data_array = array('orders_id' => (int)$insert_id, 
                                  'orders_status_id' => (int)$order->info['order_status'], 
                                  'date_added' => 'now()', 
                                  'customer_notified' => '0',
                                  'comments' => MODULE_PAYMENT_2CHECKOUT_TEXT_WARNING_TRANSACTION_ORDER);

          tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        }
      }
    }

    function get_error() {
      $error = array('title' => '',
                     'error' => MODULE_PAYMENT_2CHECKOUT_TEXT_ERROR_MESSAGE);

      return $error;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_2CHECKOUT_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable 2Checkout', 'MODULE_PAYMENT_2CHECKOUT_STATUS', 'False', 'Do you want to accept 2CheckOut payments?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Vendor Account', 'MODULE_PAYMENT_2CHECKOUT_LOGIN', '', 'The vendor account number for the 2Checkout gateway.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Mode', 'MODULE_PAYMENT_2CHECKOUT_TESTMODE', 'Test', 'Transaction mode used for the 2Checkout gateway.', '6', '0', 'tep_cfg_select_option(array(\'Test\', \'Production\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Secret Word', 'MODULE_PAYMENT_2CHECKOUT_SECRET_WORD', '', 'The secret word to confirm transactions with. (Must be the same as defined on the Vendor Admin interface)', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Payment Routine', 'MODULE_PAYMENT_2CHECKOUT_ROUTINE', 'Multi-Page', 'The payment routine to use on the 2Checkout gateway.', '6', '0', 'tep_cfg_select_option(array(\'Multi-Page\', \'Single-Page\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Processing Currency', 'MODULE_PAYMENT_2CHECKOUT_CURRENCY', '" . DEFAULT_CURRENCY . "', 'The currency to process transactions in. (Must be the same as defined on the Vendor Admin interface)', '6', '0', 'pm2checkout::getCurrencies(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_PAYMENT_2CHECKOUT_SORT_ORDER', '0', 'Sort order of display. (Lowest is displayed first)', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_2CHECKOUT_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_2CHECKOUT_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value.', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_2CHECKOUT_STATUS', 'MODULE_PAYMENT_2CHECKOUT_LOGIN', 'MODULE_PAYMENT_2CHECKOUT_TESTMODE', 'MODULE_PAYMENT_2CHECKOUT_SECRET_WORD', 'MODULE_PAYMENT_2CHECKOUT_ROUTINE', 'MODULE_PAYMENT_2CHECKOUT_CURRENCY', 'MODULE_PAYMENT_2CHECKOUT_ZONE', 'MODULE_PAYMENT_2CHECKOUT_ORDER_STATUS_ID', 'MODULE_PAYMENT_2CHECKOUT_SORT_ORDER');
    }

// format prices without currency formatting
    function format_raw($number, $currency_code = '', $currency_value = '') {
      global $currencies, $currency;

      if (empty($currency_code) || !$currencies->is_set($currency_code)) {
        $currency_code = $currency;
      }

      if (empty($currency_value) || !is_numeric($currency_value)) {
        $currency_value = $currencies->currencies[$currency_code]['value'];
      }

      return number_format(tep_round($number * $currency_value, $currencies->currencies[$currency_code]['decimal_places']), $currencies->currencies[$currency_code]['decimal_places'], '.', '');
    }

    function getCurrencies($value, $key = '') {
      $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

      $currencies_array = array();

      $currencies_query = tep_db_query("select code, title from " . TABLE_CURRENCIES . " order by title");
      while ($currencies = tep_db_fetch_array($currencies_query)) {
        $currencies_array[] = array('id' => $currencies['code'],
                                    'text' => $currencies['title']);
      }

      return tep_draw_pull_down_menu($name, $currencies_array, $value);
    }
  }
?>
