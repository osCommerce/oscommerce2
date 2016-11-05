<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\HTTP;
  use OSC\OM\Mail;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class sage_pay_direct {
    var $code, $title, $description, $enabled;

    function __construct() {
      global $PHP_SELF, $order;

      $this->signature = 'sage_pay|sage_pay_direct|3.1|2.3';
      $this->api_version = '3.00';

      $this->code = 'sage_pay_direct';
      $this->title = OSCOM::getDef('module_payment_sage_pay_direct_text_title');
      $this->public_title = OSCOM::getDef('module_payment_sage_pay_direct_text_public_title');
      $this->description = OSCOM::getDef('module_payment_sage_pay_direct_text_description');
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
        $this->description = '<div class="secWarning">' . OSCOM::getDef('module_payment_sage_pay_direct_error_admin_curl') . '</div>' . $this->description;

        $this->enabled = false;
      }

      if ( $this->enabled === true ) {
        if ( !tep_not_null(MODULE_PAYMENT_SAGE_PAY_DIRECT_VENDOR_LOGIN_NAME) ) {
          $this->description = '<div class="secWarning">' . OSCOM::getDef('module_payment_sage_pay_direct_error_admin_configuration') . '</div>' . $this->description;

          $this->enabled = false;
        }
      }

      if ( $this->enabled === true ) {
        if ( isset($order) && is_object($order) ) {
          $this->update_status();
        }
      }

      if ( defined('FILENAME_MODULES') && (basename($PHP_SELF) == 'modules.php') && isset($_GET['action']) && ($_GET['action'] == 'install') && isset($_GET['subaction']) && ($_GET['subaction'] == 'conntest') ) {
        echo $this->getTestConnectionResult();
        exit;
      }
    }

    function update_status() {
      global $order;

      $OSCOM_Db = Registry::get('Db');

      if ( ($this->enabled == true) && ($this->hasCards() == false) ) {
        $this->enabled = false;
      }

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_SAGE_PAY_DIRECT_ZONE > 0) ) {
        $check_flag = false;
        $Qcheck = $OSCOM_Db->get('zones_to_geo_zones', 'zone_id', ['geo_zone_id' => MODULE_PAYMENT_SAGE_PAY_DIRECT_ZONE, 'zone_country_id' => $order->billing['country']['id']], 'zone_id');
        while ($Qcheck->fetch()) {
          if ($Qcheck->valueInt('zone_id') < 1) {
            $check_flag = true;
            break;
          } elseif ($Qcheck->valueInt('zone_id') == $order->billing['zone_id']) {
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
      $OSCOM_Db = Registry::get('Db');

      if ( (MODULE_PAYMENT_SAGE_PAY_DIRECT_TOKENS == 'True') && !isset($_SESSION['payment']) ) {
        $Qtokens = $OSCOM_Db->get('customers_sagepay_tokens', '1', ['customers_id' => $_SESSION['customer_id']], null, 1);

        if ( $Qtokens->fetch() !== false ) {
          $_SESSION['payment'] = $this->code;
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
      global $order;

      $OSCOM_Db = Registry::get('Db');

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
        $Qtokens = $OSCOM_Db->get('customers_sagepay_tokens', ['id', 'card_type', 'number_filtered', 'expiry_date'], ['customers_id' => $_SESSION['customer_id']], 'date_added');

        if ($Qtokens->fetch() !== false) {
          $content .= '<table id="sagepay_table" border="0" width="100%" cellspacing="0" cellpadding="2">';

          do {
            $content .= '<tr class="moduleRow" id="sagepay_card_' . $Qtokens->valueInt('id') . '">' .
                        '  <td width="40" valign="top"><input type="radio" name="sagepay_card" value="' . $Qtokens->valueInt('id') . '" /></td>' .
                        '  <td valign="top">' . $Qtokens->valueProtected('number_filtered') . '&nbsp;&nbsp;' . HTML::outputProtected(substr($Qtokens->value('expiry_date'), 0, 2)) . '/' . strftime('%Y', mktime(0, 0, 0, 1, 1, (2000 + substr($Qtokens->value('expiry_date'), 2)))) . '&nbsp;&nbsp;' . $Qtokens->valueProtected('card_type') . '</td>' .
                        '</tr>';

            if (MODULE_PAYMENT_SAGE_PAY_DIRECT_VERIFY_WITH_CVC == 'True') {
              $content .= '<tr class="moduleRowExtra" id="sagepay_card_cvc_' . $Qtokens->valueInt('id') . '">' .
                          '  <td width="40" valign="top">&nbsp;</td>' .
                          '  <td valign="top">' . OSCOM::getDef('module_payment_sage_pay_direct_credit_card_cvc') . '&nbsp;' . HTML::inputField('cc_cvc_tokens_nh-dns[' . $Qtokens->valueInt('id') . ']', '', 'size="5" maxlength="4"') . '</td>' .
                          '</tr>';
            }
          } while ($Qtokens->fetch());

          $content .= '<tr class="moduleRow" id="sagepay_card_0">' .
                      '  <td width="40" valign="top"><input type="radio" name="sagepay_card" value="0" /></td>' .
                      '  <td valign="top">' . OSCOM::getDef('module_payment_sage_pay_direct_credit_card_new') . '</td>' .
                      '</tr>' .
                      '</table>';
        }
      }

      $content .= '<table id="sagepay_table_new_card" border="0" width="100%" cellspacing="0" cellpadding="2">' .
                  '<tr>' .
                  '  <td width="30%">' . OSCOM::getDef('module_payment_sage_pay_direct_credit_card_type') . '</td>' .
                  '  <td>' . HTML::selectField('cc_type', $card_types, '', 'id="sagepay_card_type"') . '</td>' .
                  '</tr>' .
                  '<tr>' .
                  '  <td width="30%">' . OSCOM::getDef('module_payment_sage_pay_direct_credit_card_owner') . '</td>' .
                  '  <td>' . HTML::inputField('cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'], 'maxlength="50"') . '</td>' .
                  '</tr>' .
                  '<tr>' .
                  '  <td width="30%">' . OSCOM::getDef('module_payment_sage_pay_direct_credit_card_number') . '</td>' .
                  '  <td>' . HTML::inputField('cc_number_nh-dns', '', 'maxlength="20"') . '</td>' .
                  '</tr>';

      if ( (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MAESTRO == 'True') || (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_AMEX == 'True') ) {
        $content .= '<tr>' .
                    '  <td width="30%">' . OSCOM::getDef('module_payment_sage_pay_direct_credit_card_starts') . '</td>' .
                    '  <td>' . HTML::selectField('cc_starts_month', $months_array, '', 'id="sagepay_card_date_start"') . '&nbsp;' . HTML::selectField('cc_starts_year', $year_valid_from_array) . '&nbsp;' . OSCOM::getDef('module_payment_sage_pay_direct_credit_card_starts_info') . '</td>' .
                    '</tr>';
      }

      $content .= '<tr>' .
                  '  <td width="30%">' . OSCOM::getDef('module_payment_sage_pay_direct_credit_card_expires') . '</td>' .
                  '  <td>' . HTML::selectField('cc_expires_month', $months_array) . '&nbsp;' . HTML::selectField('cc_expires_year', $year_valid_to_array) . '</td>' .
                  '</tr>';

      if ( (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MAESTRO == 'True') ) {
        $content .= '<tr>' .
                    '  <td width="30%">' . OSCOM::getDef('module_payment_sage_pay_direct_credit_card_issue_number') . '</td>' .
                    '  <td>' . HTML::inputField('cc_issue_nh-dns', '', 'id="sagepay_card_issue" size="3" maxlength="2"') . '&nbsp;' . OSCOM::getDef('module_payment_sage_pay_direct_credit_card_issue_number_info') . '</td>' .
                    '</tr>';
      }

      if (MODULE_PAYMENT_SAGE_PAY_DIRECT_VERIFY_WITH_CVC == 'True') {
        $content .= '<tr>' .
                    '  <td width="30%">' . OSCOM::getDef('module_payment_sage_pay_direct_credit_card_cvc') . '</td>' .
                    '  <td>' . HTML::inputField('cc_cvc_nh-dns', '', 'size="5" maxlength="4"') . '</td>' .
                    '</tr>';
      }

      if ( MODULE_PAYMENT_SAGE_PAY_DIRECT_TOKENS == 'True' ) {
        $content .= '<tr>' .
                    '  <td width="30%">&nbsp;</td>' .
                    '  <td>' . HTML::checkboxField('cc_save', 'true') . ' ' . OSCOM::getDef('module_payment_sage_pay_direct_credit_card_save') . '</td>' .
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
      global $order, $order_totals, $sage_pay_response;

      $OSCOM_Db = Registry::get('Db');

      $transaction_response = null;
      $sage_pay_response = null;

      $error = null;

      if ( isset($_GET['check']) ) {
        if ( ($_GET['check'] == '3D') && isset($_POST['MD']) && tep_not_null($_POST['MD']) && isset($_POST['PaRes']) && tep_not_null($_POST['PaRes']) ) {
          if ( MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_SERVER == 'Live' ) {
            $gateway_url = 'https://live.sagepay.com/gateway/service/direct3dcallback.vsp';
          } else {
            $gateway_url = 'https://test.sagepay.com/gateway/service/direct3dcallback.vsp';
          }

          $post_string = 'MD=' . $_POST['MD'] . '&PARes=' . $_POST['PaRes'];

          $transaction_response = $this->sendTransactionToGateway($gateway_url, $post_string);
        } elseif ( ($_GET['check'] == 'PAYPAL') && isset($_POST['Status']) ) {
          if ( ($_POST['Status'] == 'PAYPALOK') && isset($_POST['VPSTxId']) && isset($_POST['CustomerEMail']) && isset($_POST['PayerID']) ) {
            $params = array('VPSProtocol' => $this->api_version,
                            'TxType' => 'COMPLETE',
                            'VPSTxId' => $_POST['VPSTxId'],
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
          } elseif ( isset($_POST['StatusDetail']) && ($_POST['StatusDetail'] == 'Paypal transaction cancelled by client.') ) {
            OSCOM::redirect('checkout_confirmation.php');
          }
        }
      } else {
        $sagepay_token = null;
        $sagepay_token_cvc = null;

        if ( MODULE_PAYMENT_SAGE_PAY_DIRECT_TOKENS == 'True' ) {
          if ( isset($_POST['sagepay_card']) && is_numeric($_POST['sagepay_card']) && ($_POST['sagepay_card'] > 0) ) {
            $Qtoken = $OSCOM_Db->get('customers_sagepay_tokens', 'sagepay_token', ['id' => $_POST['sagepay_card'], 'customers_id' => $_SESSION['customer_id']]);

            if ( $Qtoken->fetch() !== false ) {
              $sagepay_token = $Qtoken->value('sagepay_token');

              if ( isset($_POST['cc_cvc_tokens_nh-dns']) && is_array($_POST['cc_cvc_tokens_nh-dns']) && isset($_POST['cc_cvc_tokens_nh-dns'][$_POST['sagepay_card']]) ) {
                $sagepay_token_cvc = substr($_POST['cc_cvc_tokens_nh-dns'][$_POST['sagepay_card']], 0, 4);
              }
            }
          }
        }

        if ( !isset($sagepay_token) ) {
          $cc_type = isset($_POST['cc_type']) ? substr($_POST['cc_type'], 0, 15) : null;

          if ( !isset($cc_type) || ($this->isCard($cc_type) == false) ) {
            OSCOM::redirect('checkout_payment.php', 'payment_error=' . $this->code . '&error=cardtype');
          }

          if ( $cc_type != 'PAYPAL' ) {
            $cc_owner = isset($_POST['cc_owner']) ? substr($_POST['cc_owner'], 0, 50) : null;
            $cc_number = isset($_POST['cc_number_nh-dns']) ? substr(preg_replace('/[^0-9]/', '', $_POST['cc_number_nh-dns']), 0, 20) : null;
            $cc_start = null;
            $cc_expires = null;
            $cc_issue = isset($_POST['cc_issue_nh-dns']) ? substr($_POST['cc_issue_nh-dns'], 0, 2) : null;
            $cc_cvc = isset($_POST['cc_cvc_nh-dns']) ? substr($_POST['cc_cvc_nh-dns'], 0, 4) : null;

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
              OSCOM::redirect('checkout_payment.php', 'payment_error=' . $this->code . '&error=cardowner');
            }

            if ( !isset($cc_number) || (is_numeric($cc_number) == false) ) {
              OSCOM::redirect('checkout_payment.php', 'payment_error=' . $this->code . '&error=cardnumber');
            }

            if ( (($cc_type == 'MAESTRO') && (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MAESTRO == 'True')) || (($cc_type == 'AMEX') && (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_AMEX == 'True')) ) {
              if ( !isset($_POST['cc_starts_month']) || !in_array($_POST['cc_starts_month'], $months_array) ) {
                OSCOM::redirect('checkout_payment.php', 'payment_error=' . $this->code . '&error=cardstart');
              }

              if ( !isset($_POST['cc_starts_year']) || !in_array($_POST['cc_starts_year'], $year_valid_from_array) ) {
                OSCOM::redirect('checkout_payment.php', 'payment_error=' . $this->code . '&error=cardstart');
              }

              $cc_start = substr($_POST['cc_starts_month'] . $_POST['cc_starts_year'], 0, 4);
            }

            if ( !isset($_POST['cc_expires_month']) || !in_array($_POST['cc_expires_month'], $months_array) ) {
              OSCOM::redirect('checkout_payment.php', 'payment_error=' . $this->code . '&error=cardexpires');
            }

            if ( !isset($_POST['cc_expires_year']) || !in_array($_POST['cc_expires_year'], $year_valid_to_array) ) {
              OSCOM::redirect('checkout_payment.php', 'payment_error=' . $this->code . '&error=cardexpires');
            }

            if ( ($_POST['cc_expires_year'] == date('y')) && ($_POST['cc_expires_month'] < date('m')) ) {
              OSCOM::redirect('checkout_payment.php', 'payment_error=' . $this->code . '&error=cardexpires');
            }

            $cc_expires = substr($_POST['cc_expires_month'] . $_POST['cc_expires_year'], 0, 4);

            if ( (($cc_type == 'MAESTRO') && (MODULE_PAYMENT_SAGE_PAY_DIRECT_ALLOW_MAESTRO == 'True')) ) {
              if ( !isset($cc_issue) || empty($cc_issue) ) {
                OSCOM::redirect('checkout_payment.php', 'payment_error=' . $this->code . '&error=cardissue');
              }
            }

            if (MODULE_PAYMENT_SAGE_PAY_DIRECT_VERIFY_WITH_CVC == 'True') {
              if ( !isset($cc_cvc) || empty($cc_cvc) ) {
                OSCOM::redirect('checkout_payment.php', 'payment_error=' . $this->code . '&error=cardcvc');
              }
            }
          }
        }

        $params = array('VPSProtocol' => $this->api_version,
                        'ReferrerID' => 'C74D7B82-E9EB-4FBD-93DB-76F0F551C802',
                        'Vendor' => substr(MODULE_PAYMENT_SAGE_PAY_DIRECT_VENDOR_LOGIN_NAME, 0, 15),
                        'VendorTxCode' => substr(date('YmdHis') . '-' . $_SESSION['customer_id'] . '-' . $_SESSION['cartID'], 0, 40),
                        'Amount' => $this->format_raw($order->info['total']),
                        'Currency' => $_SESSION['currency'],
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
                        'VendorData' => 'Customer ID ' . $_SESSION['customer_id']);

        if ( isset($sagepay_token) ) {
          $params['Token'] = $sagepay_token;
          $params['StoreToken'] = '1';

          if (MODULE_PAYMENT_SAGE_PAY_DIRECT_VERIFY_WITH_CVC == 'True') {
            $params['CV2'] = $sagepay_token_cvc;
          }
        } else {
          $params['CardType'] = $cc_type;

          if ( $cc_type == 'PAYPAL' ) {
            $params['PayPalCallbackURL'] = OSCOM::link('checkout_process.php', 'check=PAYPAL');
          } else {
            $params['CardHolder'] = $cc_owner;
            $params['CardNumber'] = $cc_number;
            $params['ExpiryDate'] = $cc_expires;
            $params['CreateToken'] = ((MODULE_PAYMENT_SAGE_PAY_DIRECT_TOKENS == 'True') && isset($_POST['cc_save']) && ($_POST['cc_save'] == 'true') ? '1' : '0');

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

        $ip_address = HTTP::getIpAddress();

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
        $_SESSION['sagepay_token_cc_type'] = $params['CardType'];
        $_SESSION['sagepay_token_cc_number'] = str_repeat('X', strlen($params['CardNumber']) - 4) . substr($params['CardNumber'], -4);
        $_SESSION['sagepay_token_cc_expiry_date'] = $params['ExpiryDate'];
      }

      if ($sage_pay_response['Status'] == '3DAUTH') {
        $_SESSION['sage_pay_direct_acsurl'] = $sage_pay_response['ACSURL'];
        $_SESSION['sage_pay_direct_pareq'] = $sage_pay_response['PAReq'];
        $_SESSION['sage_pay_direct_md'] = $sage_pay_response['MD'];

        OSCOM::redirect('ext/modules/payment/sage_pay/checkout.php');
      }

      if ($sage_pay_response['Status'] == 'PPREDIRECT') {
        HTTP::redirect($sage_pay_response['PayPalRedirectURL']);
      }

      if ( ($sage_pay_response['Status'] != 'OK') && ($sage_pay_response['Status'] != 'AUTHENTICATED') && ($sage_pay_response['Status'] != 'REGISTERED') ) {
          $this->sendDebugEmail($sage_pay_response);

        $error = $this->getErrorMessageNumber($sage_pay_response['StatusDetail']);

        OSCOM::redirect('checkout_payment.php', 'payment_error=' . $this->code . (tep_not_null($error) ? '&error=' . $error : ''));
      }
    }

    function after_process() {
      global $insert_id, $sage_pay_response;

      $OSCOM_Db = Registry::get('Db');

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

      if ( isset($sage_pay_response['Token']) && isset($_SESSION['sagepay_token_cc_number']) ) {
        $Qcheck = $OSCOM_Db->get('customers_sagepay_tokens', 'id', ['customers_id' => $_SESSION['customer_id'], 'sagepay_token' => $sage_pay_response['Token']], null, 1);

        if ($Qcheck->fetch() === false) {
          $sql_data_array = array('customers_id' => $_SESSION['customer_id'],
                                  'sagepay_token' => $sage_pay_response['Token'],
                                  'card_type' => $_SESSION['sagepay_token_cc_type'],
                                  'number_filtered' => $_SESSION['sagepay_token_cc_number'],
                                  'expiry_date' => $_SESSION['sagepay_token_cc_expiry_date'],
                                  'date_added' => 'now()');

          $OSCOM_Db->save('customers_sagepay_tokens', $sql_data_array);
        }

        $result['Token Created'] = 'Yes';

        unset($_SESSION['sagepay_token_cc_type']);
        unset($_SESSION['sagepay_token_cc_number']);
        unset($_SESSION['sagepay_token_cc_expiry_date']);
      }

      if ( isset($_GET['check']) && ($_GET['check'] == 'PAYPAL') && isset($_POST['Status']) && ($_POST['Status'] == 'PAYPALOK') && isset($_POST['VPSTxId']) && isset($sage_pay_response['VPSTxId']) && ($_POST['VPSTxId'] == $sage_pay_response['VPSTxId']) ) {
        $result['PayPal Payer E-Mail'] = $_POST['CustomerEMail'];
        $result['PayPal Payer Status'] = $_POST['PayerStatus'];
        $result['PayPal Payer ID'] = $_POST['PayerID'];
        $result['PayPal Payer Address'] = $_POST['AddressStatus'];
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

      $OSCOM_Db->save('orders_status_history', $sql_data_array);

      if (isset($_SESSION['sage_pay_direct_acsurl'])) {
        unset($_SESSION['sage_pay_direct_acsurl']);
        unset($_SESSION['sage_pay_direct_pareq']);
        unset($_SESSION['sage_pay_direct_md']);
      }

      $sage_pay_response = null;
    }

    function get_error() {
      $message = OSCOM::getDef('module_payment_sage_pay_direct_error_general');

      if ( isset($_GET['error']) && tep_not_null($_GET['error']) ) {
        if ( is_numeric($_GET['error']) && $this->errorMessageNumberExists($_GET['error']) ) {
          $message = $this->getErrorMessage($_GET['error']) . ' ' . OSCOM::getDef('module_payment_sage_pay_direct_error_general');
        } else {
          switch ($_GET['error']) {
            case 'cardtype':
              $message = OSCOM::getDef('module_payment_sage_pay_direct_error_cardtype');
              break;

            case 'cardowner':
              $message = OSCOM::getDef('module_payment_sage_pay_direct_error_cardowner');
              break;

            case 'cardnumber':
              $message = OSCOM::getDef('module_payment_sage_pay_direct_error_cardnumber');
              break;

            case 'cardstart':
              $message = OSCOM::getDef('module_payment_sage_pay_direct_error_cardstart');
              break;

            case 'cardexpires':
              $message = OSCOM::getDef('module_payment_sage_pay_direct_error_cardexpires');
              break;

            case 'cardissue':
              $message = OSCOM::getDef('module_payment_sage_pay_direct_error_cardissue');
              break;

            case 'cardcvc':
              $message = OSCOM::getDef('module_payment_sage_pay_direct_error_cardcvc');
              break;
          }
        }
      }

      $error = array('title' => OSCOM::getDef('module_payment_sage_pay_direct_error_title'),
                     'error' => $message);

      return $error;
    }

    function check() {
      return defined('MODULE_PAYMENT_SAGE_PAY_DIRECT_STATUS');
    }

    function install($parameter = null) {
      $OSCOM_Db = Registry::get('Db');

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

        $OSCOM_Db->save('configuration', $sql_data_array);
      }
    }

    function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
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
      $OSCOM_Db = Registry::get('Db');

      $Qcheck = $OSCOM_Db->query('show tables like "customers_sagepay_tokens"');

      if ($Qcheck->fetch() === false) {
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

        $OSCOM_Db->exec($sql);
      }

      if (!defined('MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_ORDER_STATUS_ID')) {
        $Qcheck = $OSCOM_Db->get('orders_status', 'orders_status_id', ['orders_status_name' => 'Sage Pay [Transactions]'], null, 1);

        if ($Qcheck->fetch() === false) {
          $Qstatus = $OSCOM_Db->get('orders_status', 'max(orders_status_id) as status_id');

          $status_id = $Qstatus->valueInt('status_id') + 1;

          $languages = tep_get_languages();

          foreach ($languages as $lang) {
            $OSCOM_Db->save('orders_status', [
              'orders_status_id' => $status_id,
              'language_id' => $lang['id'],
              'orders_status_name' => 'Sage Pay [Transactions]',
              'public_flag' => 0,
              'downloads_flag' => 0
            ]);
          }
        } else {
          $status_id = $Qcheck->valueInt('orders_status_id');
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

        if ( is_file(OSCOM::getConfig('dir_root', 'Shop') . 'ext/modules/payment/sage_pay/sagepay.com.crt') ) {
          curl_setopt($curl, CURLOPT_CAINFO, OSCOM::getConfig('dir_root', 'Shop') . 'ext/modules/payment/sage_pay/sagepay.com.crt');
        } elseif ( is_file(OSCOM::getConfig('dir_root', 'Shop') . 'includes/cacert.pem') ) {
          curl_setopt($curl, CURLOPT_CAINFO, OSCOM::getConfig('dir_root', 'Shop') . 'includes/cacert.pem');
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
      global $currencies;

      if (empty($currency_code) || !$currencies->is_set($currency_code)) {
        $currency_code = $_SESSION['currency'];
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
      $OSCOM_Db = Registry::get('Db');

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

      return $OSCOM_Db->delete('customers_sagepay_tokens', ['id' => $token_id, 'customers_id' => $_SESSION['customer_id'], 'sagepay_token' => $token]) === 1;
    }

    function loadErrorMessages() {
      $errors = array();

      if (is_file(dirname(__FILE__) . '/../../../ext/modules/payment/sage_pay/errors.php')) {
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
      $dialog_title = OSCOM::getDef('module_payment_sage_pay_direct_dialog_connection_title');
      $dialog_button_close = OSCOM::getDef('module_payment_sage_pay_direct_dialog_connection_button_close');
      $dialog_success = OSCOM::getDef('module_payment_sage_pay_direct_dialog_connection_success');
      $dialog_failed = OSCOM::getDef('module_payment_sage_pay_direct_dialog_connection_failed');
      $dialog_error = OSCOM::getDef('module_payment_sage_pay_direct_dialog_connection_error');
      $dialog_connection_time = OSCOM::getDef('module_payment_sage_pay_direct_dialog_connection_time');

      $test_url = OSCOM::link('modules.php', 'set=payment&module=' . $this->code . '&action=install&subaction=conntest');

      $js = <<<EOD
<script>
if ( typeof jQuery == 'undefined' ) {
  document.write('<scr' + 'ipt src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></scr' + 'ipt>');
  document.write('<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/redmond/jquery-ui.css" />');
  document.write('<scr' + 'ipt src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></scr' + 'ipt>');
}
</script>

<script>
(function() {
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

      $info = '<p><img src="images/icons/locked.gif" border="0">&nbsp;<a href="javascript:openTestConnectionDialog();" style="text-decoration: underline; font-weight: bold;">' . OSCOM::getDef('module_payment_sage_pay_direct_dialog_connection_link_title') . '</a></p>' .
              '<div id="testConnectionDialog" style="display: none;"><p>';

      if ( MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_SERVER == 'Live' ) {
        $info .= 'Live Server:<br />https://live.sagepay.com/gateway/service/vspdirect-register.vsp';
      } else {
        $info .= 'Test Server:<br />https://test.sagepay.com/gateway/service/vspdirect-register.vsp';
      }

      $info .= '</p><div id="testConnectionDialogProgress"><p>' . OSCOM::getDef('module_payment_sage_pay_direct_dialog_connection_general_text') . '</p><div id="tcdprogressbar"></div></div></div>' .
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

      $ip_address = HTTP::getIpAddress();

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
<script>
if ( typeof jQuery == 'undefined' ) {
  document.write('<scr' + 'ipt src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></scr' + 'ipt>');
}
</script>

<script>
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
      if (tep_not_null(MODULE_PAYMENT_SAGE_PAY_DIRECT_DEBUG_EMAIL)) {
        $email_body = '';

        if (!empty($response)) {
          $email_body .= 'RESPONSE:' . "\n\n" . print_r($response, true) . "\n\n";
        }

        if (!empty($_POST)) {
          if (isset($_POST['cc_number_nh-dns'])) {
            $_POST['cc_number_nh-dns'] = 'XXXX' . substr($_POST['cc_number_nh-dns'], -4);
          }

          if (isset($_POST['cc_cvc_tokens_nh-dns'])) {
            $_POST['cc_cvc_tokens_nh-dns'] = 'XXX';
          }

          if (isset($_POST['cc_cvc_nh-dns'])) {
            $_POST['cc_cvc_nh-dns'] = 'XXX';
          }

          if (isset($_POST['cc_issue_nh-dns'])) {
            $_POST['cc_issue_nh-dns'] = 'XXX';
          }

          if (isset($_POST['cc_expires_month'])) {
            $_POST['cc_expires_month'] = 'XX';
          }

          if (isset($_POST['cc_expires_year'])) {
            $_POST['cc_expires_year'] = 'XX';
          }

          if (isset($_POST['cc_starts_month'])) {
            $_POST['cc_starts_month'] = 'XX';
          }

          if (isset($_POST['cc_starts_year'])) {
            $_POST['cc_starts_year'] = 'XX';
          }

          $email_body .= '$_POST:' . "\n\n" . print_r($_POST, true) . "\n\n";
        }

        if (!empty($_GET)) {
          $email_body .= '$_GET:' . "\n\n" . print_r($_GET, true) . "\n\n";
        }

        if (!empty($email_body)) {
          $debugEmail = new Mail(MODULE_PAYMENT_SAGE_PAY_DIRECT_DEBUG_EMAIL, null, STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER, 'Sage Pay Direct Debug E-Mail');
          $debugEmail->setBody($email_body);
          $debugEmail->send();
        }
      }
    }
  }
?>
