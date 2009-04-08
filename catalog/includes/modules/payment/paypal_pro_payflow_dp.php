<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2009 osCommerce

  Released under the GNU General Public License
*/

  class paypal_pro_payflow_dp {
    var $code, $title, $description, $enabled;

// class constructor
    function paypal_pro_payflow_dp() {
      global $order;

      $this->signature = 'paypal|paypal_pro_payflow_dp|1.2|2.2';

      $this->code = 'paypal_pro_payflow_dp';
      $this->title = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TEXT_TITLE;
      $this->public_title = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TEXT_PUBLIC_TITLE;
      $this->description = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();

      $this->cc_types = array('0' => 'Visa',
                              '1' => 'MasterCard',
                              '9' => 'Maestro',
                              'S' => 'Solo');
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
        while ($check = tep_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $order->delivery['zone_id']) {
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
      $selection = array('id' => $this->code,
                         'module' => $this->public_title);

      if (MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_INPUT_PAGE == 'Payment') {
        global $order;

        $types_array = array();
        while (list($key, $value) = each($this->cc_types)) {
          $types_array[] = array('id' => $key,
                                 'text' => $value);
        }

        $today = getdate();

        $months_array = array();
        for ($i=1; $i<13; $i++) {
          $months_array[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B',mktime(0,0,0,$i,1,2000)));
        }

        $year_valid_from_array = array();
        for ($i=$today['year']-10; $i < $today['year']+1; $i++) {
          $year_valid_from_array[] = array('id' => strftime('%y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
        }

        $year_expires_array = array();
        for ($i=$today['year']; $i < $today['year']+10; $i++) {
          $year_expires_array[] = array('id' => strftime('%y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
        }

        $selection['fields'] = array(array('title' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_OWNER_FIRSTNAME,
                                           'field' => tep_draw_input_field('cc_owner_firstname', $order->billing['firstname'])),
                                     array('title' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_OWNER_LASTNAME,
                                           'field' => tep_draw_input_field('cc_owner_lastname', $order->billing['lastname'])),
                                     array('title' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_TYPE,
                                           'field' => tep_draw_pull_down_menu('cc_type', $types_array)),
                                     array('title' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_NUMBER,
                                           'field' => tep_draw_input_field('cc_number_nh-dns')),
                                     array('title' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_VALID_FROM,
                                           'field' => tep_draw_pull_down_menu('cc_starts_month', $months_array) . '&nbsp;' . tep_draw_pull_down_menu('cc_starts_year', $year_valid_from_array) . ' ' . MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_VALID_FROM_INFO),
                                     array('title' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_EXPIRES,
                                           'field' => tep_draw_pull_down_menu('cc_expires_month', $months_array) . '&nbsp;' . tep_draw_pull_down_menu('cc_expires_year', $year_expires_array)),
                                     array('title' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_CVC,
                                           'field' => tep_draw_input_field('cc_cvc_nh-dns', '', 'size="5" maxlength="4"')),
                                     array('title' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_ISSUE_NUMBER,
                                           'field' => tep_draw_input_field('cc_issue_nh-dns', '', 'size="3" maxlength="2"') . ' ' . MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_ISSUE_NUMBER_INFO));
      }

      return $selection;
    }

    function pre_confirmation_check() {
      if (MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_INPUT_PAGE == 'Payment') {
        global $HTTP_POST_VARS;

        if (!isset($HTTP_POST_VARS['cc_owner_firstname']) || empty($HTTP_POST_VARS['cc_owner_firstname']) || !isset($HTTP_POST_VARS['cc_owner_lastname']) || empty($HTTP_POST_VARS['cc_owner_lastname']) || (strlen($HTTP_POST_VARS['cc_owner_firstname'] . ' ' . $HTTP_POST_VARS['cc_owner_lastname']) < CC_OWNER_MIN_LENGTH) || !isset($HTTP_POST_VARS['cc_type']) || !isset($this->cc_types[$HTTP_POST_VARS['cc_type']]) || !isset($HTTP_POST_VARS['cc_number_nh-dns']) || empty($HTTP_POST_VARS['cc_number_nh-dns']) || (strlen($HTTP_POST_VARS['cc_number_nh-dns']) < CC_NUMBER_MIN_LENGTH)) {
          $payment_error_return = 'payment_error=' . $this->code . '&error=' . urlencode(MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ERROR_ALL_FIELDS_REQUIRED) . '&cc_owner_firstname=' . urlencode($HTTP_POST_VARS['cc_owner_firstname']) . '&cc_owner_lastname=' . urlencode($HTTP_POST_VARS['cc_owner_lastname']) . '&cc_starts_month=' . $HTTP_POST_VARS['cc_starts_month'] . '&cc_starts_year=' . $HTTP_POST_VARS['cc_starts_year'] . '&cc_expires_month=' . $HTTP_POST_VARS['cc_expires_month'] . '&cc_expires_year=' . $HTTP_POST_VARS['cc_expires_year'];

          tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
        }
      }

      return false;
    }

    function confirmation() {
      $confirmation = array();

      if (MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_INPUT_PAGE == 'Payment') {
        global $HTTP_POST_VARS;

        $confirmation['fields'] = array(array('title' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_OWNER,
                                              'field' => $HTTP_POST_VARS['cc_owner_firstname'] . ' ' . $HTTP_POST_VARS['cc_owner_lastname']),
                                        array('title' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_TYPE,
                                              'field' => $this->cc_types[$HTTP_POST_VARS['cc_type']]),
                                        array('title' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_NUMBER,
                                              'field' => str_repeat('X', strlen($HTTP_POST_VARS['cc_number_nh-dns']) - 4) . substr($HTTP_POST_VARS['cc_number_nh-dns'], -4)),
                                        array('title' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_VALID_FROM,
                                              'field' => $HTTP_POST_VARS['cc_starts_month'] . '/' . $HTTP_POST_VARS['cc_starts_year']),
                                        array('title' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_EXPIRES,
                                              'field' => $HTTP_POST_VARS['cc_expires_month'] . '/' . $HTTP_POST_VARS['cc_expires_year']),
                                        array('title' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_CVC,
                                              'field' => $HTTP_POST_VARS['cc_cvc_nh-dns']));

        if (isset($HTTP_POST_VARS['cc_issue_nh-dns']) && !empty($HTTP_POST_VARS['cc_issue_nh-dns'])) {
          $confirmation['fields'][] = array('title' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_ISSUE_NUMBER,
                                            'field' => $HTTP_POST_VARS['cc_issue_nh-dns']);
        }
      } else {
        global $order;

        $types_array = array();
        while (list($key, $value) = each($this->cc_types)) {
          $types_array[] = array('id' => $key,
                                 'text' => $value);
        }

        $today = getdate();

        $months_array = array();
        for ($i=1; $i<13; $i++) {
          $months_array[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B',mktime(0,0,0,$i,1,2000)));
        }

        $year_valid_from_array = array();
        for ($i=$today['year']-10; $i < $today['year']+1; $i++) {
          $year_valid_from_array[] = array('id' => strftime('%y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
        }

        $year_expires_array = array();
        for ($i=$today['year']; $i < $today['year']+10; $i++) {
          $year_expires_array[] = array('id' => strftime('%y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
        }

        $confirmation['fields'] = array(array('title' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_OWNER_FIRSTNAME,
                                              'field' => tep_draw_input_field('cc_owner_firstname', $order->billing['firstname'])),
                                        array('title' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_OWNER_LASTNAME,
                                              'field' => tep_draw_input_field('cc_owner_lastname', $order->billing['lastname'])),
                                        array('title' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_TYPE,
                                              'field' => tep_draw_pull_down_menu('cc_type', $types_array)),
                                        array('title' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_NUMBER,
                                              'field' => tep_draw_input_field('cc_number_nh-dns')),
                                        array('title' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_VALID_FROM,
                                              'field' => tep_draw_pull_down_menu('cc_starts_month', $months_array) . '&nbsp;' . tep_draw_pull_down_menu('cc_starts_year', $year_valid_from_array) . ' ' . MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_VALID_FROM_INFO),
                                        array('title' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_EXPIRES,
                                              'field' => tep_draw_pull_down_menu('cc_expires_month', $months_array) . '&nbsp;' . tep_draw_pull_down_menu('cc_expires_year', $year_expires_array)),
                                        array('title' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_CVC,
                                              'field' => tep_draw_input_field('cc_cvc_nh-dns', '', 'size="5" maxlength="4"')),
                                        array('title' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_ISSUE_NUMBER,
                                              'field' => tep_draw_input_field('cc_issue_nh-dns', '', 'size="3" maxlength="2"') . ' ' . MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_ISSUE_NUMBER_INFO));
      }

      return $confirmation;
    }

    function process_button() {
      if (MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_INPUT_PAGE == 'Payment') {
        global $HTTP_POST_VARS;

        $process_button_string = tep_draw_hidden_field('cc_owner_firstname', $HTTP_POST_VARS['cc_owner_firstname']) .
                                 tep_draw_hidden_field('cc_owner_lastname', $HTTP_POST_VARS['cc_owner_lastname']) .
                                 tep_draw_hidden_field('cc_type', $HTTP_POST_VARS['cc_type']) .
                                 tep_draw_hidden_field('cc_number_nh-dns', $HTTP_POST_VARS['cc_number_nh-dns']) .
                                 tep_draw_hidden_field('cc_starts_month', $HTTP_POST_VARS['cc_starts_month']) .
                                 tep_draw_hidden_field('cc_starts_year', $HTTP_POST_VARS['cc_starts_year']) .
                                 tep_draw_hidden_field('cc_expires_month', $HTTP_POST_VARS['cc_expires_month']) .
                                 tep_draw_hidden_field('cc_expires_year', $HTTP_POST_VARS['cc_expires_year']) .
                                 tep_draw_hidden_field('cc_cvc_nh-dns', $HTTP_POST_VARS['cc_cvc_nh-dns']);

        if (isset($HTTP_POST_VARS['cc_issue_nh-dns']) && !empty($HTTP_POST_VARS['cc_issue_nh-dns'])) {
          $process_button_string .= tep_draw_hidden_field('cc_issue_nh-dns', $HTTP_POST_VARS['cc_issue_nh-dns']);
        }

        return $process_button_string;
      }

      return false;
    }

    function before_process() {
      global $HTTP_POST_VARS, $order, $sendto;

      if (isset($HTTP_POST_VARS['cc_owner_firstname']) && !empty($HTTP_POST_VARS['cc_owner_firstname']) && isset($HTTP_POST_VARS['cc_owner_lastname']) && !empty($HTTP_POST_VARS['cc_owner_lastname']) && isset($HTTP_POST_VARS['cc_type']) && isset($this->cc_types[$HTTP_POST_VARS['cc_type']]) && isset($HTTP_POST_VARS['cc_number_nh-dns']) && !empty($HTTP_POST_VARS['cc_number_nh-dns'])) {
        if (MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_SERVER == 'Live') {
          $api_url = 'https://payflowpro.verisign.com/transaction';
        } else {
          $api_url = 'https://pilot-payflowpro.verisign.com/transaction';
        }

        $name = explode(' ', $HTTP_POST_VARS['cc_owner'], 2);

        $params = array('USER' => (tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_USERNAME) ? MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_USERNAME : MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_VENDOR),
                        'VENDOR' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_VENDOR,
                        'PARTNER' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PARTNER,
                        'PWD' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PASSWORD,
                        'TENDER' => 'C',
                        'TRXTYPE' => ((MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_METHOD == 'Sale') ? 'S' : 'A'),
                        'AMT' => $this->format_raw($order->info['total']),
                        'CURRENCY' => $order->info['currency'],
                        'FIRSTNAME' => $HTTP_POST_VARS['cc_owner_firstname'],
                        'LASTNAME' => $HTTP_POST_VARS['cc_owner_lastname'],
                        'STREET' => $order->billing['street_address'],
                        'CITY' => $order->billing['city'],
                        'STATE' => tep_get_zone_code($order->billing['country']['id'], $order->billing['zone_id'], $order->billing['state']),
                        'COUNTRY' => $order->billing['country']['iso_code_2'],
                        'ZIP' => $order->billing['postcode'],
                        'CLIENTIP' => tep_get_ip_address(),
                        'EMAIL' => $order->customer['email_address'],
                        'ACCT' => $HTTP_POST_VARS['cc_number_nh-dns'],
                        'ACCTTYPE' => $HTTP_POST_VARS['cc_type'],
                        'CARDSTART' => $HTTP_POST_VARS['cc_starts_month'] . $HTTP_POST_VARS['cc_starts_year'],
                        'EXPDATE' => $HTTP_POST_VARS['cc_expires_month'] . $HTTP_POST_VARS['cc_expires_year'],
                        'CVV2' => $HTTP_POST_VARS['cc_cvc_nh-dns'],
                        'BUTTONSOURCE' => 'osCommerce22_Default_PRO2DP');

        if ( ($HTTP_POST_VARS['cc_type'] == '9') || ($HTTP_POST_VARS['cc_type'] == 'S') ) {
          $params['CARDISSUE'] = $HTTP_POST_VARS['cc_issue_nh-dns'];
        }

        if (is_numeric($sendto) && ($sendto > 0)) {
          $params['SHIPTOFIRSTNAME'] = $order->delivery['firstname'];
          $params['SHIPTOLASTNAME'] = $order->delivery['lastname'];
          $params['SHIPTOSTREET'] = $order->delivery['street_address'];
          $params['SHIPTOCITY'] = $order->delivery['city'];
          $params['SHIPTOSTATE'] = tep_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']);
          $params['SHIPTOCOUNTRY'] = $order->delivery['country']['iso_code_2'];
          $params['SHIPTOZIP'] = $order->delivery['postcode'];
        }

        $post_string = '';

        foreach ($params as $key => $value) {
          $post_string .= $key . '[' . strlen(urlencode(utf8_encode(trim($value)))) . ']=' . urlencode(utf8_encode(trim($value))) . '&';
        }

        $post_string = substr($post_string, 0, -1);

        $response = $this->sendTransactionToGateway($api_url, $post_string, array('X-VPS-REQUEST-ID: ' . md5($cartID . tep_session_id() . rand())));
        $response_array = array();
        parse_str($response, $response_array);

        if ($response_array['RESULT'] != '0') {
          switch ($response_array['RESULT']) {
            case '1':
            case '26':
              $error_message = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ERROR_CFG_ERROR;
              break;

            case '7':
              $error_message = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ERROR_ADDRESS;
              break;

            case '12':
              $error_message = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ERROR_DECLINED;
              break;

            case '23':
            case '24':
              $error_message = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ERROR_INVALID_CREDIT_CARD;
              break;

            default:
              $error_message = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ERROR_GENERAL;
              break;
          }

          tep_redirect(tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, 'error_message=' . urlencode($error_message), 'SSL'));
        }
      } else {
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, 'error_message=' . MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ERROR_ALL_FIELDS_REQUIRED, 'SSL'));
      }
    }

    function after_process() {
      return false;
    }

    function get_error() {
      if (MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_INPUT_PAGE == 'Payment') {
        global $HTTP_GET_VARS;

        $error = array('error' => stripslashes(urldecode($HTTP_GET_VARS['error'])));

        return $error;
      }

      return false;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable PayPal Direct (UK)', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_STATUS', 'False', 'Do you want to accept PayPal Direct (UK) payments?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Vendor', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_VENDOR', '', 'Your merchant login ID that you created when you registered for the Website Payments Pro account.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('User', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_USERNAME', '', 'If you set up one or more additional users on the account, this value is the ID of the user authorised to process transactions. If, however, you have not set up additional users on the account, USER has the same value as VENDOR.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Password', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PASSWORD', '', 'The 6- to 32-character password that you defined while registering for the account.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Partner', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PARTNER', 'PayPalUK', 'The ID provided to you by the authorised PayPal Reseller who registered you for the Payflow SDK. If you purchased your account directly from PayPal, use PayPalUK.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Server', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_SERVER', 'Live', 'Use the live or testing (sandbox) gateway server to process transactions?', '6', '0', 'tep_cfg_select_option(array(\'Live\', \'Sandbox\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Method', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_METHOD', 'Sale', 'The processing method to use for each transaction.', '6', '0', 'tep_cfg_select_option(array(\'Authorization\', \'Sale\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Card Acceptance Page', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_INPUT_PAGE', 'Confirmation', 'The location to accept card information. Either on the Checkout Confirmation page or the Checkout Payment page.', '6', '0', 'tep_cfg_select_option(array(\'Confirmation\', \'Payment\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value.', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('cURL Program Location', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CURL', '/usr/bin/curl', 'The location to the cURL program application.', '6', '0' , now())");
   }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_STATUS', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_VENDOR', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_USERNAME', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PASSWORD', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PARTNER', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_SERVER', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_METHOD', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CARD_INPUT_PAGE', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ZONE', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ORDER_STATUS_ID', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_SORT_ORDER', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CURL');
    }

    function sendTransactionToGateway($url, $parameters, $headers = null) {
      $header = array();

      $server = parse_url($url);

      if (!isset($server['port'])) {
        $server['port'] = ($server['scheme'] == 'https') ? 443 : 80;
      }

      if (!isset($server['path'])) {
        $server['path'] = '/';
      }

      if (isset($server['user']) && isset($server['pass'])) {
        $header[] = 'Authorization: Basic ' . base64_encode($server['user'] . ':' . $server['pass']);
      }

      if (!empty($headers) && is_array($headers)) {
        $header = array_merge($header, $headers);
      }

      if (function_exists('curl_init')) {
        $curl = curl_init($server['scheme'] . '://' . $server['host'] . $server['path'] . (isset($server['query']) ? '?' . $server['query'] : ''));
        curl_setopt($curl, CURLOPT_PORT, $server['port']);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);

        if (!empty($header)) {
          curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }

        $result = curl_exec($curl);

        curl_close($curl);
      } else {
        exec(escapeshellarg(MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_CURL) . ' -d ' . escapeshellarg($parameters) . ' "' . $server['scheme'] . '://' . $server['host'] . $server['path'] . (isset($server['query']) ? '?' . $server['query'] : '') . '" -P ' . $server['port'] . ' -k' . (!empty($header) ? ' -H ' . escapeshellarg(implode("\r\n", $header)) : ''), $result);
        $result = implode("\n", $result);
      }

      return $result;
    }

// format prices without currency formatting
    function format_raw($number, $currency_code = '', $currency_value = '') {
      global $currencies, $currency;

      if (empty($currency_code) || !$this->is_set($currency_code)) {
        $currency_code = $currency;
      }

      if (empty($currency_value) || !is_numeric($currency_value)) {
        $currency_value = $currencies->currencies[$currency_code]['value'];
      }

      return number_format(tep_round($number * $currency_value, $currencies->currencies[$currency_code]['decimal_places']), $currencies->currencies[$currency_code]['decimal_places'], '.', '');
    }
  }
?>
