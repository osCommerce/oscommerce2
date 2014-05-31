<?php
/*
  $Id: $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class sage_pay_direct {
    var $code, $title, $description, $enabled;

    function sage_pay_direct() {
      global $HTTP_GET_VARS, $PHP_SELF, $order;

      $this->signature = 'sage_pay|sage_pay_direct|3.0|2.3';
      $this->api_version = '3.00';

      $this->code = 'sage_pay_direct';
      $this->title = MODULE_PAYMENT_SAGE_PAY_DIRECT_TEXT_TITLE;
      $this->public_title = MODULE_PAYMENT_SAGE_PAY_DIRECT_TEXT_PUBLIC_TITLE;
      $this->description = MODULE_PAYMENT_SAGE_PAY_DIRECT_TEXT_DESCRIPTION;
      $this->sort_order = defined('MODULE_PAYMENT_SAGE_PAY_DIRECT_SORT_ORDER') ? MODULE_PAYMENT_SAGE_PAY_DIRECT_SORT_ORDER : 0;
      $this->enabled = defined('MODULE_PAYMENT_SAGE_PAY_DIRECT_STATUS') && (MODULE_PAYMENT_SAGE_PAY_DIRECT_STATUS == 'True') ? true : false;
      $this->order_status = defined('MODULE_PAYMENT_SAGE_PAY_DIRECT_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_SAGE_PAY_DIRECT_ORDER_STATUS_ID > 0) ? (int)MODULE_PAYMENT_SAGE_PAY_DIRECT_ORDER_STATUS_ID : 0;

      if ( defined('MODULE_PAYMENT_SAGE_PAY_DIRECT_STATUS') ) {
        if ( MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_SERVER == 'Test' ) {
          $this->title .= ' [Test]';
          $this->public_title .= ' (' . $this->code . '; Test)';
        }

        $this->description .= $this->getTestLinkInfo();
      }

      if ( !function_exists('curl_init') ) {
        $this->description = '<div class="secWarning">' . MODULE_PAYMENT_SAGE_PAY_DIRECT_ERROR_ADMIN_CURL . '</div>' . $this->description;

        $this->enabled = false;
      }

      if ( $this->enabled === true ) {
        if ( !tep_not_null(MODULE_PAYMENT_SAGE_PAY_DIRECT_VENDOR_LOGIN_NAME) ) {
          $this->description = '<div class="secWarning">' . MODULE_PAYMENT_SAGE_PAY_DIRECT_ERROR_ADMIN_CONFIGURATION . '</div>' . $this->description;

          $this->enabled = false;
        }
      }

      if ( $this->enabled === true ) {
        if ( isset($order) && is_object($order) ) {
          $this->update_status();
        }
      }

      if ( defined('FILENAME_MODULES') && ($PHP_SELF == FILENAME_MODULES) && isset($HTTP_GET_VARS['action']) && ($HTTP_GET_VARS['action'] == 'install') && isset($HTTP_GET_VARS['subaction']) && ($HTTP_GET_VARS['subaction'] == 'conntest') ) {
        echo $this->getTestConnectionResult();
        exit;
      }
    }

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
      global $customer_id, $payment;

      if ( (MODULE_PAYMENT_SAGE_PAY_DIRECT_TOKENS == 'True') && !tep_session_is_registered('payment') ) {
        $tokens_query = tep_db_query("select 1 from customers_sagepay_tokens where customers_id = '" . (int)$customer_id . "' limit 1");

        if ( tep_db_num_rows($tokens_query) ) {
          $payment = $this->code;
          tep_session_register('payment');
        }
      }

      return array('id' => $this->code,
                   'module' => $this->public_title);
    }

    function pre_confirmation_check() {
      if ( $this->templateClassExists() ) {
        $GLOBALS['oscTemplate']->addBlock($this->getSubmitCardDetailsJavascript(), 'header_tags');
      }
    }

    function confirmation() {
      global $order, $customer_id;

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

      $content = '';

      if ( MODULE_PAYMENT_SAGE_PAY_DIRECT_TOKENS == 'True' ) {
        $tokens_query = tep_db_query("select id, card_type, number_filtered, expiry_date from customers_sagepay_tokens where customers_id = '" . (int)$customer_id . "' order by date_added");

        if ( tep_db_num_rows($tokens_query) > 0 ) {
          $content .= '<table id="sagepay_table" border="0" width="100%" cellspacing="0" cellpadding="2">';

          while ( $tokens = tep_db_fetch_array($tokens_query) ) {
            $content .= '<tr class="moduleRow" id="sagepay_card_' . (int)$tokens['id'] . '">' . 
                        '  <td width="40" valign="top"><input type="radio" name="sagepay_card" value="' . (int)$tokens['id'] . '" /></td>' .
                        '  <td valign="top">' . tep_output_string_protected($tokens['number_filtered']) . '&nbsp;&nbsp;' . tep_output_string_protected(substr($tokens['expiry_date'], 0, 2)) . '/' . strftime('%Y', mktime(0, 0, 0, 1, 1, (2000 + substr($tokens['expiry_date'], 2)))) . '&nbsp;&nbsp;' . tep_output_string_protected($tokens['card_type']) . '</td>' .
                        '</tr>';

            if (MODULE_PAYMENT_SAGE_PAY_DIRECT_VERIFY_WITH_CVC == 'True') {
              $content .= '<tr class="moduleRowExtra" id="sagepay_card_cvc_' . (int)$tokens['id'] . '">' .
                          '  <td width="40" valign="top">&nbsp;</td>' .
                          '  <td valign="top">' . MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_CVC . '&nbsp;' . tep_draw_input_field('cc_cvc_tokens_nh-dns[' . (int)$tokens['id'] . ']', '', 'size="5" maxlength="4"') . '</td>' .
                          '</tr>';
            }
          }

          $content .= '<tr class="moduleRow" id="sagepay_card_0">' .
                      '  <td width="40" valign="top"><input type="radio" name="sagepay_card" value="0" /></td>' .
                      '  <td valign="top">' . MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_NEW . '</td>' .
                      '</tr>' .
                      '</table>';
        }
      }

      $content .= '<table id="sagepay_table_new_card" border="0" width="100%" cellspacing="0" cellpadding="2">' .
                  '<tr>' .
                  '  <td width="30%">' . MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_TYPE . '</td>' .
                  '  <td>' . tep_draw_pull_down_menu('cc_type', $card_types, '', 'id="sagepay_card_type"') . '</td>' .
                  '</tr>' .
                  '<tr>' .
                  '  <td width="30%">' . MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_OWNER . '</td>' .
                  '  <td>' . tep_draw_input_field('cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'], 'maxlength="50"') . '</td>' .
                  '</tr>' .
                  '<tr>' .
                  '  <td width="30%">' . MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_NUMBER . '</td>' .
                  '  <td>' . tep_draw_input_field('cc_number_nh-dns', '', 'maxlength="20"') . '</td>' .
                  '</tr>';

      if ( (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MAESTRO == 'True') || (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_AMEX == 'True') ) {
        $content .= '<tr>' .
                    '  <td width="30%">' . MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_STARTS . '</td>' .
                    '  <td>' . tep_draw_pull_down_menu('cc_starts_month', $months_array, '', 'id="sagepay_card_date_start"') . '&nbsp;' . tep_draw_pull_down_menu('cc_starts_year', $year_valid_from_array) . '&nbsp;' . MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_STARTS_INFO . '</td>' .
                    '</tr>';
      }

      $content .= '<tr>' .
                  '  <td width="30%">' . MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_EXPIRES . '</td>' .
                  '  <td>' . tep_draw_pull_down_menu('cc_expires_month', $months_array) . '&nbsp;' . tep_draw_pull_down_menu('cc_expires_year', $year_valid_to_array) . '</td>' .
                  '</tr>';

      if ( (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MAESTRO == 'True') ) {
        $content .= '<tr>' .
                    '  <td width="30%">' . MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_ISSUE_NUMBER . '</td>' .
                    '  <td>' . tep_draw_input_field('cc_issue_nh-dns', '', 'id="sagepay_card_issue" size="3" maxlength="2"') . '&nbsp;' . MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_ISSUE_NUMBER_INFO . '</td>' .
                    '</tr>';
      }

      if (MODULE_PAYMENT_SAGE_PAY_DIRECT_VERIFY_WITH_CVC == 'True') {
        $content .= '<tr>' .
                    '  <td width="30%">' . MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_CVC . '</td>' .
                    '  <td>' . tep_draw_input_field('cc_cvc_nh-dns', '', 'size="5" maxlength="4"') . '</td>' .
                    '</tr>';
      }

      if ( MODULE_PAYMENT_SAGE_PAY_DIRECT_TOKENS == 'True' ) {
        $content .= '<tr>' .
                    '  <td width="30%">&nbsp;</td>' .
                    '  <td>' . tep_draw_checkbox_field('cc_save', 'true') . ' ' . MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_SAVE . '</td>' .
                    '</tr>';
      }

      $content .= '</table>';

      $content .= !$this->templateClassExists() ? $this->getSubmitCardDetailsJavascript() : '';

      $confirmation = array('title' => $content);

      return $confirmation;
    }

    function process_button() {
      return false;
    }

    function before_process() {
      global $HTTP_GET_VARS, $HTTP_POST_VARS, $customer_id, $order, $currency, $order_totals, $cartID, $sage_pay_response;

      $transaction_response = null;
      $sage_pay_response = null;

      $error = null;

      if ( isset($HTTP_GET_VARS['check']) ) {
        if ( ($HTTP_GET_VARS['check'] == '3D') && isset($HTTP_POST_VARS['MD']) && tep_not_null($HTTP_POST_VARS['MD']) && isset($HTTP_POST_VARS['PaRes']) && tep_not_null($HTTP_POST_VARS['PaRes']) ) {
          if ( MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_SERVER == 'Live' ) {
            $gateway_url = 'https://live.sagepay.com/gateway/service/direct3dcallback.vsp';
          } else {
            $gateway_url = 'https://test.sagepay.com/gateway/service/direct3dcallback.vsp';
          }

          $post_string = 'MD=' . $HTTP_POST_VARS['MD'] . '&PARes=' . $HTTP_POST_VARS['PaRes'];

          $transaction_response = $this->sendTransactionToGateway($gateway_url, $post_string);
        } elseif ( ($HTTP_GET_VARS['check'] == 'PAYPAL') && isset($HTTP_POST_VARS['Status']) ) {
          if ( ($HTTP_POST_VARS['Status'] == 'PAYPALOK') && isset($HTTP_POST_VARS['VPSTxId']) && isset($HTTP_POST_VARS['CustomerEMail']) && isset($HTTP_POST_VARS['PayerID']) ) {
            $params = array('VPSProtocol' => $this->api_version,
                            'TxType' => 'COMPLETE',
                            'VPSTxId' => $HTTP_POST_VARS['VPSTxId'],
                            'Amount' => $this->format_raw($order->info['total']),
                            'Accept' => 'YES');

            $post_string = '';

            foreach ($params as $key => $value) {
              $post_string .= $key . '=' . urlencode(trim($value)) . '&';
            }

            if ( MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_SERVER == 'Live' ) {
              $gateway_url = 'https://live.sagepay.com/gateway/service/complete.vsp';
            } else {
              $gateway_url = 'https://test.sagepay.com/gateway/service/complete.vsp';
            }

            $transaction_response = $this->sendTransactionToGateway($gateway_url, $post_string);
          } elseif ( isset($HTTP_POST_VARS['StatusDetail']) && ($HTTP_POST_VARS['StatusDetail'] == 'Paypal transaction cancelled by client.') ) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'));
          }
        }
      } else {
        $sagepay_token = null;
        $sagepay_token_cvc = null;

        if ( MODULE_PAYMENT_SAGE_PAY_DIRECT_TOKENS == 'True' ) {
          if ( isset($HTTP_POST_VARS['sagepay_card']) && is_numeric($HTTP_POST_VARS['sagepay_card']) && ($HTTP_POST_VARS['sagepay_card'] > 0) ) {
            $token_query = tep_db_query("select sagepay_token from customers_sagepay_tokens where id = '" . (int)$HTTP_POST_VARS['sagepay_card'] . "' and customers_id = '" . (int)$customer_id . "'");

            if ( tep_db_num_rows($token_query) == 1 ) {
              $token = tep_db_fetch_array($token_query);

              $sagepay_token = $token['sagepay_token'];

              if ( isset($HTTP_POST_VARS['cc_cvc_tokens_nh-dns']) && is_array($HTTP_POST_VARS['cc_cvc_tokens_nh-dns']) && isset($HTTP_POST_VARS['cc_cvc_tokens_nh-dns'][$HTTP_POST_VARS['sagepay_card']]) ) {
                $sagepay_token_cvc = substr($HTTP_POST_VARS['cc_cvc_tokens_nh-dns'][$HTTP_POST_VARS['sagepay_card']], 0, 4);
              }
            }
          }
        }

        if ( !isset($sagepay_token) ) {
          $cc_type = isset($HTTP_POST_VARS['cc_type']) ? substr($HTTP_POST_VARS['cc_type'], 0, 15) : null;

          if ( !isset($cc_type) || ($this->isCard($cc_type) == false) ) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=cardtype', 'SSL'));
          }

          if ( $cc_type != 'PAYPAL' ) {
            $cc_owner = isset($HTTP_POST_VARS['cc_owner']) ? substr($HTTP_POST_VARS['cc_owner'], 0, 50) : null;
            $cc_number = isset($HTTP_POST_VARS['cc_number_nh-dns']) ? substr(preg_replace('/[^0-9]/', '', $HTTP_POST_VARS['cc_number_nh-dns']), 0, 20) : null;
            $cc_start = null;
            $cc_expires = null;
            $cc_issue = isset($HTTP_POST_VARS['cc_issue_nh-dns']) ? substr($HTTP_POST_VARS['cc_issue_nh-dns'], 0, 2) : null;
            $cc_cvc = isset($HTTP_POST_VARS['cc_cvc_nh-dns']) ? substr($HTTP_POST_VARS['cc_cvc_nh-dns'], 0, 4) : null;

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
              $year_valid_from_array[] = strftime('%y',mktime(0,0,0,1,1,$i));
            }

            if ( !isset($cc_owner) || empty($cc_owner) ) {
              tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=cardowner', 'SSL'));
            }

            if ( !isset($cc_number) || (is_numeric($cc_number) == false) ) {
              tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=cardnumber', 'SSL'));
            }

            if ( (($cc_type == 'MAESTRO') && (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MAESTRO == 'True')) || (($cc_type == 'AMEX') && (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_AMEX == 'True')) ) {
              if ( !isset($HTTP_POST_VARS['cc_starts_month']) || !in_array($HTTP_POST_VARS['cc_starts_month'], $months_array) ) {
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=cardstart', 'SSL'));
              }

              if ( !isset($HTTP_POST_VARS['cc_starts_year']) || !in_array($HTTP_POST_VARS['cc_starts_year'], $year_valid_from_array) ) {
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=cardstart', 'SSL'));
              }

              $cc_start = substr($HTTP_POST_VARS['cc_starts_month'] . $HTTP_POST_VARS['cc_starts_year'], 0, 4);
            }

            if ( !isset($HTTP_POST_VARS['cc_expires_month']) || !in_array($HTTP_POST_VARS['cc_expires_month'], $months_array) ) {
              tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=cardexpires', 'SSL'));
            }

            if ( !isset($HTTP_POST_VARS['cc_expires_year']) || !in_array($HTTP_POST_VARS['cc_expires_year'], $year_valid_to_array) ) {
              tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=cardexpires', 'SSL'));
            }

            if ( ($HTTP_POST_VARS['cc_expires_year'] == date('y')) && ($HTTP_POST_VARS['cc_expires_month'] < date('m')) ) {
              tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=cardexpires', 'SSL'));
            }

            $cc_expires = substr($HTTP_POST_VARS['cc_expires_month'] . $HTTP_POST_VARS['cc_expires_year'], 0, 4);

            if ( (($cc_type == 'MAESTRO') && (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MAESTRO == 'True')) ) {
              if ( !isset($cc_issue) || empty($cc_issue) ) {
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=cardissue', 'SSL'));
              }
            }

            if (MODULE_PAYMENT_SAGE_PAY_DIRECT_VERIFY_WITH_CVC == 'True') {
              if ( !isset($cc_cvc) || empty($cc_cvc) ) {
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=cardcvc', 'SSL'));
              }
            }
          }
        }

        $params = array('VPSProtocol' => $this->api_version,
                        'ReferrerID' => 'C74D7B82-E9EB-4FBD-93DB-76F0F551C802',
                        'Vendor' => substr(MODULE_PAYMENT_SAGE_PAY_DIRECT_VENDOR_LOGIN_NAME, 0, 15),
                        'VendorTxCode' => substr(date('YmdHis') . '-' . $customer_id . '-' . $cartID, 0, 40),
                        'Amount' => $this->format_raw($order->info['total']),
                        'Currency' => $currency,
                        'Description' => substr(STORE_NAME, 0, 100),
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
                        'VendorData' => 'Customer ID ' . $customer_id);

        if ( isset($sagepay_token) ) {
          $params['Token'] = $sagepay_token;
          $params['StoreToken'] = '1';

          if (MODULE_PAYMENT_SAGE_PAY_DIRECT_VERIFY_WITH_CVC == 'True') {
            $params['CV2'] = $sagepay_token_cvc;
          }
        } else {
          $params['CardType'] = $cc_type;

          if ( $cc_type == 'PAYPAL' ) {
            $params['PayPalCallbackURL'] = tep_href_link(FILENAME_CHECKOUT_PROCESS, 'check=PAYPAL', 'SSL');
          } else {
            $params['CardHolder'] = $cc_owner;
            $params['CardNumber'] = $cc_number;
            $params['ExpiryDate'] = $cc_expires;
            $params['CreateToken'] = ((MODULE_PAYMENT_SAGE_PAY_DIRECT_TOKENS == 'True') && isset($HTTP_POST_VARS['cc_save']) && ($HTTP_POST_VARS['cc_save'] == 'true') ? '1' : '0');

            if ( (($cc_type == 'MAESTRO') && (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MAESTRO == 'True')) || (($cc_type == 'AMEX') && (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_AMEX == 'True')) ) {
              $params['StartDate'] = $cc_start;
            }

            if ( (($cc_type == 'MAESTRO') && (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MAESTRO == 'True')) ) {
              $params['IssueNumber'] = $cc_issue;
            }

            if (MODULE_PAYMENT_SAGE_PAY_DIRECT_VERIFY_WITH_CVC == 'True') {
              $params['CV2'] = $cc_cvc;
            }
          }
        }

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

        if ( MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_SERVER == 'Live' ) {
          $gateway_url = 'https://live.sagepay.com/gateway/service/vspdirect-register.vsp';
        } else {
          $gateway_url = 'https://test.sagepay.com/gateway/service/vspdirect-register.vsp';
        }

        $transaction_response = $this->sendTransactionToGateway($gateway_url, $post_string);
      }

      $string_array = explode(chr(10), $transaction_response);
      $sage_pay_response = array();

      foreach ($string_array as $string) {
        if (strpos($string, '=') != false) {
          $parts = explode('=', $string, 2);
          $sage_pay_response[trim($parts[0])] = trim($parts[1]);
        }
      }

      if ( isset($params['CreateToken']) && ($params['CreateToken'] == '1') ) {
        global $sagepay_token_cc_type, $sagepay_token_cc_number, $sagepay_token_cc_expiry_date;

        tep_session_register('sagepay_token_cc_type');
        $sagepay_token_cc_type = $params['CardType'];

        tep_session_register('sagepay_token_cc_number');
        $sagepay_token_cc_number = str_repeat('X', strlen($params['CardNumber']) - 4) . substr($params['CardNumber'], -4);

        tep_session_register('sagepay_token_cc_expiry_date');
        $sagepay_token_cc_expiry_date = $params['ExpiryDate'];
      }

      if ($sage_pay_response['Status'] == '3DAUTH') {
        global $sage_pay_direct_acsurl, $sage_pay_direct_pareq, $sage_pay_direct_md;

        tep_session_register('sage_pay_direct_acsurl');
        $sage_pay_direct_acsurl = $sage_pay_response['ACSURL'];

        tep_session_register('sage_pay_direct_pareq');
        $sage_pay_direct_pareq = $sage_pay_response['PAReq'];

        tep_session_register('sage_pay_direct_md');
        $sage_pay_direct_md = $sage_pay_response['MD'];

        tep_redirect(tep_href_link('ext/modules/payment/sage_pay/checkout.php', '', 'SSL'));
      }

      if ($sage_pay_response['Status'] == 'PPREDIRECT') {
        tep_redirect($sage_pay_response['PayPalRedirectURL']);
      }

      if ( ($sage_pay_response['Status'] != 'OK') && ($sage_pay_response['Status'] != 'AUTHENTICATED') && ($sage_pay_response['Status'] != 'REGISTERED') ) {
          $this->sendDebugEmail($sage_pay_response);

        $error = $this->getErrorMessageNumber($sage_pay_response['StatusDetail']);

        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . (tep_not_null($error) ? '&error=' . $error : ''), 'SSL'));
      }
    }

    function after_process() {
      global $HTTP_GET_VARS, $HTTP_POST_VARS, $customer_id, $insert_id, $sage_pay_response;

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

      if ( isset($sage_pay_response['Token']) && tep_session_is_registered('sagepay_token_cc_number') ) {
        global $sagepay_token_cc_type, $sagepay_token_cc_number, $sagepay_token_cc_expiry_date;

        $check_query = tep_db_query("select id from customers_sagepay_tokens where customers_id = '" . (int)$customer_id . "' and sagepay_token = '" . tep_db_input($sage_pay_response['Token']) . "' limit 1");
        if ( tep_db_num_rows($check_query) < 1 ) {
          $sql_data_array = array('customers_id' => $customer_id,
                                  'sagepay_token' => $sage_pay_response['Token'],
                                  'card_type' => $sagepay_token_cc_type,
                                  'number_filtered' => $sagepay_token_cc_number,
                                  'expiry_date' => $sagepay_token_cc_expiry_date,
                                  'date_added' => 'now()');

          tep_db_perform('customers_sagepay_tokens', $sql_data_array);
        }

        $result['Token Created'] = 'Yes';

        tep_session_unregister('sagepay_token_cc_type');
        tep_session_unregister('sagepay_token_cc_number');
        tep_session_unregister('sagepay_token_cc_expiry_date');
      }

      if ( isset($HTTP_GET_VARS['check']) && ($HTTP_GET_VARS['check'] == 'PAYPAL') && isset($HTTP_POST_VARS['Status']) && ($HTTP_POST_VARS['Status'] == 'PAYPALOK') && isset($HTTP_POST_VARS['VPSTxId']) && isset($sage_pay_response['VPSTxId']) && ($HTTP_POST_VARS['VPSTxId'] == $sage_pay_response['VPSTxId']) ) {
        $result['PayPal Payer E-Mail'] = $HTTP_POST_VARS['CustomerEMail'];
        $result['PayPal Payer Status'] = $HTTP_POST_VARS['PayerStatus'];
        $result['PayPal Payer ID'] = $HTTP_POST_VARS['PayerID'];
        $result['PayPal Payer Address'] = $HTTP_POST_VARS['AddressStatus'];
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
      if ( tep_db_num_rows(tep_db_query("show tables like 'customers_sagepay_tokens'")) != 1 ) {
        $sql = <<<EOD
CREATE TABLE customers_sagepay_tokens (
  id int NOT NULL auto_increment,
  customers_id int NOT NULL,
  sagepay_token char(38) NOT NULL,
  card_type varchar(15) NOT NULL,
  number_filtered varchar(20) NOT NULL,
  expiry_date char(4) NOT NULL,
  date_added datetime NOT NULL,
  PRIMARY KEY (id),
  KEY idx_csagepayt_customers_id (customers_id),
  KEY idx_csagepayt_token (sagepay_token)
);
EOD;

        tep_db_query($sql);
      }

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
                                                                       'value' => 'True',
                                                                       'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_VENDOR_LOGIN_NAME' => array('title' => 'Vendor Login Name',
                                                                                  'desc' => 'The vendor login name to connect to the gateway with.',
                                                                                  'value' => ''),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_VERIFY_WITH_CVC' => array('title' => 'Verify With CVC',
                                                                                'desc' => 'Verify the credit card with the billing address with the Credit Card Verification Checknumber (CVC)?',
                                                                                'value' => 'True',
                                                                                'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_TOKENS' => array('title' => 'Create Tokens',
                                                                       'desc' => 'Create and store tokens for card payments customer can use on their next purchase?',
                                                                       'value' => 'False',
                                                                       'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_METHOD' => array('title' => 'Transaction Method',
                                                                                   'desc' => 'The processing method to use for each transaction.',
                                                                                   'value' => 'Authenticate',
                                                                                   'set_func' => 'tep_cfg_select_option(array(\'Authenticate\', \'Deferred\', \'Payment\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_ORDER_STATUS_ID' => array('title' => 'Set Order Status',
                                                                                'desc' => 'Set the status of orders made with this payment module to this value',
                                                                                'value' => '0',
                                                                                'use_func' => 'tep_get_order_status_name',
                                                                                'set_func' => 'tep_cfg_pull_down_order_statuses('),
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
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_SERVER' => array('title' => 'Transaction Server',
                                                                                   'desc' => 'Perform transactions on the production server or on the testing server.',
                                                                                   'value' => 'Live',
                                                                                   'set_func' => 'tep_cfg_select_option(array(\'Live\', \'Test\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_VERIFY_SSL' => array('title' => 'Verify SSL Certificate',
                                                                           'desc' => 'Verify transaction server SSL certificate on connection?',
                                                                           'value' => 'True',
                                                                           'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_PROXY' => array('title' => 'Proxy Server',
                                                                      'desc' => 'Send API requests through this proxy server. (host:port, eg: 123.45.67.89:8080 or proxy.example.com:8080)'),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_DEBUG_EMAIL' => array('title' => 'Debug E-Mail Address',
                                                                            'desc' => 'All parameters of an invalid transaction will be sent to this email address.'),
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
                                                                            'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_PAYPAL' => array('title' => 'Accept PayPal',
                                                                             'desc' => 'Do you want to accept PayPal payments?',
                                                                             'value' => 'False',
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

      $curl = curl_init($server['scheme'] . '://' . $server['host'] . $server['path'] . (isset($server['query']) ? '?' . $server['query'] : ''));
      curl_setopt($curl, CURLOPT_PORT, $server['port']);
      curl_setopt($curl, CURLOPT_HEADER, false);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
      curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);

      if ( MODULE_PAYMENT_SAGE_PAY_DIRECT_VERIFY_SSL == 'True' ) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

        if ( file_exists(DIR_FS_CATALOG . 'ext/modules/payment/sage_pay/sagepay.com.crt') ) {
          curl_setopt($curl, CURLOPT_CAINFO, DIR_FS_CATALOG . 'ext/modules/payment/sage_pay/sagepay.com.crt');
        } elseif ( file_exists(DIR_FS_CATALOG . 'includes/cacert.pem') ) {
          curl_setopt($curl, CURLOPT_CAINFO, DIR_FS_CATALOG . 'includes/cacert.pem');
        }
      } else {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      }

      if ( tep_not_null(MODULE_PAYMENT_SAGE_PAY_DIRECT_PROXY) ) {
        curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, true);
        curl_setopt($curl, CURLOPT_PROXY, MODULE_PAYMENT_SAGE_PAY_DIRECT_PROXY);
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

      if (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_PAYPAL == 'True') {
        $this->_cards['PAYPAL'] = 'PayPal';
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

    function deleteCard($token, $token_id) {
      global $customer_id;

      if ( MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_SERVER == 'Live' ) {
        $gateway_url = 'https://live.sagepay.com/gateway/service/removetoken.vsp';
      } else {
        $gateway_url = 'https://test.sagepay.com/gateway/service/removetoken.vsp';
      }

      $params = array('VPSProtocol' => $this->api_version,
                      'TxType' => 'REMOVETOKEN',
                      'Vendor' => substr(MODULE_PAYMENT_SAGE_PAY_DIRECT_VENDOR_LOGIN_NAME, 0, 15),
                      'Token' => $token);

      $post_string = '';

      foreach ($params as $key => $value) {
        $post_string .= $key . '=' . urlencode(trim($value)) . '&';
      }

      $response = $this->sendTransactionToGateway($gateway_url, $post_string);

      $string_array = explode(chr(10), $response);
      $sage_pay_response = array();

      foreach ($string_array as $string) {
        if (strpos($string, '=') != false) {
          $parts = explode('=', $string, 2);
          $sage_pay_response[trim($parts[0])] = trim($parts[1]);
        }
      }

      tep_db_query("delete from customers_sagepay_tokens where id = '" . (int)$token_id . "' and customers_id = '" . (int)$customer_id . "' and sagepay_token = '" . tep_db_prepare_input(tep_db_input($token)) . "'");

      return (tep_db_affected_rows() === 1);
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

    function getTestLinkInfo() {
      $dialog_title = MODULE_PAYMENT_SAGE_PAY_DIRECT_DIALOG_CONNECTION_TITLE;
      $dialog_button_close = MODULE_PAYMENT_SAGE_PAY_DIRECT_DIALOG_CONNECTION_BUTTON_CLOSE;
      $dialog_success = MODULE_PAYMENT_SAGE_PAY_DIRECT_DIALOG_CONNECTION_SUCCESS;
      $dialog_failed = MODULE_PAYMENT_SAGE_PAY_DIRECT_DIALOG_CONNECTION_FAILED;
      $dialog_error = MODULE_PAYMENT_SAGE_PAY_DIRECT_DIALOG_CONNECTION_ERROR;
      $dialog_connection_time = MODULE_PAYMENT_SAGE_PAY_DIRECT_DIALOG_CONNECTION_TIME;

      $test_url = tep_href_link(FILENAME_MODULES, 'set=payment&module=' . $this->code . '&action=install&subaction=conntest');

      $js = <<<EOD
<script type="text/javascript">
$(function() {
  $('#tcdprogressbar').progressbar({
    value: false
  });
});

function openTestConnectionDialog() {
  var d = $('<div>').html($('#testConnectionDialog').html()).dialog({
    modal: true,
    title: '{$dialog_title}',
    buttons: {
      '{$dialog_button_close}': function () {
        $(this).dialog('destroy');
      }
    }
  });

  var timeStart = new Date().getTime();

  $.ajax({
    url: '{$test_url}'
  }).done(function(data) {
    if ( data == '1' ) {
      d.find('#testConnectionDialogProgress').html('<p style="font-weight: bold; color: green;">{$dialog_success}</p>');
    } else {
      d.find('#testConnectionDialogProgress').html('<p style="font-weight: bold; color: red;">{$dialog_failed}</p>');
    }
  }).fail(function() {
    d.find('#testConnectionDialogProgress').html('<p style="font-weight: bold; color: red;">{$dialog_error}</p>');
  }).always(function() {
    var timeEnd = new Date().getTime();
    var timeTook = new Date(0, 0, 0, 0, 0, 0, timeEnd-timeStart);

    d.find('#testConnectionDialogProgress').append('<p>{$dialog_connection_time} ' + timeTook.getSeconds() + '.' + timeTook.getMilliseconds() + 's</p>');
  });
}
</script>
EOD;

      $info = '<p><img src="images/icons/locked.gif" border="0">&nbsp;<a href="javascript:openTestConnectionDialog();" style="text-decoration: underline; font-weight: bold;">' . MODULE_PAYMENT_SAGE_PAY_DIRECT_DIALOG_CONNECTION_LINK_TITLE . '</a></p>' .
              '<div id="testConnectionDialog" style="display: none;"><p>';

      if ( MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_SERVER == 'Live' ) {
        $info .= 'Live Server:<br />https://live.sagepay.com/gateway/service/vspdirect-register.vsp';
      } else {
        $info .= 'Test Server:<br />https://test.sagepay.com/gateway/service/vspdirect-register.vsp';
      }

      $info .= '</p><div id="testConnectionDialogProgress"><p>' . MODULE_PAYMENT_SAGE_PAY_DIRECT_DIALOG_CONNECTION_GENERAL_TEXT . '</p><div id="tcdprogressbar"></div></div></div>' .
               $js;

      return $info;
    }

    function getTestConnectionResult() {
      if ( MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_SERVER == 'Live' ) {
        $gateway_url = 'https://live.sagepay.com/gateway/service/vspdirect-register.vsp';
      } else {
        $gateway_url = 'https://test.sagepay.com/gateway/service/vspdirect-register.vsp';
      }

      $params = array('VPSProtocol' => $this->api_version,
                      'ReferrerID' => 'C74D7B82-E9EB-4FBD-93DB-76F0F551C802',
                      'Vendor' => substr(MODULE_PAYMENT_SAGE_PAY_DIRECT_VENDOR_LOGIN_NAME, 0, 15),
                      'Amount' => 0,
                      'Currency' => DEFAULT_CURRENCY);

      $ip_address = tep_get_ip_address();

      if ( !empty($ip_address) && (ip2long($ip_address) != -1) && (ip2long($ip_address) != false) ) {
        $params['ClientIPAddress']= $ip_address;
      }

      $post_string = '';

      foreach ($params as $key => $value) {
        $post_string .= $key . '=' . urlencode(trim($value)) . '&';
      }

      $response = $this->sendTransactionToGateway($gateway_url, $post_string);

      if ( $response != false ) {
        return 1;
      }

      return -1;
    }

    function templateClassExists() {
      return class_exists('oscTemplate') && isset($GLOBALS['oscTemplate']) && is_object($GLOBALS['oscTemplate']) && (get_class($GLOBALS['oscTemplate']) == 'oscTemplate');
    }

    function getSubmitCardDetailsJavascript() {
      $js = <<<EOD
<script type="text/javascript">
$(function() {
  if ( $('#sagepay_table').length > 0 ) {
    if ( typeof($('#sagepay_table').parent().closest('table').attr('width')) == 'undefined' ) {
      $('#sagepay_table').parent().closest('table').attr('width', '100%');
    }

    $('#sagepay_table .moduleRowExtra').hide();

    $('#sagepay_table_new_card').hide();

    $('form[name="checkout_confirmation"] input[name="sagepay_card"]').change(function() {
      var selected = $(this).val();

      if ( selected == '0' ) {
        sagepayShowNewCardFields();
      } else {
        $('#sagepay_table_new_card').hide();

        $('[id^="sagepay_card_cvc_"]').hide();

        $('#sagepay_card_cvc_' + selected).show();
      }

      $('tr[id^="sagepay_card_"]').removeClass('moduleRowSelected');
      $('#sagepay_card_' + selected).addClass('moduleRowSelected');
    });

    $('form[name="checkout_confirmation"] input[name="sagepay_card"]:first').prop('checked', true).trigger('change');

    $('#sagepay_table .moduleRow').hover(function() {
      $(this).addClass('moduleRowOver');
    }, function() {
      $(this).removeClass('moduleRowOver');
    }).click(function(event) {
      var target = $(event.target);

      if ( !target.is('input:radio') ) {
        $(this).find('input:radio').each(function() {
          if ( $(this).prop('checked') == false ) {
            $(this).prop('checked', true).trigger('change');
          }
        });
      }
    });
  } else {
    if ( typeof($('#sagepay_table_new_card').parent().closest('table').attr('width')) == 'undefined' ) {
      $('#sagepay_table_new_card').parent().closest('table').attr('width', '100%');
    }

    sagepayShowNewCardFields();
  }

  $('#sagepay_card_type').change(function() {
    var selected = $(this).val();

    if ( selected == 'PAYPAL' ) {
      $('#sagepay_table_new_card input[name="cc_owner"]').parent().parent().hide();
      $('#sagepay_table_new_card input[name="cc_number_nh-dns"]').parent().parent().hide();
      $('#sagepay_table_new_card select[name="cc_expires_month"]').parent().parent().hide();
      $('#sagepay_table_new_card select[name="cc_expires_year"]').parent().parent().hide();

      if ( $('#sagepay_table_new_card input[name="cc_cvc_nh-dns"]').length > 0 ) {
        $('#sagepay_table_new_card input[name="cc_cvc_nh-dns"]').parent().parent().hide();
      }

      if ( $('#sagepay_table_new_card input[name="cc_save"]').length > 0 ) {
        $('#sagepay_table_new_card input[name="cc_save"]').parent().parent().hide();
      }
    } else {
      $('#sagepay_table_new_card input[name="cc_owner"]').parent().parent().show();
      $('#sagepay_table_new_card input[name="cc_number_nh-dns"]').parent().parent().show();
      $('#sagepay_table_new_card select[name="cc_expires_month"]').parent().parent().show();
      $('#sagepay_table_new_card select[name="cc_expires_year"]').parent().parent().show();

      if ( $('#sagepay_table_new_card input[name="cc_cvc_nh-dns"]').length > 0 ) {
        $('#sagepay_table_new_card input[name="cc_cvc_nh-dns"]').parent().parent().show();
      }

      if ( $('#sagepay_table_new_card input[name="cc_save"]').length > 0 ) {
        $('#sagepay_table_new_card input[name="cc_save"]').parent().parent().show();
      }
    }

    if ( $('#sagepay_card_date_start').length > 0 ) {
      if ( selected == 'MAESTRO' || selected == 'AMEX' ) {
        $('#sagepay_card_date_start').parent().parent().show();
      } else {
        $('#sagepay_card_date_start').parent().parent().hide();
      }
    }

    if ( $('#sagepay_card_issue').length > 0 ) {
      if ( selected == 'MAESTRO' ) {
        $('#sagepay_card_issue').parent().parent().show();
      } else {
        $('#sagepay_card_issue').parent().parent().hide();
      }
    }
  });
});

function sagepayShowNewCardFields() {
  var sagepay_card_type_default = $('#sagepay_card_type').val();

  $('[id^="sagepay_card_cvc_"]').hide();

  $('#sagepay_table_new_card').show();

  if ( $('#sagepay_card_date_start').length > 0 ) {
    if ( sagepay_card_type_default != 'MAESTRO' || sagepay_card_type_default != 'AMEX' ) {
      $('#sagepay_card_date_start').parent().parent().hide();
    }
  }

  if ( $('#sagepay_card_issue').length > 0 ) {
    if ( sagepay_card_type_default != 'MAESTRO' ) {
      $('#sagepay_card_issue').parent().parent().hide();
    }
  }
}
</script>
EOD;

      return $js;
    }

    function sendDebugEmail($response = array()) {
      global $HTTP_POST_VARS, $HTTP_GET_VARS;

      if (tep_not_null(MODULE_PAYMENT_SAGE_PAY_DIRECT_DEBUG_EMAIL)) {
        $email_body = '';

        if (!empty($response)) {
          $email_body .= 'RESPONSE:' . "\n\n" . print_r($response, true) . "\n\n";
        }

        if (!empty($HTTP_POST_VARS)) {
          if (isset($HTTP_POST_VARS['cc_number_nh-dns'])) {
            $HTTP_POST_VARS['cc_number_nh-dns'] = 'XXXX' . substr($HTTP_POST_VARS['cc_number_nh-dns'], -4);
          }

          if (isset($HTTP_POST_VARS['cc_cvc_tokens_nh-dns'])) {
            $HTTP_POST_VARS['cc_cvc_tokens_nh-dns'] = 'XXX';
          }

          if (isset($HTTP_POST_VARS['cc_cvc_nh-dns'])) {
            $HTTP_POST_VARS['cc_cvc_nh-dns'] = 'XXX';
          }

          if (isset($HTTP_POST_VARS['cc_issue_nh-dns'])) {
            $HTTP_POST_VARS['cc_issue_nh-dns'] = 'XXX';
          }

          if (isset($HTTP_POST_VARS['cc_expires_month'])) {
            $HTTP_POST_VARS['cc_expires_month'] = 'XX';
          }

          if (isset($HTTP_POST_VARS['cc_expires_year'])) {
            $HTTP_POST_VARS['cc_expires_year'] = 'XX';
          }

          if (isset($HTTP_POST_VARS['cc_starts_month'])) {
            $HTTP_POST_VARS['cc_starts_month'] = 'XX';
          }

          if (isset($HTTP_POST_VARS['cc_starts_year'])) {
            $HTTP_POST_VARS['cc_starts_year'] = 'XX';
          }

          $email_body .= '$HTTP_POST_VARS:' . "\n\n" . print_r($HTTP_POST_VARS, true) . "\n\n";
        }

        if (!empty($HTTP_GET_VARS)) {
          $email_body .= '$HTTP_GET_VARS:' . "\n\n" . print_r($HTTP_GET_VARS, true) . "\n\n";
        }

        if (!empty($email_body)) {
          tep_mail('', MODULE_PAYMENT_SAGE_PAY_DIRECT_DEBUG_EMAIL, 'Sage Pay Direct Debug E-Mail', trim($email_body), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
        }
      }
    }
  }
?>
