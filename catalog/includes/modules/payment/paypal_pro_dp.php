<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  if ( !class_exists('OSCOM_PayPal') ) {
    include(DIR_FS_CATALOG . 'includes/apps/paypal/OSCOM_PayPal.php');
  }

  class paypal_pro_dp {
    var $code, $title, $description, $enabled, $_app;

    function paypal_pro_dp() {
      global $order;

      $this->_app = new OSCOM_PayPal();
      $this->_app->loadLanguageFile('modules/DP/DP.php');

      $this->signature = 'paypal|paypal_pro_dp|' . $this->_app->getVersion() . '|2.3';
      $this->api_version = $this->_app->getApiVersion();

      $this->code = 'paypal_pro_dp';
      $this->title = $this->_app->getDef('module_dp_title');
      $this->public_title = $this->_app->getDef('module_dp_public_title');
      $this->description = '<div align="center">' . $this->_app->drawButton($this->_app->getDef('module_dp_legacy_admin_app_button'), tep_href_link('paypal.php', 'action=configure&module=DP'), 'primary', null, true) . '</div>';
      $this->sort_order = defined('OSCOM_APP_PAYPAL_DP_SORT_ORDER') ? OSCOM_APP_PAYPAL_DP_SORT_ORDER : 0;
      $this->enabled = defined('OSCOM_APP_PAYPAL_DP_STATUS') && in_array(OSCOM_APP_PAYPAL_DP_STATUS, array('1', '0')) ? true : false;
      $this->order_status = defined('OSCOM_APP_PAYPAL_DP_ORDER_STATUS_ID') && ((int)OSCOM_APP_PAYPAL_DP_ORDER_STATUS_ID > 0) ? (int)OSCOM_APP_PAYPAL_DP_ORDER_STATUS_ID : 0;

      if ( !defined('MODULE_PAYMENT_INSTALLED') || !tep_not_null(MODULE_PAYMENT_INSTALLED) || !in_array('paypal_express.php', explode(';', MODULE_PAYMENT_INSTALLED)) || !defined('OSCOM_APP_PAYPAL_EC_STATUS') || !in_array(OSCOM_APP_PAYPAL_EC_STATUS, array('1', '0')) ) {
        $this->description .= '<div class="secWarning">' . $this->_app->getDef('module_dp_error_express_module') . '</div>';

        $this->enabled = false;
      }

      if ( defined('OSCOM_APP_PAYPAL_DP_STATUS') ) {
        if ( OSCOM_APP_PAYPAL_DP_STATUS == '0' ) {
          $this->title .= ' [Sandbox]';
          $this->public_title .= ' (' . $this->code . '; Sandbox)';
        }
      }

      if ( !function_exists('curl_init') ) {
        $this->description .= '<div class="secWarning">' . $this->_app->getDef('module_dp_error_curl') . '</div>';

        $this->enabled = false;
      }

      if ( $this->enabled === true ) {
        if ( !$this->_app->hasCredentials('DP') ) {
          $this->description .= '<div class="secWarning">' . $this->_app->getDef('module_dp_error_credentials') . '</div>';

          $this->enabled = false;
        }
      }

      if ( $this->enabled === true ) {
        if ( isset($order) && is_object($order) ) {
          $this->update_status();
        }
      }

      $this->cc_types = array('VISA' => 'Visa',
                              'MASTERCARD' => 'MasterCard',
                              'DISCOVER' => 'Discover Card',
                              'AMEX' => 'American Express',
                              'MAESTRO' => 'Maestro');
    }

    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)OSCOM_APP_PAYPAL_DP_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . OSCOM_APP_PAYPAL_DP_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
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
      return array('id' => $this->code,
                   'module' => $this->public_title);
    }

    function pre_confirmation_check() {
      if ( $this->templateClassExists() ) {
        $GLOBALS['oscTemplate']->addBlock($this->getSubmitCardDetailsJavascript(), 'header_tags');
      }
    }

    function confirmation() {
      global $order;

      $types_array = array();
      foreach ( $this->cc_types as $key => $value ) {
        if ($this->isCardAccepted($key)) {
          $types_array[] = array('id' => $key,
                                 'text' => $value);
        }
      }

      $today = getdate();

      $months_array = array();
      for ($i=1; $i<13; $i++) {
        $months_array[] = array('id' => sprintf('%02d', $i), 'text' => sprintf('%02d', $i));
      }

      $year_valid_from_array = array();
      for ($i=$today['year']-10; $i < $today['year']+1; $i++) {
        $year_valid_from_array[] = array('id' => strftime('%Y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
      }

      $year_expires_array = array();
      for ($i=$today['year']; $i < $today['year']+10; $i++) {
        $year_expires_array[] = array('id' => strftime('%Y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
      }

      $content = '<table id="paypal_table_new_card" border="0" width="100%" cellspacing="0" cellpadding="2">' .
                 '  <tr>' .
                 '    <td width="30%">' . $this->_app->getDef('module_dp_field_card_type') . '</td>' .
                 '    <td>' . tep_draw_pull_down_menu('cc_type', $types_array, '', 'id="paypal_card_type"') . '</td>' .
                 '  </tr>' .
                 '  <tr>' .
                 '    <td width="30%">' . $this->_app->getDef('module_dp_field_card_owner') . '</td>' .
                 '    <td>' . tep_draw_input_field('cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname']) . '</td>' .
                 '  </tr>' .
                 '  <tr>' .
                 '    <td width="30%">' . $this->_app->getDef('module_dp_field_card_number') . '</td>' .
                 '    <td>' . tep_draw_input_field('cc_number_nh-dns', '', 'id="paypal_card_num"') . '</td>' .
                 '  </tr>' .
                 '  <tr>' .
                 '    <td width="30%">' . $this->_app->getDef('module_dp_field_card_expires') . '</td>' .
                 '    <td>' . tep_draw_pull_down_menu('cc_expires_month', $months_array) . '&nbsp;' . tep_draw_pull_down_menu('cc_expires_year', $year_expires_array) . '</td>' .
                 '  </tr>' .
                 '  <tr>' .
                 '    <td width="30%">' . $this->_app->getDef('module_dp_field_card_cvc') . '</td>' .
                 '    <td>' . tep_draw_input_field('cc_cvc_nh-dns', '', 'size="5" maxlength="4"') . ' <span id="cardSecurityCodeInfo" title="' . tep_output_string($this->_app->getDef('module_dp_field_card_cvc_info')) . '" style="color: #084482; text-decoration: none; border-bottom: 1px dashed #084482; cursor: pointer;">' . $this->_app->getDef('module_dp_field_card_cvc_info_link') . '</span></td>' .
                 '  </tr>';

      if ( $this->isCardAccepted('MAESTRO') ) {
        $content .= '  <tr>' .
                    '    <td width="30%">' . $this->_app->getDef('module_dp_field_card_valid_from') . '</td>' .
                    '    <td>' . tep_draw_pull_down_menu('cc_starts_month', $months_array, '', 'id="paypal_card_date_start"') . '&nbsp;' . tep_draw_pull_down_menu('cc_starts_year', $year_valid_from_array) . '&nbsp;' . $this->_app->getDef('module_dp_field_card_valid_from_info') . '</td>' .
                    '  </tr>' .
                    '  <tr>' .
                    '    <td width="30%">' . $this->_app->getDef('module_dp_field_card_issue_number') . '</td>' .
                    '    <td>' . tep_draw_input_field('cc_issue_nh-dns', '', 'id="paypal_card_issue" size="3" maxlength="2"') . '&nbsp;' . $this->_app->getDef('module_dp_field_card_issue_number_info') . '</td>' .
                    '  </tr>';
      }

      $content .= '</table>';

      if ( !$this->templateClassExists() ) {
        $content .= $this->getSubmitCardDetailsJavascript();
      }

      $confirmation = array('title' => $content);

      return $confirmation;
    }

    function process_button() {
      return false;
    }

    function before_process() {
      if ( OSCOM_APP_PAYPAL_GATEWAY == '1' ) {
        $this->before_process_paypal();
      } else {
        $this->before_process_payflow();
      }
    }

    function before_process_paypal() {
      global $HTTP_POST_VARS, $order, $order_totals, $sendto, $response_array;

      if ( isset($HTTP_POST_VARS['cc_owner']) && !empty($HTTP_POST_VARS['cc_owner']) && isset($HTTP_POST_VARS['cc_type']) && $this->isCardAccepted($HTTP_POST_VARS['cc_type']) && isset($HTTP_POST_VARS['cc_number_nh-dns']) && !empty($HTTP_POST_VARS['cc_number_nh-dns']) ) {
        $params = array('AMT' => $this->_app->formatCurrencyRaw($order->info['total']),
                        'CREDITCARDTYPE' => $HTTP_POST_VARS['cc_type'],
                        'ACCT' => $HTTP_POST_VARS['cc_number_nh-dns'],
                        'EXPDATE' => $HTTP_POST_VARS['cc_expires_month'] . $HTTP_POST_VARS['cc_expires_year'],
                        'CVV2' => $HTTP_POST_VARS['cc_cvc_nh-dns'],
                        'FIRSTNAME' => substr($HTTP_POST_VARS['cc_owner'], 0, strpos($HTTP_POST_VARS['cc_owner'], ' ')),
                        'LASTNAME' => substr($HTTP_POST_VARS['cc_owner'], strpos($HTTP_POST_VARS['cc_owner'], ' ')+1),
                        'STREET' => $order->billing['street_address'],
                        'CITY' => $order->billing['city'],
                        'STATE' => tep_get_zone_code($order->billing['country']['id'], $order->billing['zone_id'], $order->billing['state']),
                        'COUNTRYCODE' => $order->billing['country']['iso_code_2'],
                        'ZIP' => $order->billing['postcode'],
                        'EMAIL' => $order->customer['email_address'],
                        'SHIPTOPHONENUM' => $order->customer['telephone'],
                        'CURRENCYCODE' => $order->info['currency']);

        if ( $HTTP_POST_VARS['cc_type'] == 'MAESTRO' ) {
          $params['STARTDATE'] = $HTTP_POST_VARS['cc_starts_month'] . $HTTP_POST_VARS['cc_starts_year'];
          $params['ISSUENUMBER'] = $HTTP_POST_VARS['cc_issue_nh-dns'];
        }

        if ( is_numeric($sendto) && ($sendto > 0) ) {
          $params['SHIPTONAME'] = $order->delivery['firstname'] . ' ' . $order->delivery['lastname'];
          $params['SHIPTOSTREET'] = $order->delivery['street_address'];
          $params['SHIPTOCITY'] = $order->delivery['city'];
          $params['SHIPTOSTATE'] = tep_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']);
          $params['SHIPTOCOUNTRYCODE'] = $order->delivery['country']['iso_code_2'];
          $params['SHIPTOZIP'] = $order->delivery['postcode'];
        }

        $item_params = array();

        $line_item_no = 0;

        foreach ( $order->products as $product ) {
          $item_params['L_NAME' . $line_item_no] = $product['name'];
          $item_params['L_AMT' . $line_item_no] = $this->_app->formatCurrencyRaw($product['final_price']);
          $item_params['L_NUMBER' . $line_item_no] = $product['id'];
          $item_params['L_QTY' . $line_item_no] = $product['qty'];

          $line_item_no++;
        }

        $items_total = $this->_app->formatCurrencyRaw($order->info['subtotal']);

        foreach ( $order_totals as $ot ) {
          if ( !in_array($ot['code'], array('ot_subtotal', 'ot_shipping', 'ot_tax', 'ot_total')) ) {
            $item_params['L_NAME' . $line_item_no] = $ot['title'];
            $item_params['L_AMT' . $line_item_no] = $this->_app->formatCurrencyRaw($ot['value']);

            $items_total += $this->_app->formatCurrencyRaw($ot['value']);

            $line_item_no++;
          }
        }

        $item_params['ITEMAMT'] = $items_total;
        $item_params['TAXAMT'] = $this->_app->formatCurrencyRaw($order->info['tax']);
        $item_params['SHIPPINGAMT'] = $this->_app->formatCurrencyRaw($order->info['shipping_cost']);

        if ( $this->_app->formatCurrencyRaw($item_params['ITEMAMT'] + $item_params['TAXAMT'] + $item_params['SHIPPINGAMT']) == $params['AMT'] ) {
          $params = array_merge($params, $item_params);
        }

        $response_array = $this->_app->getApiResult('DP', 'DoDirectPayment', $params);

        if ( !in_array($response_array['ACK'], array('Success', 'SuccessWithWarning')) ) {
          tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, 'error_message=' . stripslashes($response_array['L_LONGMESSAGE0']), 'SSL'));
        }
      } else {
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, 'error_message=' . $this->_app->getDef('module_dp_error_all_fields_required'), 'SSL'));
      }
    }

    function before_process_payflow() {
      global $HTTP_POST_VARS, $cartID, $order, $order_totals, $sendto, $response_array;

      if ( isset($HTTP_POST_VARS['cc_owner']) && !empty($HTTP_POST_VARS['cc_owner']) && isset($HTTP_POST_VARS['cc_type']) && $this->isCardAccepted($HTTP_POST_VARS['cc_type']) && isset($HTTP_POST_VARS['cc_number_nh-dns']) && !empty($HTTP_POST_VARS['cc_number_nh-dns']) ) {
        $params = array('AMT' => $this->_app->formatCurrencyRaw($order->info['total']),
                        'CURRENCY' => $order->info['currency'],
                        'BILLTOFIRSTNAME' => substr($HTTP_POST_VARS['cc_owner'], 0, strpos($HTTP_POST_VARS['cc_owner'], ' ')),
                        'BILLTOLASTNAME' => substr($HTTP_POST_VARS['cc_owner'], strpos($HTTP_POST_VARS['cc_owner'], ' ')+1),
                        'BILLTOSTREET' => $order->billing['street_address'],
                        'BILLTOCITY' => $order->billing['city'],
                        'BILLTOSTATE' => tep_get_zone_code($order->billing['country']['id'], $order->billing['zone_id'], $order->billing['state']),
                        'BILLTOCOUNTRY' => $order->billing['country']['iso_code_2'],
                        'BILLTOZIP' => $order->billing['postcode'],
                        'EMAIL' => $order->customer['email_address'],
                        'ACCT' => $HTTP_POST_VARS['cc_number_nh-dns'],
                        'EXPDATE' => $HTTP_POST_VARS['cc_expires_month'] . $HTTP_POST_VARS['cc_expires_year'],
                        'CVV2' => $HTTP_POST_VARS['cc_cvc_nh-dns']);

        if ( is_numeric($sendto) && ($sendto > 0) ) {
          $params['SHIPTOFIRSTNAME'] = $order->delivery['firstname'];
          $params['SHIPTOLASTNAME'] = $order->delivery['lastname'];
          $params['SHIPTOSTREET'] = $order->delivery['street_address'];
          $params['SHIPTOCITY'] = $order->delivery['city'];
          $params['SHIPTOSTATE'] = tep_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']);
          $params['SHIPTOCOUNTRY'] = $order->delivery['country']['iso_code_2'];
          $params['SHIPTOZIP'] = $order->delivery['postcode'];
        }

        $item_params = array();

        $line_item_no = 0;

        foreach ($order->products as $product) {
          $item_params['L_NAME' . $line_item_no] = $product['name'];
          $item_params['L_COST' . $line_item_no] = $this->_app->formatCurrencyRaw($product['final_price']);
          $item_params['L_QTY' . $line_item_no] = $product['qty'];

          $line_item_no++;
        }

        $items_total = $this->_app->formatCurrencyRaw($order->info['subtotal']);

        foreach ($order_totals as $ot) {
          if ( !in_array($ot['code'], array('ot_subtotal', 'ot_shipping', 'ot_tax', 'ot_total')) ) {
            $item_params['L_NAME' . $line_item_no] = $ot['title'];
            $item_params['L_COST' . $line_item_no] = $this->_app->formatCurrencyRaw($ot['value']);
            $item_params['L_QTY' . $line_item_no] = 1;

            $items_total += $this->_app->formatCurrencyRaw($ot['value']);

            $line_item_no++;
          }
        }

        $item_params['ITEMAMT'] = $items_total;
        $item_params['TAXAMT'] = $this->_app->formatCurrencyRaw($order->info['tax']);
        $item_params['FREIGHTAMT'] = $this->_app->formatCurrencyRaw($order->info['shipping_cost']);

        if ( $this->_app->formatCurrencyRaw($item_params['ITEMAMT'] + $item_params['TAXAMT'] + $item_params['FREIGHTAMT']) == $params['AMT'] ) {
          $params = array_merge($params, $item_params);
        }

        $params['_headers'] = array('X-VPS-REQUEST-ID: ' . md5($cartID . tep_session_id() . $this->_app->formatCurrencyRaw($order->info['total'])),
                                    'X-VPS-CLIENT-TIMEOUT: 45',
                                    'X-VPS-VIT-INTEGRATION-PRODUCT: OSCOM',
                                    'X-VPS-VIT-INTEGRATION-VERSION: 2.3');

        $response_array = $this->_app->getApiResult('DP', 'PayflowPayment', $params);

        if ( $response_array['RESULT'] != '0' ) {
          switch ( $response_array['RESULT'] ) {
            case '1':
            case '26':
              $error_message = $this->_app->getDef('module_dp_error_configuration');
              break;

            case '7':
              $error_message = $this->_app->getDef('module_dp_error_address');
              break;

            case '12':
              $error_message = $this->_app->getDef('module_dp_error_declined');
              break;

            case '23':
            case '24':
              $error_message = $this->_app->getDef('module_dp_error_invalid_card');
              break;

            default:
              $error_message = $this->_app->getDef('module_dp_error_general');
              break;
          }

          tep_redirect(tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, 'error_message=' . $error_message, 'SSL'));
        }
      } else {
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, 'error_message=' . $this->_app->getDef('module_dp_error_all_fields_required'), 'SSL'));
      }
    }

    function after_process() {
      if ( OSCOM_APP_PAYPAL_GATEWAY == '1' ) {
        $this->after_process_paypal();
      } else {
        $this->after_process_payflow();
      }
    }

    function after_process_paypal() {
      global $response_array, $insert_id;

      $details = $this->_app->getApiResult('APP', 'GetTransactionDetails', array('TRANSACTIONID' => $response_array['TRANSACTIONID']), (OSCOM_APP_PAYPAL_DP_STATUS == '1') ? 'live' : 'sandbox');

      $result = 'Transaction ID: ' . tep_output_string_protected($response_array['TRANSACTIONID']) . "\n";

      if ( in_array($details['ACK'], array('Success', 'SuccessWithWarning')) ) {
        $result .= 'Payer Status: ' . tep_output_string_protected($details['PAYERSTATUS']) . "\n" .
                   'Address Status: ' . tep_output_string_protected($details['ADDRESSSTATUS']) . "\n" .
                   'Payment Status: ' . tep_output_string_protected($details['PAYMENTSTATUS']) . "\n" .
                   'Payment Type: ' . tep_output_string_protected($details['PAYMENTTYPE']) . "\n" .
                   'Pending Reason: ' . tep_output_string_protected($details['PENDINGREASON']) . "\n";
      }

      $result .= 'AVS Code: ' . tep_output_string_protected($response_array['AVSCODE']) . "\n" .
                 'CVV2 Match: ' . tep_output_string_protected($response_array['CVV2MATCH']);

      $sql_data_array = array('orders_id' => $insert_id,
                              'orders_status_id' => OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID,
                              'date_added' => 'now()',
                              'customer_notified' => '0',
                              'comments' => $result);

      tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
    }

    function after_process_payflow() {
      global $insert_id, $response_array;

      $details = $this->_app->getApiResult('APP', 'PayflowInquiry', array('ORIGID' => $response_array['PNREF']), (OSCOM_APP_PAYPAL_DP_STATUS == '1') ? 'live' : 'sandbox');

      $result = 'Transaction ID: ' . tep_output_string_protected($response_array['PNREF']) . "\n" .
                'Gateway: Payflow' . "\n" .
                'PayPal ID: ' . tep_output_string_protected($response_array['PPREF']) . "\n" .
                'Response: ' . tep_output_string_protected($response_array['RESPMSG']) . "\n";

      if ( isset($details['RESULT']) && ($details['RESULT'] == '0') ) {
        $pending_reason = $details['TRANSSTATE'];
        $payment_status = null;

        switch ( $details['TRANSSTATE'] ) {
          case '3':
            $pending_reason = 'authorization';
            $payment_status = 'Pending';
            break;

          case '4':
            $pending_reason = 'other';
            $payment_status = 'In-Progress';
            break;

          case '6':
            $pending_reason = 'scheduled';
            $payment_status = 'Pending';
            break;

          case '8':
          case '9':
            $pending_reason = 'None';
            $payment_status = 'Completed';
            break;
        }

        if ( isset($payment_status) ) {
          $result .= 'Payment Status: ' . tep_output_string_protected($payment_status) . "\n";
        }

        $result .= 'Pending Reason: ' . tep_output_string_protected($pending_reason) . "\n";
      }

      switch ( $response_array['AVSADDR'] ) {
        case 'Y':
          $result .= 'AVS Address: Match' . "\n";
          break;

        case 'N':
          $result .= 'AVS Address: No Match' . "\n";
          break;
      }

      switch ( $response_array['AVSZIP'] ) {
        case 'Y':
          $result .= 'AVS ZIP: Match' . "\n";
          break;

        case 'N':
          $result .= 'AVS ZIP: No Match' . "\n";
          break;
      }

      switch ( $response_array['IAVS'] ) {
        case 'Y':
          $result .= 'IAVS: International' . "\n";
          break;

        case 'N':
          $result .= 'IAVS: USA' . "\n";
          break;
      }

      switch ( $response_array['CVV2MATCH'] ) {
        case 'Y':
          $result .= 'CVV2: Match' . "\n";
          break;

        case 'N':
          $result .= 'CVV2: No Match' . "\n";
          break;
      }

      $sql_data_array = array('orders_id' => $insert_id,
                              'orders_status_id' => OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID,
                              'date_added' => 'now()',
                              'customer_notified' => '0',
                              'comments' => $result);

      tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
    }

    function get_error() {
      return false;
    }

    function check() {
      $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'OSCOM_APP_PAYPAL_DP_STATUS'");
      if ( tep_db_num_rows($check_query) ) {
        $check = tep_db_fetch_array($check_query);

        return tep_not_null($check['configuration_value']);
      }

      return false;
    }

    function install() {
      tep_redirect(tep_href_link('paypal.php', 'action=configure&subaction=install&module=DP'));
    }

    function remove() {
      tep_redirect(tep_href_link('paypal.php', 'action=configure&subaction=uninstall&module=DP'));
    }

    function keys() {
      return array('OSCOM_APP_PAYPAL_DP_SORT_ORDER');
    }

    function isCardAccepted($card) {
      static $cards;

      if ( !isset($cards) ) {
        $cards = explode(';', OSCOM_APP_PAYPAL_DP_CARDS);
      }

      return isset($this->cc_types[$card]) && in_array(strtolower($card), $cards);
    }

    function templateClassExists() {
      return class_exists('oscTemplate') && isset($GLOBALS['oscTemplate']) && is_object($GLOBALS['oscTemplate']) && (get_class($GLOBALS['oscTemplate']) == 'oscTemplate');
    }

    function getSubmitCardDetailsJavascript() {
      $js = <<<EOD
<script>
if ( typeof jQuery == 'undefined' ) {
  document.write('<scr' + 'ipt src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></scr' + 'ipt>');
}
</script>

<script>
$(function() {
  if ( typeof($('#paypal_table_new_card').parent().closest('table').attr('width')) == 'undefined' ) {
    $('#paypal_table_new_card').parent().closest('table').attr('width', '100%');
  }

  paypalShowNewCardFields();

  $('#paypal_card_type').change(function() {
    var selected = $(this).val();

    if ( $('#paypal_card_date_start').length > 0 ) {
      if ( selected == 'MAESTRO' ) {
        $('#paypal_card_date_start').parent().parent().show();
      } else {
        $('#paypal_card_date_start').parent().parent().hide();
      }
    }

    if ( $('#paypal_card_issue').length > 0 ) {
      if ( selected == 'MAESTRO' ) {
        $('#paypal_card_issue').parent().parent().show();
      } else {
        $('#paypal_card_issue').parent().parent().hide();
      }
    }
  });

  $('#cardSecurityCodeInfo').tooltip();
});

function paypalShowNewCardFields() {
  var selected = $('#paypal_card_type').val();

  if ( $('#paypal_card_date_start').length > 0 ) {
    if ( selected != 'MAESTRO' ) {
      $('#paypal_card_date_start').parent().parent().hide();
    }
  }

  if ( $('#paypal_card_issue').length > 0 ) {
    if ( selected != 'MAESTRO' ) {
      $('#paypal_card_issue').parent().parent().hide();
    }
  }
}
</script>
EOD;

      return $js;
    }
  }
?>
