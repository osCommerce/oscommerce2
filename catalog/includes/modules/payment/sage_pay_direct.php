<?php
/*
  $Id: $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  class sage_pay_direct {
    var $code, $title, $description, $enabled;

// class constructor
    function sage_pay_direct() {
      global $order;

      $this->signature = 'sage_pay|sage_pay_direct|2.0|2.3';
      $this->api_version = '3.0';

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
        $months_array[] = array('id' => sprintf('%02d', $i), 'text' => sprintf('%02d', $i));
      }

      $year_valid_to_array = array();
      for ($i=$today['year']; $i < $today['year']+10; $i++) {
        $year_valid_to_array[] = array('id' => strftime('%y', mktime(0, 0, 0, 1, 1, $i)), 'text' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)));
      }

      $year_valid_from_array = array();
      for ($i=$today['year']-4; $i < $today['year']+1; $i++) {
        $year_valid_from_array[] = array('id' => strftime('%y', mktime(0, 0, 0, 1, 1, $i)), 'text' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)));
      }

      $js = <<<EOD
<script type="text/javascript">
$(function() {
  var sagepay_card_type_default = $('#sagepay_card_type').val();

  if ( sagepay_card_type_default != 'MAESTRO' || sagepay_card_type_default != 'AMEX' ) {
    if ( $('#sagepay_card_date_start').parent().parent().is(':visible') ) {
      $('#sagepay_card_date_start').parent().parent().hide();
    }
  }

  if ( sagepay_card_type_default != 'MAESTRO' ) {
    if ( $('#sagepay_card_issue').parent().parent().is(':visible') ) {
      $('#sagepay_card_issue').parent().parent().hide();
    }
  }

  $('#sagepay_card_type').change(function() {
    var selected = $(this).val();

    if ( selected == 'MAESTRO' || selected == 'AMEX' ) {
      if ( $('#sagepay_card_date_start').parent().parent().is(':hidden') ) {
        $('#sagepay_card_date_start').parent().parent().show();
      }
    } else {
      if ( $('#sagepay_card_date_start').parent().parent().is(':visible') ) {
        $('#sagepay_card_date_start').parent().parent().hide();
      }
    }

    if ( selected == 'MAESTRO' ) {
      if ( $('#sagepay_card_issue').parent().parent().is(':hidden') ) {
        $('#sagepay_card_issue').parent().parent().show();
      }
    } else {
      if ( $('#sagepay_card_issue').parent().parent().is(':visible') ) {
        $('#sagepay_card_issue').parent().parent().hide();
      }
    }
  });
});
</script>
EOD;

      $confirmation = array('fields' => array(array('title' => MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_TYPE,
                                                    'field' => tep_draw_pull_down_menu('cc_type', $card_types, '', 'id="sagepay_card_type"') . $js),
                                              array('title' => MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_OWNER,
                                                    'field' => tep_draw_input_field('cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'], 'maxlength="50"')),
                                              array('title' => MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_NUMBER,
                                                    'field' => tep_draw_input_field('cc_number_nh-dns', '', 'maxlength="20"'))));

      if ( (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MAESTRO == 'True') || (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_AMEX == 'True') ) {
        $confirmation['fields'][] = array('title' => MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_STARTS,
                                          'field' => tep_draw_pull_down_menu('cc_starts_month', $months_array, '', 'id="sagepay_card_date_start"') . '&nbsp;' . tep_draw_pull_down_menu('cc_starts_year', $year_valid_from_array) . '&nbsp;' . MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_STARTS_INFO);
      }

      $confirmation['fields'][] = array('title' => MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_EXPIRES,
                                        'field' => tep_draw_pull_down_menu('cc_expires_month', $months_array) . '&nbsp;' . tep_draw_pull_down_menu('cc_expires_year', $year_valid_to_array));

      if ( (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MAESTRO == 'True') ) {
        $confirmation['fields'][] = array('title' => MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_ISSUE_NUMBER,
                                          'field' => tep_draw_input_field('cc_issue_nh-dns', '', 'id="sagepay_card_issue" size="3" maxlength="2"') . '&nbsp;' . MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_ISSUE_NUMBER_INFO);
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
      global $HTTP_GET_VARS, $HTTP_POST_VARS, $customer_id, $order, $currency, $order_totals, $cartID, $sage_pay_response;

      $sage_pay_response = null;

      $error = null;

      if (isset($HTTP_GET_VARS['check']) && ($HTTP_GET_VARS['check'] == '3D') && isset($HTTP_POST_VARS['MD']) && tep_not_null($HTTP_POST_VARS['MD']) && isset($HTTP_POST_VARS['PaRes']) && tep_not_null($HTTP_POST_VARS['PaRes'])) {
        switch (MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_SERVER) {
          case 'Live':
            $gateway_url = 'https://live.sagepay.com/gateway/service/direct3dcallback.vsp';
            break;

          case 'Test':
          default:
            $gateway_url = 'https://test.sagepay.com/gateway/service/direct3dcallback.vsp';
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

        if ( (($cc_type == 'MAESTRO') && (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MAESTRO == 'True')) || (($cc_type == 'AMEX') && (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_AMEX == 'True')) ) {
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

        if ( (($cc_type == 'MAESTRO') && (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MAESTRO == 'True')) ) {
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

        $params = array('VPSProtocol' => '3.00',
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
                        'DeliveryPhone' => substr($order->customer['telephone'], 0, 20),
                        'CustomerEMail' => substr($order->customer['email_address'], 0, 255),
                        'Apply3DSecure' => '0',
                        'CreateToken' => (MODULE_PAYMENT_SAGE_PAY_DIRECT_TOKENS == 'True' ? '1' : '0'));

        $ip_address = tep_get_ip_address();

        if ( !empty($ip_address) && (ip2long($ip_address) != -1) && (ip2long($ip_address) != false) ) {
          $params['ClientIPAddress']= $ip_address;
        }

        if ( MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_METHOD == 'Payment' ) {
          $params['TxType'] = 'PAYMENT';
        } elseif ( MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_METHOD == 'Deferred' ) {
          $params['TxType'] = 'DEFERRED';
        } else {
          $params['TxType'] = 'AUTHENTICATE';
        }

        if ( (($cc_type == 'MAESTRO') && (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MAESTRO == 'True')) || (($cc_type == 'AMEX') && (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_AMEX == 'True')) ) {
          $params['StartDate'] = $cc_start;
        }

        if ( (($cc_type == 'MAESTRO') && (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MAESTRO == 'True')) ) {
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
          default:
            $gateway_url = 'https://test.sagepay.com/gateway/service/vspdirect-register.vsp';
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

      $sage_pay_response = $return;
    }

    function after_process() {
      global $insert_id, $sage_pay_response;

      $result = array();

      if ( isset($sage_pay_response['VPSTxId']) ) {
        $result['ID'] = $sage_pay_response['VPSTxId'];
      }

      if ( isset($sage_pay_response['SecurityKey']) ) {
        $result['Security Key'] = $sage_pay_response['SecurityKey'];
      }

      if ( isset($sage_pay_response['AVSCV2']) ) {
        $result['AVS/CV2'] = $sage_pay_response['AVSCV2'];
      }

      if ( isset($sage_pay_response['AddressResult']) ) {
        $result['Address'] = $sage_pay_response['AddressResult'];
      }

      if ( isset($sage_pay_response['PostCodeResult']) ) {
        $result['Post Code'] = $sage_pay_response['PostCodeResult'];
      }

      if ( isset($sage_pay_response['CV2Result']) ) {
        $result['CV2'] = $sage_pay_response['CV2Result'];
      }

      if ( isset($sage_pay_response['3DSecureStatus']) ) {
        $result['3D Secure'] = $sage_pay_response['3DSecureStatus'];
      }

      $result_string = '';

      foreach ( $result as $k => $v ) {
        $result_string .= $k . ': ' . $v . "\n";
      }

      $sql_data_array = array('orders_id' => $insert_id,
                              'orders_status_id' => MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_ORDER_STATUS_ID,
                              'date_added' => 'now()',
                              'customer_notified' => '0',
                              'comments' => trim($result_string));

      tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

      if (tep_session_is_registered('sage_pay_direct_acsurl')) {
        tep_session_unregister('sage_pay_direct_acsurl');
        tep_session_unregister('sage_pay_direct_pareq');
        tep_session_unregister('sage_pay_direct_md');
      }

      $sage_pay_response = null;
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

    function install($parameter = null) {
      $params = $this->getParams();

      if (isset($parameter)) {
        if (isset($params[$parameter])) {
          $params = array($parameter => $params[$parameter]);
        } else {
          $params = array();
        }
      }

      foreach ($params as $key => $data) {
        $sql_data_array = array('configuration_title' => $data['title'],
                                'configuration_key' => $key,
                                'configuration_value' => (isset($data['value']) ? $data['value'] : ''),
                                'configuration_description' => $data['desc'],
                                'configuration_group_id' => '6',
                                'sort_order' => '0',
                                'date_added' => 'now()');

        if (isset($data['set_func'])) {
          $sql_data_array['set_function'] = $data['set_func'];
        }

        if (isset($data['use_func'])) {
          $sql_data_array['use_function'] = $data['use_func'];
        }

        tep_db_perform(TABLE_CONFIGURATION, $sql_data_array);
      }
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      $keys = array_keys($this->getParams());

      if ($this->check()) {
        foreach ($keys as $key) {
          if (!defined($key)) {
            $this->install($key);
          }
        }
      }

      return $keys;
    }

    function getParams() {
      if (!defined('MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_ORDER_STATUS_ID')) {
        $check_query = tep_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Sage Pay [Transactions]' limit 1");

        if (tep_db_num_rows($check_query) < 1) {
          $status_query = tep_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
          $status = tep_db_fetch_array($status_query);

          $status_id = $status['status_id']+1;

          $languages = tep_get_languages();

          foreach ($languages as $lang) {
            tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id . "', '" . $lang['id'] . "', 'Sage Pay [Transactions]')");
          }

          $flags_query = tep_db_query("describe " . TABLE_ORDERS_STATUS . " public_flag");
          if (tep_db_num_rows($flags_query) == 1) {
            tep_db_query("update " . TABLE_ORDERS_STATUS . " set public_flag = 0 and downloads_flag = 0 where orders_status_id = '" . $status_id . "'");
          }
        } else {
          $check = tep_db_fetch_array($check_query);

          $status_id = $check['orders_status_id'];
        }
      } else {
        $status_id = MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_ORDER_STATUS_ID;
      }

      $params = array('MODULE_PAYMENT_SAGE_PAY_DIRECT_STATUS' => array('title' => 'Enable Sage Pay Direct Module',
                                                                       'desc' => 'Do you want to accept Sage Pay Direct payments?',
                                                                       'value' => 'False',
                                                                       'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_VENDOR_LOGIN_NAME' => array('title' => 'Vendor Login Name',
                                                                                  'desc' => 'The vendor login name to connect to the gateway with.',
                                                                                  'value' => ''),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_METHOD' => array('title' => 'Transaction Method',
                                                                                   'desc' => 'The processing method to use for each transaction.',
                                                                                   'value' => 'Authenticate',
                                                                                   'set_func' => 'tep_cfg_select_option(array(\'Authenticate\', \'Deferred\', \'Payment\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_VERIFY_WITH_CVC' => array('title' => 'Verify With CVC',
                                                                                'desc' => 'Verify the credit card with the billing address with the Credit Card Verification Checknumber (CVC)?',
                                                                                'value' => 'True',
                                                                                'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_TOKENS' => array('title' => 'Create Tokens',
                                                                       'desc' => 'Create and store tokens for card payments customer can use on their next purchase?',
                                                                       'value' => 'False',
                                                                       'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_SERVER' => array('title' => 'Transaction Server',
                                                                                   'desc' => 'Perform transactions on the production server or on the testing server.',
                                                                                   'value' => 'Test',
                                                                                   'set_func' => 'tep_cfg_select_option(array(\'Live\', \'Test\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_VERIFY_SSL' => array('title' => 'Verify SSL Certificate',
                                                                           'desc' => 'Verify transaction server SSL certificate on connection?',
                                                                           'value' => 'True',
                                                                           'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_ORDER_STATUS_ID' => array('title' => 'Transaction Order Status',
                                                                                            'desc' => 'Include transaction information in this order status level',
                                                                                            'value' => $status_id,
                                                                                            'set_func' => 'tep_cfg_pull_down_order_statuses(',
                                                                                            'use_func' => 'tep_get_order_status_name'),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_ZONE' => array('title' => 'Payment Zone',
                                                                     'desc' => 'If a zone is selected, only enable this payment method for that zone.',
                                                                     'value' => '0',
                                                                     'use_func' => 'tep_get_zone_class_title',
                                                                     'set_func' => 'tep_cfg_pull_down_zone_classes('),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_ORDER_STATUS_ID' => array('title' => 'Set Order Status',
                                                                                'desc' => 'Set the status of orders made with this payment module to this value',
                                                                                'value' => '0',
                                                                                'use_func' => 'tep_get_order_status_name',
                                                                                'set_func' => 'tep_cfg_pull_down_order_statuses('),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_SORT_ORDER' => array('title' => 'Sort order of display.',
                                                                           'desc' => 'Sort order of display. Lowest is displayed first.',
                                                                           'value' => '0'),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_VISA' => array('title' => 'Accept Visa',
                                                                           'desc' => 'Do you want to accept Visa payments?',
                                                                           'value' => 'True',
                                                                           'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MC' => array('title' => 'Accept Mastercard',
                                                                         'desc' => 'Do you want to accept Mastercard payments?',
                                                                         'value' => 'True',
                                                                         'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MCDEBIT' => array('title' => 'Accept Mastercard Debit',
                                                                              'desc' => 'Do you want to accept Mastercard Debit payments?',
                                                                              'value' => 'True',
                                                                              'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_DELTA' => array('title' => 'Accept Visa Delta/Debit',
                                                                            'desc' => 'Do you want to accept Visa Delta/Debit payments?',
                                                                            'value' => 'True',
                                                                            'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MAESTRO' => array('title' => 'Accept Maestro',
                                                                              'desc' => 'Do you want to accept Maestro payments?',
                                                                              'value' => 'True',
                                                                              'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_UKE' => array('title' => 'Accept Visa Electron UK Debit',
                                                                          'desc' => 'Do you want to accept Visa Electron UK Debit payments?',
                                                                          'value' => 'True',
                                                                          'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_AMEX' => array('title' => 'Accept American Express',
                                                                           'desc' => 'Do you want to accept American Express payments?',
                                                                           'value' => 'True',
                                                                           'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_DC' => array('title' => 'Accept Diners Club',
                                                                         'desc' => 'Do you want to accept Diners Club payments?',
                                                                         'value' => 'True',
                                                                         'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_JCB' => array('title' => 'Accept Japan Credit Bureau',
                                                                          'desc' => 'Do you want to accept Japan Credit Bureau payments?',
                                                                          'value' => 'True',
                                                                          'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_LASER' => array('title' => 'Accept Laser Card',
                                                                            'desc' => 'Do you want to accept Laser Card payments?',
                                                                            'value' => 'True',
                                                                            'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '));

      return $params;
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

      $curl = curl_init($server['scheme'] . '://' . $server['host'] . $server['path'] . (isset($server['query']) ? '?' . $server['query'] : ''));
      curl_setopt($curl, CURLOPT_PORT, $server['port']);
      curl_setopt($curl, CURLOPT_HEADER, 0);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
      curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
      curl_setopt($curl, CURLOPT_POST, 1);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);

      if ( MODULE_PAYMENT_SAGE_PAY_DIRECT_VERIFY_SSL == 'True' ) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
      } else {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      }

      $result = curl_exec($curl);

      curl_close($curl);

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

      if (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MCDEBIT == 'True') {
        $this->_cards['MCDEBIT'] = 'Mastercard Debit';
      }

      if (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_DELTA == 'True') {
        $this->_cards['DELTA'] = 'Visa Delta/Debit';
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

      if (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_LASER == 'True') {
        $this->_cards['LASER'] = 'Laser Card';
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
