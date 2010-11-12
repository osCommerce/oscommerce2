<?php
/*
  $Id: $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2009 osCommerce

  Released under the GNU General Public License
*/

  class sage_pay_direct {
    var $code, $title, $description, $enabled;

// class constructor
    function sage_pay_direct() {
      global $order;

      $this->signature = 'sage_pay|sage_pay_direct|1.0|2.3';

      $this->code = 'sage_pay_direct';
      $this->title = MODULE_PAYMENT_SAGE_PAY_DIRECT_TEXT_TITLE;
      $this->public_title = MODULE_PAYMENT_SAGE_PAY_DIRECT_TEXT_PUBLIC_TITLE;
      $this->description = MODULE_PAYMENT_SAGE_PAY_DIRECT_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_SAGE_PAY_DIRECT_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_SAGE_PAY_DIRECT_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_SAGE_PAY_DIRECT_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_SAGE_PAY_DIRECT_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ($this->hasCards() == false) ) {
        $this->enabled = false;
      }

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_SAGE_PAY_DIRECT_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_SAGE_PAY_DIRECT_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
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
                   'module' => $this->public_title);
    }

    function pre_confirmation_check() {
      return false;
    }

    function confirmation() {
      global $order;

      $card_types = array();
      foreach ($this->getCardTypes() as $key => $value) {
        $card_types[] = array('id' => $key,
                              'text' => $value);
      }

      $today = getdate(); 

      $months_array = array();
      for ($i=1; $i<13; $i++) {
        $months_array[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B', mktime(0, 0, 0, $i, 1, 2000)));
      }

      $year_valid_to_array = array();
      for ($i=$today['year']; $i < $today['year']+10; $i++) {
        $year_valid_to_array[] = array('id' => strftime('%y', mktime(0, 0, 0, 1, 1, $i)), 'text' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)));
      }

      $year_valid_from_array = array();
      for ($i=$today['year']-4; $i < $today['year']+1; $i++) {
        $year_valid_from_array[] = array('id' => strftime('%y', mktime(0, 0, 0, 1, 1, $i)), 'text' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)));
      }

      $confirmation = array('fields' => array(array('title' => MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_TYPE,
                                                    'field' => tep_draw_pull_down_menu('cc_type', $card_types)),
                                              array('title' => MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_OWNER,
                                                    'field' => tep_draw_input_field('cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'], 'maxlength="50"')),
                                              array('title' => MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_NUMBER,
                                                    'field' => tep_draw_input_field('cc_number_nh-dns', '', 'maxlength="20"'))));

      if ( (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MAESTRO == 'True') || (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_SOLO == 'True') || (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_AMEX == 'True') ) {
        $confirmation['fields'][] = array('title' => MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_STARTS,
                                          'field' => tep_draw_pull_down_menu('cc_starts_month', $months_array) . '&nbsp;' . tep_draw_pull_down_menu('cc_starts_year', $year_valid_from_array) . '&nbsp;' . MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_STARTS_INFO);
      }

      $confirmation['fields'][] = array('title' => MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_EXPIRES,
                                        'field' => tep_draw_pull_down_menu('cc_expires_month', $months_array) . '&nbsp;' . tep_draw_pull_down_menu('cc_expires_year', $year_valid_to_array));

      if ( (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MAESTRO == 'True') || (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_SOLO == 'True') ) {
        $confirmation['fields'][] = array('title' => MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_ISSUE_NUMBER,
                                          'field' => tep_draw_input_field('cc_issue_nh-dns', '', 'size="3" maxlength="2"') . '&nbsp;' . MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_ISSUE_NUMBER_INFO);
      }

      if (MODULE_PAYMENT_SAGE_PAY_DIRECT_VERIFY_WITH_CVC == 'True') {
        $confirmation['fields'][] = array('title' => MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_CVC,
                                          'field' => tep_draw_input_field('cc_cvc_nh-dns', '', 'size="5" maxlength="4"'));
      }

      return $confirmation;
    }

    function process_button() {
      return false;
    }

    function before_process() {
      global $HTTP_GET_VARS, $HTTP_POST_VARS, $customer_id, $order, $currency, $order_totals, $cartID;

      $error = null;

      if (isset($HTTP_GET_VARS['check']) && ($HTTP_GET_VARS['check'] == '3D') && isset($HTTP_POST_VARS['MD']) && tep_not_null($HTTP_POST_VARS['MD']) && isset($HTTP_POST_VARS['PaRes']) && tep_not_null($HTTP_POST_VARS['PaRes'])) {
        switch (MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_SERVER) {
          case 'Live':
            $gateway_url = 'https://live.sagepay.com/gateway/service/direct3dcallback.vsp';
            break;

          case 'Test':
            $gateway_url = 'https://test.sagepay.com/gateway/service/direct3dcallback.vsp';
            break;

          default:
            $gateway_url = 'https://test.sagepay.com/Simulator/VSPDirectCallback.asp';
            break;
        }

        $post_string = 'MD=' . $HTTP_POST_VARS['MD'] . '&PARes=' . $HTTP_POST_VARS['PaRes'];

        $transaction_response = $this->sendTransactionToGateway($gateway_url, $post_string);
      } else {
        $cc_type = substr($HTTP_POST_VARS['cc_type'], 0, 15);
        $cc_owner = substr($HTTP_POST_VARS['cc_owner'], 0, 50);
        $cc_number = substr(preg_replace('/[^0-9]/', '', $HTTP_POST_VARS['cc_number_nh-dns']), 0, 20);
        $cc_start = null;
        $cc_expires = null;
        $cc_issue = null;
        $cc_cvc = null;

        $today = getdate(); 

        $months_array = array();
        for ($i=1; $i<13; $i++) {
          $months_array[] = sprintf('%02d', $i);
        }

        $year_valid_to_array = array();
        for ($i=$today['year']; $i < $today['year']+10; $i++) {
          $year_valid_to_array[] = strftime('%y',mktime(0,0,0,1,1,$i));
        }

        $year_valid_from_array = array();
        for ($i=$today['year']-4; $i < $today['year']+1; $i++) {
          $year_valid_from_array[] = strftime('%Y',mktime(0,0,0,1,1,$i));
        }

        if ( (isset($HTTP_POST_VARS['cc_type']) == false) || ($this->isCard($cc_type) == false) ) {
          tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=cardtype', 'SSL'));
        }

        if ( (isset($HTTP_POST_VARS['cc_owner']) == false) || empty($cc_owner) ) {
          tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=cardowner', 'SSL'));
        }

        if ( (isset($HTTP_POST_VARS['cc_number_nh-dns']) == false) || (is_numeric($cc_number) == false) ) {
          tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=cardnumber', 'SSL'));
        }

        if ( (($cc_type == 'MAESTRO') && (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MAESTRO == 'True')) || (($cc_type == 'SOLO') && (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_SOLO == 'True')) || (($cc_type == 'AMEX') && (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_AMEX == 'True')) ) {
          if ( in_array($HTTP_POST_VARS['cc_starts_month'], $months_array) == false ) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=cardstart', 'SSL'));
          }

          if ( in_array($HTTP_POST_VARS['cc_starts_year'], $year_valid_from_array) == false ) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=cardstart', 'SSL'));
          }

          $cc_start = substr($HTTP_POST_VARS['cc_starts_month'] . $HTTP_POST_VARS['cc_starts_year'], 0, 4);
        }

        if ( in_array($HTTP_POST_VARS['cc_expires_month'], $months_array) == false ) {
          tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=cardexpires', 'SSL'));
        }

        if ( in_array($HTTP_POST_VARS['cc_expires_year'], $year_valid_to_array) == false ) {
          tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=cardexpires', 'SSL'));
        }

        if ( ($HTTP_POST_VARS['cc_expires_year'] == date('y')) && ($HTTP_POST_VARS['cc_expires_month'] < date('m')) ) {
          tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=cardexpires', 'SSL'));
        }

        $cc_expires = substr($HTTP_POST_VARS['cc_expires_month'] . $HTTP_POST_VARS['cc_expires_year'], 0, 4);

        if ( (($cc_type == 'MAESTRO') && (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MAESTRO == 'True')) || (($cc_type == 'SOLO') && (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_SOLO == 'True')) ) {
          $cc_issue = substr($HTTP_POST_VARS['cc_issue_nh-dns'], 0, 2);

          if ( (isset($HTTP_POST_VARS['cc_issue_nh-dns']) == false) || empty($cc_issue) ) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=cardissue', 'SSL'));
          }
        }

        if (MODULE_PAYMENT_SAGE_PAY_DIRECT_VERIFY_WITH_CVC == 'True') {
          $cc_cvc = substr($HTTP_POST_VARS['cc_cvc_nh-dns'], 0, 4);

          if ( (isset($HTTP_POST_VARS['cc_cvc_nh-dns']) == false) || empty($cc_cvc) ) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=cardcvc', 'SSL'));
          }
        }

        $params = array('VPSProtocol' => '2.23',
                        'ReferrerID' => 'C74D7B82-E9EB-4FBD-93DB-76F0F551C802',
                        'Vendor' => substr(MODULE_PAYMENT_SAGE_PAY_DIRECT_VENDOR_LOGIN_NAME, 0, 15),
                        'VendorTxCode' => substr(date('YmdHis') . '-' . $customer_id . '-' . $cartID, 0, 40),
                        'Amount' => $this->format_raw($order->info['total']),
                        'Currency' => $currency,
                        'Description' => substr(STORE_NAME, 0, 100),
                        'CardHolder' => $cc_owner,
                        'CardNumber' => $cc_number,
                        'ExpiryDate' => $cc_expires,
                        'CardType' => $cc_type,
                        'BillingSurname' => substr($order->billing['lastname'], 0, 20),
                        'BillingFirstnames' => substr($order->billing['firstname'], 0, 20),
                        'BillingAddress1' => substr($order->billing['street_address'], 0, 100),
                        'BillingCity' => substr($order->billing['city'], 0, 40),
                        'BillingPostCode' => substr($order->billing['postcode'], 0, 10),
                        'BillingCountry' => $order->billing['country']['iso_code_2'],
                        'BillingPhone' => substr($order->customer['telephone'], 0, 20),
                        'DeliverySurname' => substr($order->delivery['lastname'], 0, 20),
                        'DeliveryFirstnames' => substr($order->delivery['firstname'], 0, 20),
                        'DeliveryAddress1' => substr($order->delivery['street_address'], 0, 100),
                        'DeliveryCity' => substr($order->delivery['city'], 0, 40),
                        'DeliveryPostCode' => substr($order->delivery['postcode'], 0, 10),
                        'DeliveryCountry' => $order->delivery['country']['iso_code_2'],
                        'CustomerName' => substr($order->billing['firstname'] . ' ' . $order->billing['lastname'], 0, 100),
                        'DeliveryPhone' => substr($order->customer['telephone'], 0, 20),
                        'CustomerEMail' => substr($order->customer['email_address'], 0, 255),
                        'Apply3DSecure' => '0');

        $ip_address = tep_get_ip_address();

        if ( (ip2long($ip_address) != -1) && (ip2long($ip_address) != false) ) {
          $params['ClientIPAddress']= $ip_address;
        }

        if ( MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_METHOD == 'Payment' ) {
          $params['TxType'] = 'PAYMENT';
        } elseif ( MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_METHOD == 'Deferred' ) {
          $params['TxType'] = 'DEFERRED';
        } else {
          $params['TxType'] = 'AUTHENTICATE';
        }

        if ( (($cc_type == 'MAESTRO') && (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MAESTRO == 'True')) || (($cc_type == 'SOLO') && (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_SOLO == 'True')) || (($cc_type == 'AMEX') && (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_AMEX == 'True')) ) {
          $params['StartDate'] = $cc_start;
        }

        if ( (($cc_type == 'MAESTRO') && (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MAESTRO == 'True')) || (($cc_type == 'SOLO') && (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_SOLO == 'True')) ) {
          $params['IssueNumber'] = $cc_issue;
        }

        if (MODULE_PAYMENT_SAGE_PAY_DIRECT_VERIFY_WITH_CVC == 'True') {
          $params['CV2'] = $cc_cvc;
        }

        if ($params['BillingCountry'] == 'US') {
          $params['BillingState'] = tep_get_zone_code($order->billing['country']['id'], $order->billing['zone_id'], '');
        }

        if ($params['DeliveryCountry'] == 'US') {
          $params['DeliveryState'] = tep_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], '');
        }

        $contents = array();

        foreach ($order->products as $product) {
          $product_name = $product['name'];

          if (isset($product['attributes'])) {
            foreach ($product['attributes'] as $att) {
              $product_name .= '; ' . $att['option'] . '=' . $att['value'];
            }
          }

          $contents[] = str_replace(array(':', "\n", "\r", '&'), '', $product_name) . ':' . $product['qty'] . ':' . $this->format_raw($product['final_price']) . ':' . $this->format_raw(($product['tax'] / 100) * $product['final_price']) . ':' . $this->format_raw((($product['tax'] / 100) * $product['final_price']) + $product['final_price']) . ':' . $this->format_raw(((($product['tax'] / 100) * $product['final_price']) + $product['final_price']) * $product['qty']);
        }

        foreach ($order_totals as $ot) {
          $contents[] = str_replace(array(':', "\n", "\r", '&'), '', strip_tags($ot['title'])) . ':---:---:---:---:' . $this->format_raw($ot['value']);
        }

        $params['Basket'] = substr(sizeof($contents) . ':' . implode(':', $contents), 0, 7500);

        $post_string = '';

        foreach ($params as $key => $value) {
          $post_string .= $key . '=' . urlencode(trim($value)) . '&';
        }

        switch (MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_SERVER) {
          case 'Live':
            $gateway_url = 'https://live.sagepay.com/gateway/service/vspdirect-register.vsp';
            break;

          case 'Test':
            $gateway_url = 'https://test.sagepay.com/gateway/service/vspdirect-register.vsp';
            break;

          default:
            $gateway_url = 'https://test.sagepay.com/Simulator/VSPDirectGateway.asp';
            break;
        }

        $transaction_response = $this->sendTransactionToGateway($gateway_url, $post_string);
      }

      $string_array = explode(chr(10), $transaction_response);
      $return = array();

      foreach ($string_array as $string) {
        if (strpos($string, '=') != false) {
          $parts = explode('=', $string, 2);
          $return[trim($parts[0])] = trim($parts[1]);
        }
      }

      if ($return['Status'] == '3DAUTH') {
        global $sage_pay_direct_acsurl, $sage_pay_direct_pareq, $sage_pay_direct_md;

        tep_session_register('sage_pay_direct_acsurl');
        $sage_pay_direct_acsurl = $return['ACSURL'];

        tep_session_register('sage_pay_direct_pareq');
        $sage_pay_direct_pareq = $return['PAReq'];

        tep_session_register('sage_pay_direct_md');
        $sage_pay_direct_md = $return['MD'];

        tep_redirect(tep_href_link('ext/modules/payment/sage_pay/checkout.php', '', 'SSL'));
      }

      if ( ($return['Status'] != 'OK') && ($return['Status'] != 'AUTHENTICATED') && ($return['Status'] != 'REGISTERED') ) {
        $error = $this->getErrorMessageNumber($return['StatusDetail']);

        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . (tep_not_null($error) ? '&error=' . $error : ''), 'SSL'));
      }

      if ( isset($return['VPSTxId']) ) {
        $order->info['comments'] = 'Sage Pay Reference ID: ' . $return['VPSTxId'] . (tep_not_null($order->info['comments']) ? "\n\n" . $order->info['comments'] : '');
      }
    }

    function after_process() {
      if (tep_session_is_registered('sage_pay_direct_acsurl')) {
        tep_session_unregister('sage_pay_direct_acsurl');
        tep_session_unregister('sage_pay_direct_pareq');
        tep_session_unregister('sage_pay_direct_md');
      }
    }

    function get_error() {
      global $HTTP_GET_VARS;

      $message = MODULE_PAYMENT_SAGE_PAY_DIRECT_ERROR_GENERAL;

      if ( isset($HTTP_GET_VARS['error']) && tep_not_null($HTTP_GET_VARS['error']) ) {
        if ( is_numeric($HTTP_GET_VARS['error']) && $this->errorMessageNumberExists($HTTP_GET_VARS['error']) ) {
          $message = $this->getErrorMessage($HTTP_GET_VARS['error']) . ' ' . MODULE_PAYMENT_SAGE_PAY_DIRECT_ERROR_GENERAL;
        } else {
          switch ($HTTP_GET_VARS['error']) {
            case 'cardtype':
              $message = MODULE_PAYMENT_SAGE_PAY_DIRECT_ERROR_CARDTYPE;
              break;

            case 'cardowner':
              $message = MODULE_PAYMENT_SAGE_PAY_DIRECT_ERROR_CARDOWNER;
              break;

            case 'cardnumber':
              $message = MODULE_PAYMENT_SAGE_PAY_DIRECT_ERROR_CARDNUMBER;
              break;

            case 'cardstart':
              $message = MODULE_PAYMENT_SAGE_PAY_DIRECT_ERROR_CARDSTART;
              break;

            case 'cardexpires':
              $message = MODULE_PAYMENT_SAGE_PAY_DIRECT_ERROR_CARDEXPIRES;
              break;

            case 'cardissue':
              $message = MODULE_PAYMENT_SAGE_PAY_DIRECT_ERROR_CARDISSUE;
              break;

            case 'cardcvc':
              $message = MODULE_PAYMENT_SAGE_PAY_DIRECT_ERROR_CARDCVC;
              break;
          }
        }
      }

      $error = array('title' => MODULE_PAYMENT_SAGE_PAY_DIRECT_ERROR_TITLE,
                     'error' => $message);

      return $error;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_SAGE_PAY_DIRECT_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Sage Pay Direct Module', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_STATUS', 'False', 'Do you want to accept Sage Pay Direct payments?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Vendor Login Name', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_VENDOR_LOGIN_NAME', '', 'The vendor login name to connect to the gateway with.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Verify With CVC', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_VERIFY_WITH_CVC', 'True', 'Verify the credit card with the billing address with the Credit Card Verification Checknumber (CVC)?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Method', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_METHOD', 'Authenticate', 'The processing method to use for each transaction.', '6', '0', 'tep_cfg_select_option(array(\'Authenticate\', \'Deferred\', \'Payment\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Server', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_SERVER', 'Simulator', 'Perform transactions on the production server or on the testing server.', '6', '0', 'tep_cfg_select_option(array(\'Live\', \'Test\', \'Simulator\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('cURL Program Location', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_CURL', '/usr/bin/curl', 'The location to the cURL program application.', '6', '0' , now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Accept Visa', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_VISA', 'True', 'Do you want to accept Visa payments?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Accept Mastercard', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MC', 'True', 'Do you want to accept Mastercard payments?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Accept Visa Delta/Debit', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_DELTA', 'True', 'Do you want to accept Visa Delta/Debit payments?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Accept Solo', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_SOLO', 'True', 'Do you want to accept Solo payments?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Accept Maestro', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MAESTRO', 'True', 'Do you want to accept Maestro payments?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Accept Visa Electron UK Debit', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_UKE', 'True', 'Do you want to accept Visa Electron UK Debit payments?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Accept American Express', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_AMEX', 'True', 'Do you want to accept American Express payments?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Accept Diners Club', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_DC', 'True', 'Do you want to accept Diners Club payments?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Accept Japan Credit Bureau', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_JCB', 'True', 'Do you want to accept Japan Credit Bureau payments?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_SAGE_PAY_DIRECT_STATUS', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_VENDOR_LOGIN_NAME', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_METHOD', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_VERIFY_WITH_CVC', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_SERVER', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_ZONE', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_ORDER_STATUS_ID', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_SORT_ORDER', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_CURL', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_VISA', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MC', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_DELTA', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_SOLO', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MAESTRO', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_UKE', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_AMEX', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_DC', 'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_JCB');
    }

    function sendTransactionToGateway($url, $parameters) {
      $server = parse_url($url);

      if (isset($server['port']) === false) {
        $server['port'] = ($server['scheme'] == 'https') ? 443 : 80;
      }

      if (isset($server['path']) === false) {
        $server['path'] = '/';
      }

      if (isset($server['user']) && isset($server['pass'])) {
        $header[] = 'Authorization: Basic ' . base64_encode($server['user'] . ':' . $server['pass']);
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

        $result = curl_exec($curl);

        curl_close($curl);
      } else {
        exec(escapeshellarg(MODULE_PAYMENT_SAGE_PAY_DIRECT_CURL) . ' -d ' . escapeshellarg($parameters) . ' "' . $server['scheme'] . '://' . $server['host'] . $server['path'] . (isset($server['query']) ? '?' . $server['query'] : '') . '" -P ' . $server['port'] . ' -k', $result);
        $result = implode("\n", $result);
      }

      return $result;
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

    function getCardTypes() {
      $this->_cards = array();

      if (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_VISA == 'True') {
        $this->_cards['VISA'] = 'Visa';
      }

      if (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MC == 'True') {
        $this->_cards['MC'] = 'Mastercard';
      }

      if (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_DELTA == 'True') {
        $this->_cards['DELTA'] = 'Visa Delta/Debit';
      }

      if (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_SOLO == 'True') {
        $this->_cards['SOLO'] = 'Solo';
      }

      if (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MAESTRO == 'True') {
        $this->_cards['MAESTRO'] = 'Maestro';
      }

      if (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_UKE == 'True') {
        $this->_cards['UKE'] = 'Visa Electron UK Debit';
      }

      if (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_AMEX == 'True') {
        $this->_cards['AMEX'] = 'American Express';
      }

      if (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_DC == 'True') {
        $this->_cards['DC'] = 'Diners Club';
      }

      if (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_JCB == 'True') {
        $this->_cards['JCB'] = 'Japan Credit Bureau';
      }

      return $this->_cards;
    }

    function hasCards() {
      if (!isset($this->_cards)) {
        $this->getCardTypes();
      }

      return !empty($this->_cards);
    }

    function isCard($key) {
      if (!isset($this->_cards)) {
        $this->getCardTypes();
      }

      return isset($this->_cards[$key]);
    }

    function loadErrorMessages() {
      $errors = array();

      if (file_exists(dirname(__FILE__) . '/../../../ext/modules/payment/sage_pay/errors.php')) {
        include(dirname(__FILE__) . '/../../../ext/modules/payment/sage_pay/errors.php');
      }

      $this->_error_messages = $errors;
    }

    function getErrorMessageNumber($string) {
      if (!isset($this->_error_messages)) {
        $this->loadErrorMessages();
      }

      $error = explode(' ', $string, 2);

      if (is_numeric($error[0]) && $this->errorMessageNumberExists($error[0])) {
        return $error[0];
      }

      return false;
    }

    function getErrorMessage($number) {
      if (!isset($this->_error_messages)) {
        $this->loadErrorMessages();
      }

      if (is_numeric($number) && $this->errorMessageNumberExists($number)) {
        return $this->_error_messages[$number];
      }

      return false;
    }

    function errorMessageNumberExists($number) {
      if (!isset($this->_error_messages)) {
        $this->loadErrorMessages();
      }

      return (is_numeric($number) && isset($this->_error_messages[$number]));
    }
  }
?>
