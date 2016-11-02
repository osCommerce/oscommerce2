<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  if ( !class_exists('OSCOM_Braintree') ) {
    include(DIR_FS_CATALOG . 'includes/apps/braintree/OSCOM_Braintree.php');
  }

  class braintree_cc {
    var $code, $title, $description, $enabled, $_app, $payment_types;

    function braintree_cc() {
      global $PHP_SELF, $order, $appBraintreeCcRightTurn, $payment;

      $this->_app = new OSCOM_Braintree();
      $this->_app->loadLanguageFile('modules/CC/CC.php');

      $this->signature = 'braintree|braintree_cc|' . $this->_app->getVersion() . '|2.3';
      $this->api_version = '3';

      $this->code = 'braintree_cc';
      $this->title = $this->_app->getDef('module_cc_title');
      $this->public_title = $this->_app->getDef('module_cc_public_title');
      $this->description = '<div align="center">' . $this->_app->drawButton($this->_app->getDef('module_cc_legacy_admin_app_button'), tep_href_link('braintree.php', 'action=configure&module=CC'), 'primary', null, true) . '</div>';
      $this->sort_order = defined('OSCOM_APP_PAYPAL_BRAINTREE_CC_SORT_ORDER') ? OSCOM_APP_PAYPAL_BRAINTREE_CC_SORT_ORDER : 0;
      $this->enabled = defined('OSCOM_APP_PAYPAL_BRAINTREE_CC_STATUS') && in_array(OSCOM_APP_PAYPAL_BRAINTREE_CC_STATUS, array('1', '0')) ? true : false;
      $this->order_status = defined('OSCOM_APP_PAYPAL_BRAINTREE_CC_ORDER_STATUS_ID') && ((int)OSCOM_APP_PAYPAL_BRAINTREE_CC_ORDER_STATUS_ID > 0) ? (int)OSCOM_APP_PAYPAL_BRAINTREE_CC_ORDER_STATUS_ID : 0;

      if ( defined('OSCOM_APP_PAYPAL_BRAINTREE_CC_STATUS') ) {
        if ( OSCOM_APP_PAYPAL_BRAINTREE_CC_STATUS == '0' ) {
          $this->title .= ' [Sandbox]';
          $this->public_title .= ' (' . $this->code . '; Sandbox)';
        }
      }

      $braintree_error = null;

      if ( version_compare(PHP_VERSION, '5.4.0', '<') ) {
        $braintree_error = true;
      }

      if ( !isset($braintree_error) ) {
        $requiredExtensions = array('xmlwriter', 'openssl', 'dom', 'hash', 'curl');

        $exts = array();

        foreach ( $requiredExtensions as $ext ) {
          if ( !extension_loaded($ext) ) {
            $exts[] = $ext;
          }
        }

        if ( !empty($exts) ) {
          $braintree_error = true;
        }
      }

      if ( !isset($braintree_error) ) {
        $this->api_version .= ' [SDK v' . Braintree_Version::get() . ']';
      } else {
        $this->enabled = false;
      }

      if ( defined('OSCOM_APP_PAYPAL_BRAINTREE_CC_PAYMENT_TYPES') && tep_not_null(OSCOM_APP_PAYPAL_BRAINTREE_CC_PAYMENT_TYPES) ) {
        $this->payment_types = explode(';', OSCOM_APP_PAYPAL_BRAINTREE_CC_PAYMENT_TYPES);
      }

      if ( $this->enabled === true ) {
        if ( isset($order) && is_object($order) ) {
          $this->update_status();
        }
      }

// When changing the shipping address due to no shipping rates being available, head straight to the checkout confirmation page
      if ((basename($PHP_SELF) == 'checkout_payment.php') && isset($appBraintreeCcRightTurn)) {
        tep_session_unregister('appBraintreeCcRightTurn');

        if (isset($payment) && ($payment == 'braintree_cc')) {
          tep_redirect(tep_href_link('checkout_confirmation.php', '', 'SSL'));
        }
      }
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

    function checkout_initialization_method() {
      global $cart, $appBraintreeCcFormHash;

      $content = '';

      if ($this->isPaymentTypeAccepted('paypal')) {
        $this->_app->setupCredentials();

        $transaction_currency = $this->getTransactionCurrency();

        $clientToken = Braintree_ClientToken::generate(array(
          'merchantAccountId' => $this->getMerchantAccountId($transaction_currency)
        ));

        $amount = $this->_app->formatCurrencyRaw($cart->show_total(), $transaction_currency);

        $formUrl = tep_href_link('ext/modules/payment/braintree_cc/rpc.php', 'action=paypal', 'SSL');
        $formHash = $appBraintreeCcFormHash = $this->_app->createRandomValue(16);
        tep_session_register('appBraintreeCcFormHash');

        $intent = (OSCOM_APP_PAYPAL_BRAINTREE_CC_TRANSACTION_METHOD == '1') ? 'sale' : 'authorize';

        $enableShippingAddress = in_array($cart->get_content_type(), array('physical', 'mixed')) ? 'true' : 'false';

        switch (OSCOM_APP_PAYPAL_BRAINTREE_CC_PAYPAL_BUTTON_COLOR) {
          case '2':
            $button_color = 'blue';
            break;

          case '3':
            $button_color = 'silver';
            break;

          case '1':
          default:
            $button_color = 'gold';
        }

        switch (OSCOM_APP_PAYPAL_BRAINTREE_CC_PAYPAL_BUTTON_SIZE) {
          case '2':
            $button_size = 'small';
            break;

          case '3':
            $button_size = 'medium';
            break;

          case '1':
          default:
            $button_size = 'tiny';
        }

        switch (OSCOM_APP_PAYPAL_BRAINTREE_CC_PAYPAL_BUTTON_SHAPE) {
          case '2':
            $button_shape = 'rect';
            break;

          case '1':
          default:
            $button_shape = 'pill';
        }

        $content = <<<EOD
<script>
if ( typeof jQuery == 'undefined' ) {
  document.write('<scr' + 'ipt src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></scr' + 'ipt>');
}
</script>
<script src="https://www.paypalobjects.com/api/button.js?"
  data-merchant="braintree"
  data-id="bt-paypal-button"
  data-button="checkout"
  data-color="{$button_color}"
  data-size="{$button_size}"
  data-shape="{$button_shape}"
  data-button_type="submit"
  data-button_disabled="false"
></script>
<script>
$(function() {
  braintree.client.create({
    authorization: '{$clientToken}'
  }, function (clientErr, clientInstance) {
    if (clientErr) {
      $('#bt-paypal-button').hide();

      return;
    }

    braintree.paypal.create({
      client: clientInstance
    }, function (paypalErr, paypalInstance) {
      if (paypalErr) {
        $('#bt-paypal-button').hide();

        return;
      }

      $('#bt-paypal-button').prop('disabled', false);

      $('#bt-paypal-button').on('click', function (event) {
        event.preventDefault();

        paypalInstance.tokenize({
          flow: 'checkout',
          amount: {$amount},
          currency: '{$transaction_currency}',
          enableShippingAddress: {$enableShippingAddress},
          enableBillingAddress: true,
          intent: '{$intent}'
        }, function (tokenizeErr, payload) {
          if (tokenizeErr) {
            return;
          }

          $('#bt-paypal-button').prop('disabled', true);

          $('<form>').attr({
            name: 'bt_checkout_paypal',
            action: '{$formUrl}',
            method: 'post'
          }).insertAfter('form[name="cart_quantity"]');

          $('<input>').attr({
            type: 'hidden',
            name: 'bt_paypal_form_hash',
            value: '{$formHash}'
          }).appendTo('form[name="bt_checkout_paypal"]');

          $('<input>').attr({
            type: 'hidden',
            name: 'bt_paypal_nonce',
            value: payload.nonce
          }).appendTo('form[name="bt_checkout_paypal"]');

          $('form[name="bt_checkout_paypal"]').submit();
        });
      });
    });
  });
});
</script>
EOD;

          $ext_scripts = '<script src="https://js.braintreegateway.com/web/3.2.0/js/client.min.js"></script><script src="https://js.braintreegateway.com/web/3.2.0/js/paypal.min.js"></script>';

          if ($this->templateClassExists()) {
            $GLOBALS['oscTemplate']->addBlock($ext_scripts, 'footer_scripts');
          } else {
            $content .= $ext_scripts;
          }
        }

        return $content;
    }

    function javascript_validation() {
      return false;
    }

    function selection() {
      if (tep_session_is_registered('appBraintreeCcNonce')) {
        tep_session_unregister('appBraintreeCcNonce');
      }

      return array('id' => $this->code,
                   'module' => $this->public_title);
    }

    function pre_confirmation_check() {
      global $order, $request_type;

      if (!tep_session_is_registered('appBraintreeCcNonce') && (OSCOM_APP_PAYPAL_BRAINTREE_CC_ENTRY_FORM == '3')) {
        if (($request_type == 'NONSSL') && ((OSCOM_APP_PAYPAL_BRAINTREE_CC_THREE_D_SECURE === '1') || (OSCOM_APP_PAYPAL_BRAINTREE_CC_THREE_D_SECURE === '2'))) {
          if (ENABLE_SSL == true) {
// prevent redirect loop for incorrectly configured servers
            if (!tep_session_is_registered('bt_3ds_ssl_check')) {
              $bt_3ds_ssl_check = true;
              tep_session_register('bt_3ds_ssl_check');

              tep_redirect(tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'));
            }
          }
        }

        if (tep_session_is_registered('bt_3ds_ssl_check')) {
          tep_session_unregister('bt_3ds_ssl_check');
        }

        if ($this->templateClassExists()) {
          $GLOBALS['oscTemplate']->addBlock($this->getSubmitCardDetailsJavascript(), 'footer_scripts');
        }
      }

      if ($this->isPaymentTypeAccepted('paypal') && tep_session_is_registered('appBraintreeCcNonce')) {
        $order->info['payment_method'] = '<img src="https://www.paypalobjects.com/webstatic/mktg/Logo/pp-logo-100px.png" border="0" alt="PayPal Logo" style="padding: 3px;" />';
      }
    }

    function confirmation() {
      global $customer_id, $order, $currencies, $currency;

      if (tep_session_is_registered('appBraintreeCcNonce')) {
        return false;
      }

      if (OSCOM_APP_PAYPAL_BRAINTREE_CC_ENTRY_FORM == '3') {
        $content = '<div id="btCardStatus" class="ui-state-error ui-corner-all" style="display: none; padding: 10px; margin-bottom: 10px;"></div>';

        if (!$this->templateClassExists()) {
          $content .= $this->getSubmitCardDetailsJavascript();
        }

        if (!$this->isValidCurrency($currency)) {
          $content .= '<div class="ui-state-highlight ui-corner-all" style="padding: 10px; margin-bottom: 10px;">' .
                      $this->_app->getDef('module_cc_notice_currency_charge', array(
                        'currency_total' => $currencies->format($order->info['total'], true, DEFAULT_CURRENCY),
                        'currency' => DEFAULT_CURRENCY,
                        'current_currency' => $currency
                      )) .
                      '</div>';
        }

        $default_token = null;

        if ((OSCOM_APP_PAYPAL_BRAINTREE_CC_CC_TOKENS == '1') || (OSCOM_APP_PAYPAL_BRAINTREE_CC_CC_TOKENS == '2')) {
          $tokens_query = tep_db_query('select id, card_type, number_filtered, expiry_date from customers_braintree_tokens where customers_id = "' . (int)$customer_id . '" order by date_added');
          if (tep_db_num_rows($tokens_query)) {
            $t = array();

            while ($tokens = tep_db_fetch_array($tokens_query)) {
              $default_token = (int)$tokens['id'];

              $t[] = array(
                'id' => (int)$tokens['id'],
                'text' => $this->_app->getDef('module_cc_stored_card_selection_title', array(
                  'card_type' => $tokens['card_type'],
                  'card_number' => $tokens['number_filtered'],
                  'card_expiry_date_month' => substr($tokens['expiry_date'], 0, 2),
                  'card_expiry_date_year' => substr($tokens['expiry_date'], 2)
                ))
              );
            }

            $t[] = array(
              'id' => '0',
              'text' => $this->_app->getDef('module_cc_new_card')
            );

            $content .= '<div style="margin-bottom: 10px;">
                           <label class="hosted-fields--label" for="braintree_cards">' . $this->_app->getDef('module_cc_payment_cards_title') . '</label>' .
                           tep_draw_pull_down_menu('braintree_cards', $t, $default_token, 'id="braintree_cards" class="hosted-field"') . '
                         </div>';

            if (OSCOM_APP_PAYPAL_BRAINTREE_CC_VERIFY_CVV == '1') {
              $content .= '<div id="braintree_stored_card_cvv">
                             <label class="hosted-fields--label" for="card-token-cvv">' . $this->_app->getDef('module_cc_card_cvv') . ' <span class="ui-icon ui-icon-info" style="float: right;" title="' . addslashes($this->_app->getDef('module_cc_card_cvv_info')) . '" id="btCvvTokenInfoIcon"></span></label>
                             <div id="card-token-cvv" class="hosted-field"></div>
                           </div>';
            }

            $content .= '</div>';
          }
        }

        $content .= '<div id="braintree_new_card">
                       <label class="hosted-fields--label" for="card-number">' . $this->_app->getDef('module_cc_card_number') . '</label>
                       <div id="card-number" class="hosted-field"></div>

                       <label class="hosted-fields--label" for="card-exp">' . $this->_app->getDef('module_cc_card_expiration_date') . '</label>
                       <div id="card-exp" class="hosted-field"></div>';

        if ((OSCOM_APP_PAYPAL_BRAINTREE_CC_VERIFY_CVV == '1') || (OSCOM_APP_PAYPAL_BRAINTREE_CC_VERIFY_CVV == '2')) {
          $content .= '<label class="hosted-fields--label" for="card-cvv">' . $this->_app->getDef('module_cc_card_cvv') . ' <span class="ui-icon ui-icon-info" style="float: right;" title="' . addslashes($this->_app->getDef('module_cc_card_cvv_info')) . '" id="btCvvInfoIcon"></span></label>
                       <div id="card-cvv" class="hosted-field"></div>';
        }

        if (OSCOM_APP_PAYPAL_BRAINTREE_CC_CC_TOKENS == '1') {
          $content .= '<div>
                         <label>' . tep_draw_checkbox_field('cc_save', 'true', true) . ' ' . $this->_app->getDef('module_cc_save_new_card') . '</label>
                       </div>';
        }

        $content .= '</div>';

        $content .= <<<EOD
<input type="hidden" name="payment_method_nonce">

<script>
if ($('#braintree_cards').length > 0) {
  $('#braintree_new_card').hide();
}
</script>
EOD;

        if ((OSCOM_APP_PAYPAL_BRAINTREE_CC_VERIFY_CVV == '1') || (OSCOM_APP_PAYPAL_BRAINTREE_CC_VERIFY_CVV == '2')) {
          $content .= <<<EOD
<script>
$(function() {
  $('#btCvvTokenInfoIcon, #btCvvInfoIcon').tooltip();
});
</script>
EOD;
        }
      } else {
        $this->_app->setupCredentials();

        $transaction_currency = $this->getTransactionCurrency();

        $clientToken = Braintree_ClientToken::generate(array(
          'merchantAccountId' => $this->getMerchantAccountId($transaction_currency)
        ));

        $amount = $this->_app->formatCurrencyRaw($order->info['total'], $transaction_currency);

        $content = <<<EOD
<script>
if ( typeof jQuery == 'undefined' ) {
  document.write('<scr' + 'ipt src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></scr' + 'ipt>');
}
</script>

<script>
$(function() {
  braintree.setup('{$clientToken}', 'dropin', {
    container: 'checkout_bt',
    paypal: {
      singleUse: true,
      amount: {$amount},
      currency: '{$transaction_currency}'
    }
  });
});
</script>

<div id="checkout_bt"></div>
EOD;

        $ext_script = '<script src="https://js.braintreegateway.com/v2/braintree.js"></script>';

        if ($this->templateClassExists()) {
          $GLOBALS['oscTemplate']->addBlock($ext_script, 'footer_scripts');
        } else {
          $content .= $ext_script;
        }
      }

      if (isset($content)) {
        $confirmation = array(
          'title' => $content
        );

        return $confirmation;
      }

      return false;
    }

    function process_button() {
      return false;
    }

    function before_process() {
      global $HTTP_POST_VARS, $customer_id, $order, $braintree_result, $braintree_token, $messageStack, $appBraintreeCcNonce;

      $braintree_token = null;
      $braintree_error = null;

      if (!tep_session_is_registered('appBraintreeCcNonce') && ((OSCOM_APP_PAYPAL_BRAINTREE_CC_CC_TOKENS == '1') || (OSCOM_APP_PAYPAL_BRAINTREE_CC_CC_TOKENS == '2'))) {
        if (isset($HTTP_POST_VARS['braintree_cards']) && is_numeric($HTTP_POST_VARS['braintree_cards']) && ($HTTP_POST_VARS['braintree_cards'] > 0)) {
          $token_query = tep_db_query('select braintree_token from customers_braintree_tokens where id = "' . (int)$HTTP_POST_VARS['braintree_cards'] . '" and customers_id = "' . (int)$customer_id . '"');
          if (tep_db_num_rows($token_query)) {
            $token = tep_db_fetch_array($token_query);

            $braintree_token = $token['braintree_token'];
          }
        }
      }

      $braintree_result = null;

      $this->_app->setupCredentials();

      $transaction_currency = $this->getTransactionCurrency();

      if (tep_session_is_registered('appBraintreeCcNonce')) {
        $data = array(
          'amount' => $this->_app->formatCurrencyRaw($order->info['total'], $transaction_currency),
          'paymentMethodNonce' => $appBraintreeCcNonce,
          'merchantAccountId' => $this->getMerchantAccountId($transaction_currency)
        );
      } else {
        $data = array(
          'paymentMethodNonce' => $HTTP_POST_VARS['payment_method_nonce'],
          'amount' => $this->_app->formatCurrencyRaw($order->info['total'], $transaction_currency),
          'merchantAccountId' => $this->getMerchantAccountId($transaction_currency),
          'customer' => array(
            'firstName' => $order->customer['firstname'],
            'lastName' => $order->customer['lastname'],
            'company' => $order->customer['company'],
            'phone' => $order->customer['telephone'],
            'email' => $order->customer['email_address']
          ),
          'billing' => array(
            'firstName' => $order->billing['firstname'],
            'lastName' => $order->billing['lastname'],
            'company' => $order->billing['company'],
            'streetAddress' => $order->billing['street_address'],
            'extendedAddress' => $order->billing['suburb'],
            'locality' => $order->billing['city'],
            'region' => tep_get_zone_code($order->billing['country']['id'], $order->billing['zone_id'], $order->billing['state']),
            'postalCode' => $order->billing['postcode'],
            'countryCodeAlpha2' => $order->billing['country']['iso_code_2']
          ),
          'options' => array()
        );

        if (OSCOM_APP_PAYPAL_BRAINTREE_CC_TRANSACTION_METHOD == '1') {
          $data['options']['submitForSettlement'] = true;
        }

        if (!isset($braintree_token)) {
          if (((OSCOM_APP_PAYPAL_BRAINTREE_CC_CC_TOKENS == '1') && isset($HTTP_POST_VARS['cc_save']) && ($HTTP_POST_VARS['cc_save'] == 'true')) || (OSCOM_APP_PAYPAL_BRAINTREE_CC_CC_TOKENS === '2')) {
            $data['options']['storeInVaultOnSuccess'] = true;
          }
        }
      }

      if ($order->content_type != 'virtual') {
        $data['shipping'] = array(
          'firstName' => $order->delivery['firstname'],
          'lastName' => $order->delivery['lastname'],
          'company' => $order->delivery['company'],
          'streetAddress' => $order->delivery['street_address'],
          'extendedAddress' => $order->delivery['suburb'],
          'locality' => $order->delivery['city'],
          'region' => tep_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']),
          'postalCode' => $order->delivery['postcode'],
          'countryCodeAlpha2' => $order->delivery['country']['iso_code_2']
        );
      }

      $data['channel'] = $this->_app->getIdentifier();

      $error = false;

      try {
        $braintree_result = Braintree_Transaction::sale($data);
      } catch (Exception $e) {
        $error = true;
      }

      if (($error === false) && ($braintree_result->success === true)) {
        return true;
      }

      $message = $this->_app->getDef('module_cc_error_general');

      if (isset($braintree_result->transaction)) {
        if (isset($braintree_result->transaction->gatewayRejectionReason)) {
          switch ($braintree_result->transaction->gatewayRejectionReason) {
            case 'cvv':
              $message = $this->_app->getDef('module_cc_error_cvv');
              break;

            case 'avs':
              $message = $this->_app->getDef('module_cc_error_avs');
              break;

            case 'avs_and_cvv':
              $message = $this->_app->getDef('module_cc_error_avs_and_cvv');
              break;
          }
        }
      }

      $messageStack->add_session('checkout_confirmation', $message);

      tep_redirect(tep_href_link('checkout_confirmation.php', null, 'SSL'));
    }

    function after_process() {
      global $HTTP_POST_VARS, $customer_id, $insert_id, $braintree_result, $braintree_token;

      $status_comment = array(
        'Transaction ID: ' . tep_db_prepare_input($braintree_result->transaction->id)
      );

      if (($braintree_result->transaction->paymentInstrumentType == 'credit_card') && ((OSCOM_APP_PAYPAL_BRAINTREE_CC_THREE_D_SECURE === '1') || ((OSCOM_APP_PAYPAL_BRAINTREE_CC_THREE_D_SECURE === '2') && !isset($braintree_token)))) {
        if (isset($braintree_result->transaction->threeDSecureInfo) && is_object($braintree_result->transaction->threeDSecureInfo)) {
          $status_comment[] = '3D Secure: ' . tep_db_prepare_input($braintree_result->transaction->threeDSecureInfo->status . ' (Liability Shifted: ' . ($braintree_result->transaction->threeDSecureInfo->liabilityShifted === true ? 'true' : 'false') . ')');
        } else {
          $status_comment[] = '3D Secure: ** MISSING **';
        }
      }

      $status_comment[] = 'Payment Status: ' . tep_db_prepare_input($braintree_result->transaction->status);
      $status_comment[] = 'Payment Type: ' . tep_db_prepare_input($braintree_result->transaction->paymentInstrumentType);

      if (Braintree_Configuration::environment() !== 'production') {
        $status_comment[] = 'Server: ' . tep_db_prepare_input(Braintree_Configuration::environment());
      }

      if (!tep_session_is_registered('appBraintreeCcNonce') && (((OSCOM_APP_PAYPAL_BRAINTREE_CC_CC_TOKENS == '1') && isset($HTTP_POST_VARS['cc_save']) && ($HTTP_POST_VARS['cc_save'] == 'true')) || (OSCOM_APP_PAYPAL_BRAINTREE_CC_CC_TOKENS === '2')) && !isset($braintree_token) && isset($braintree_result->transaction->creditCard['token'])) {
        $token = $braintree_result->transaction->creditCard['token'];
        $type = $braintree_result->transaction->creditCard['cardType'];
        $number = $braintree_result->transaction->creditCard['last4'];
        $expiry = $braintree_result->transaction->creditCard['expirationMonth'] . $braintree_result->transaction->creditCard['expirationYear'];

        $check_query = tep_db_query('select id from customers_braintree_tokens where customers_id = "' . (int)$customer_id . '" and braintree_token = "' . tep_db_input(tep_db_prepare_input($token)) . '"');
        if (!tep_db_num_rows($check_query)) {
          $sql_data_array = array(
            'customers_id' => (int)$customer_id,
            'braintree_token' => $token,
            'card_type' => $type,
            'number_filtered' => $number,
            'expiry_date' => $expiry,
            'date_added' => 'now()'
          );

          tep_db_perform('customers_braintree_tokens', $sql_data_array);
        }

        $status_comment[] = 'Token Created: Yes';
      } elseif (isset($braintree_token)) {
        $status_comment[] = 'Token Used: Yes';
      }

      $sql_data_array = array(
        'orders_id' => $insert_id,
        'orders_status_id' => OSCOM_APP_PAYPAL_BRAINTREE_TRANSACTIONS_ORDER_STATUS_ID,
        'date_added' => 'now()',
        'customer_notified' => '0',
        'comments' => implode("\n", $status_comment)
      );

      tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

      if (tep_session_is_registered('appBraintreeCcNonce')) {
        tep_session_unregister('appBraintreeCcNonce');
      }

      if (tep_session_is_registered('appBraintreeCcFormHash')) {
        tep_session_unregister('appBraintreeCcFormHash');
      }
    }

    function get_error() {
      global $HTTP_GET_VARS;

      $error_message = $this->_app->getDef('module_cc_error_general');

      switch ($HTTP_GET_VARS['error']) {
        case 'not_available':
          $error_message = $this->_app->getDef('module_cc_error_unavailable');
          break;
      }

      $error = array('title' => $this->_app->getDef('module_cc_error_title'),
                     'error' => $error_message);

      return $error;
    }

    function check() {
      $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'OSCOM_APP_PAYPAL_BRAINTREE_CC_STATUS'");
      if ( tep_db_num_rows($check_query) ) {
        $check = tep_db_fetch_array($check_query);

        return tep_not_null($check['configuration_value']);
      }

      return false;
    }

    function install() {
      tep_redirect(tep_href_link('braintree.php', 'action=configure'));
    }

    function remove() {
      tep_redirect(tep_href_link('braintree.php', 'action=configure'));
    }

    function keys() {
      return array('OSCOM_APP_PAYPAL_BRAINTREE_CC_SORT_ORDER');
    }

    function getTransactionCurrency() {
      global $currency;

      return $this->isValidCurrency($currency) ? $currency : DEFAULT_CURRENCY;
    }

    function getMerchantAccountId($currency) {
      $currencies_ma = (OSCOM_APP_PAYPAL_BRAINTREE_CC_STATUS === '1') ? OSCOM_APP_PAYPAL_BRAINTREE_CURRENCIES_MA : OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_CURRENCIES_MA;

      foreach (explode(';', $currencies_ma) as $ma) {
        list($a, $c) = explode(':', $ma);

        if ($c == $currency) {
          return $a;
        }
      }

      return '';
    }

    function isValidCurrency($currency) {
      global $currencies;

      $currencies_ma = (OSCOM_APP_PAYPAL_BRAINTREE_CC_STATUS === '1') ? OSCOM_APP_PAYPAL_BRAINTREE_CURRENCIES_MA : OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_CURRENCIES_MA;

      foreach (explode(';', $currencies_ma) as $combo) {
        list($id, $c) = explode(':', $combo);

        if ($c == $currency) {
          return $currencies->is_set($c);
        }
      }

      return false;
    }

    function templateClassExists() {
      return class_exists('oscTemplate') && isset($GLOBALS['oscTemplate']) && is_object($GLOBALS['oscTemplate']) && (get_class($GLOBALS['oscTemplate']) == 'oscTemplate');
    }

    function deleteCard($token, $token_id) {
      global $customer_id;

      $result = false;

      try {
        $this->_app->setupCredentials();

        Braintree_CreditCard::delete($token);

        tep_db_query('delete from customers_braintree_tokens where id = "' . (int)$token_id . '" and customers_id = "' . (int)$customer_id . '" and braintree_token = "' . tep_db_input($token) . '"');

        $result = true;
      } catch (Exception $e) {
      }

      return $result === true;
    }

    function getSubmitCardDetailsJavascript() {
      global $order, $request_type;

      $this->_app->setupCredentials();

      $transaction_currency = $this->getTransactionCurrency();

      $clientToken = Braintree_ClientToken::generate(array(
        'merchantAccountId' => $this->getMerchantAccountId($transaction_currency)
      ));

      $order_total = $this->_app->formatCurrencyRaw($order->info['total'], $transaction_currency);

      $getCardTokenRpcUrl = tep_href_link('ext/modules/payment/braintree_cc/rpc.php', 'action=getCardToken', 'SSL');

      if ((OSCOM_APP_PAYPAL_BRAINTREE_CC_THREE_D_SECURE === '1') && ($request_type == 'SSL')) {
        $has3ds = 'all';
      } elseif ((OSCOM_APP_PAYPAL_BRAINTREE_CC_THREE_D_SECURE === '2') && ($request_type == 'SSL')) {
        $has3ds = 'new';
      } else {
        $has3ds = 'none';
      }

      $url_not_available = addslashes(str_replace('&amp;', '&', tep_href_link('checkout_payment.php', 'payment_error=braintree_cc&error=not_available', 'SSL')));

      $error_unavailable = addslashes($this->_app->getDef('module_cc_error_unavailable'));
      $error_all_fields_required = addslashes($this->_app->getDef('module_cc_error_all_fields_required'));
      $error_fields_required = addslashes($this->_app->getDef('module_cc_error_fields_required'));
      $error_tmp_processing_problem = addslashes($this->_app->getDef('module_cc_error_tmp_processing_problem'));

      $js = <<<EOD
<style>
.hosted-field {
  height: 40px;
  box-sizing: border-box;
  width: 100%;
  padding: 6px;
  display: inline-block;
  box-shadow: none;
  font-weight: 600;
  border-radius: 6px;
  border: 1px solid #dddddd;
  background: #fcfcfc;
  margin-bottom: 12px;
  background: linear-gradient(to right, white 50%, #fcfcfc 50%);
  background-size: 200% 100%;
  background-position: right bottom;
  transition: all 300ms ease-in-out;
}

.hosted-fields--label {
  display: block;
  margin-bottom: 6px;
}
</style>

<script>
if ( typeof jQuery == 'undefined' ) {
  document.write('<scr' + 'ipt src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></scr' + 'ipt>');
}
</script>

<script>
$('form[name="checkout_confirmation"]').attr('id', 'braintree-payment-form');
$('#braintree-payment-form button[type="submit"]').attr('id', 'braintree-payment-form-submit-button').button('disable');

$(function() {
  if (typeof $('#braintree-payment-form-submit-button').data('orig-button-text') === 'undefined') {
    $('#braintree-payment-form-submit-button').data('orig-button-text', $('#braintree-payment-form-submit-button').html());
  }

  var has3ds = '{$has3ds}';
  var do3ds = false;

  function doTokenize(hostedFieldsInstance, clientInstance, nonce) {
    if ((hostedFieldsInstance === undefined) && (nonce !== undefined)) {
      if (do3ds === true) {
        create3DS(clientInstance, nonce);
      } else {
        $('#braintree-payment-form input[name="payment_method_nonce"]').val(nonce);

        $('#braintree-payment-form').submit();
      }

      return;
    }

    hostedFieldsInstance.tokenize(function (tokenizeErr, payload) {
      if (tokenizeErr) {
        switch (tokenizeErr.code) {
          case 'HOSTED_FIELDS_FIELDS_EMPTY':
            $('#btCardStatus').html('{$error_all_fields_required}');

            if ($('#btCardStatus').is(':hidden')) {
              $('#btCardStatus').show();
            }

            if (($('#braintree_cards').length > 0) && ($('#braintree_cards').val() !== '0')) {
              $('#braintree-payment-form label[for=card-token-cvv]').addClass('ui-state-error-text');
            } else {
              $('#braintree-payment-form label[for=card-number]').addClass('ui-state-error-text');
              $('#braintree-payment-form label[for=card-exp]').addClass('ui-state-error-text');

              if ($('#card-cvv').length === 1) {
                $('#braintree-payment-form label[for=card-cvv]').addClass('ui-state-error-text');
              }
            }

            break;

          case 'HOSTED_FIELDS_FIELDS_INVALID':
            $('#btCardStatus').html('{$error_fields_required}');

            if ($('#btCardStatus').is(':hidden')) {
              $('#btCardStatus').show();
            }

            if (($('#braintree_cards').length > 0) && ($('#braintree_cards').val() !== '0')) {
              if ($.inArray('cvv', tokenizeErr.details.invalidFieldKeys) !== -1) {
                $('#braintree-payment-form label[for=card-token-cvv]').addClass('ui-state-error-text');
              }
            } else {
              if ($.inArray('number', tokenizeErr.details.invalidFieldKeys) !== -1) {
                $('#braintree-payment-form label[for=card-number]').addClass('ui-state-error-text');
              }

              if ($.inArray('expirationDate', tokenizeErr.details.invalidFieldKeys) !== -1) {
                $('#braintree-payment-form label[for=card-exp]').addClass('ui-state-error-text');
              }

              if ($.inArray('cvv', tokenizeErr.details.invalidFieldKeys) !== -1) {
                if ($('#card-cvv').length === 1) {
                  $('#braintree-payment-form label[for=card-cvv]').addClass('ui-state-error-text');
                }
              }
            }

            break;

          default:
            $('#btCardStatus').html('{$error_tmp_processing_problem}');

            if ($('#btCardStatus').is(':hidden')) {
              $('#btCardStatus').show();
            }
        }

        $('#braintree-payment-form-submit-button').html($('#braintree-payment-form-submit-button').data('orig-button-text')).button('enable');

        return;
      }

      if (nonce === undefined) {
        nonce = payload.nonce;
      }

      if (do3ds === true) {
        create3DS(clientInstance, nonce);
      } else {
        $('#braintree-payment-form input[name="payment_method_nonce"]').val(nonce);

        $('#braintree-payment-form').submit();
      }
    });
  }

  function create3DS(clientInstance, nonce) {
    try {
      braintree.threeDSecure.create({
        client: clientInstance
      }, function (threeDSecureErr, threeDSecureInstance) {
        if (threeDSecureErr) {
          $('#btCardStatus').html('{$error_tmp_processing_problem}');

          if ($('#btCardStatus').is(':hidden')) {
            $('#btCardStatus').show();
          }

          $('#braintree-payment-form-submit-button').html($('#braintree-payment-form-submit-button').data('orig-button-text')).button('enable');

          return;
        }

        threeDSecureInstance.verifyCard({
          amount: {$order_total},
          nonce: nonce,
          addFrame: function (err, iframe) {
            $.colorbox({
              transition: 'none',
              closeButton: false,
              overlayClose: false,
              escKey: false,
              arrowKey: false,
              html: $('<div>').html(iframe).html()
            });
          },
          removeFrame: function () {
            $.colorbox.close();
          }
        }, function (error, response) {
          if (error) {
            $('#btCardStatus').html('{$error_tmp_processing_problem}');

            if ($('#btCardStatus').is(':hidden')) {
              $('#btCardStatus').show();
            }

            $('#braintree-payment-form-submit-button').html($('#braintree-payment-form-submit-button').data('orig-button-text')).button('enable');

            return;
          }

          $('#braintree-payment-form input[name="payment_method_nonce"]').val(response.nonce);

          $('#braintree-payment-form').submit();
        });
      });
    } catch (err) {
      $('#btCardStatus').html('{$error_tmp_processing_problem}');

      if ($('#btCardStatus').is(':hidden')) {
        $('#btCardStatus').show();
      }

      $('#braintree-payment-form-submit-button').html($('#braintree-payment-form-submit-button').data('orig-button-text')).button('enable');
    }
  }

  var btClientInstance;
  var btHostedFieldsInstance;

  if ($('#braintree_cards').length > 0) {
    $('#braintree_cards').change(function() {
      $('#braintree-payment-form-submit-button').button('disable');

      var selected = $(this).val();

      if (selected == '0') {
        braintreeShowNewCardFields();
      } else {
        braintreeShowStoredCardFields(selected);
      }
    });
  }

  braintree.client.create({
    authorization: '{$clientToken}'
  }, function (clientErr, clientInstance) {
    if (clientErr) {
      $('#btCardStatus').html('{$error_unavailable}');

      if ($('#btCardStatus').is(':hidden')) {
        $('#btCardStatus').show();
      }

      $('#braintree-payment-form-submit-button').hide();

      window.location = '{$url_not_available}';

      return;
    }

    btClientInstance = clientInstance;

    if (($('#braintree_cards').length > 0) && ($('#braintree_cards').val() !== '0')) {
      braintreeShowStoredCardFields($('#braintree_cards').val());
    } else {
      braintreeShowNewCardFields();
    }
  });

  $('#braintree-payment-form').on('submit', function (event) {
    if ($('#braintree-payment-form input[name="payment_method_nonce"]').val().length > 0) {
      return;
    }

    event.preventDefault();

    var doTokenizeCall = true;

    if ($('#braintree_cards').length > 0) {
      if (($('#card-token-cvv').length === 1) && $('#braintree-payment-form label[for=card-token-cvv]').hasClass('ui-state-error-text')) {
        $('#braintree-payment-form label[for=card-token-cvv]').removeClass('ui-state-error-text');
      }
    }

    if ($('#braintree-payment-form label[for=card-number]').hasClass('ui-state-error-text')) {
      $('#braintree-payment-form label[for=card-number]').removeClass('ui-state-error-text');
    }

    if ($('#braintree-payment-form label[for=card-exp]').hasClass('ui-state-error-text')) {
      $('#braintree-payment-form label[for=card-exp]').removeClass('ui-state-error-text');
    }

    if (($('#card-cvv').length === 1) && $('#braintree-payment-form label[for=card-cvv]').hasClass('ui-state-error-text')) {
      $('#braintree-payment-form label[for=card-cvv]').removeClass('ui-state-error-text');
    }

    do3ds = false;

    if (($('#braintree_cards').length > 0) && ($('#braintree_cards').val() !== '0')) {
      if (has3ds === 'all') {
        do3ds = true;
      }
    } else {
      if ((has3ds === 'all') || (has3ds === 'new')) {
        do3ds = true;
      }
    }

    if ($('#braintree_cards').length > 0) {
      var cardsel = $('#braintree_cards').val();

      if (cardsel !== '0') {
        doTokenizeCall = false;

        $.post('{$getCardTokenRpcUrl}', {card_id: cardsel}, function(response) {
          if ((typeof response == 'object') && ('result' in response) && (response.result === 1)) {
            doTokenize(btHostedFieldsInstance, btClientInstance, response.token);
          }
        }, 'json');
      }
    }

    if (doTokenizeCall === true) {
      doTokenize(btHostedFieldsInstance, btClientInstance);
    }
  });

  function braintreeShowNewCardFields() {
    if ($('#braintree_stored_card_cvv').length === 1) {
      if ($('#braintree_stored_card_cvv').is(':visible')) {
        $('#braintree_stored_card_cvv').hide();
      }
    }

    if ($('#braintree-payment-form label[for=card-number]').hasClass('ui-state-error-text')) {
      $('#braintree-payment-form label[for=card-number]').removeClass('ui-state-error-text');
    }

    if ($('#braintree-payment-form label[for=card-exp]').hasClass('ui-state-error-text')) {
      $('#braintree-payment-form label[for=card-exp]').removeClass('ui-state-error-text');
    }

    if (($('#card-cvv').length === 1) && $('#braintree-payment-form label[for=card-cvv]').hasClass('ui-state-error-text')) {
      $('#braintree-payment-form label[for=card-cvv]').removeClass('ui-state-error-text');
    }

    if ($('#braintree_new_card').not(':visible')) {
      $('#braintree_new_card').show();
    }

    if (btHostedFieldsInstance !== undefined) {
      btHostedFieldsInstance.teardown(function (teardownErr) {
        if (teardownErr) {
          return;
        }

        braintreeCreateInstance();
      });

      return;
    }

    braintreeCreateInstance();
  }

  function braintreeShowStoredCardFields(id) {
    if ($('#braintree_stored_card_cvv').length === 1) {
      if ($('#braintree-payment-form label[for=card-token-cvv]').hasClass('ui-state-error-text')) {
        $('#braintree-payment-form label[for=card-token-cvv]').removeClass('ui-state-error-text');
      }

      if ($('#braintree_stored_card_cvv').not(':visible')) {
        $('#braintree_stored_card_cvv').show();
      }
    }

    if ($('#braintree_new_card').is(':visible')) {
      $('#braintree_new_card').hide();

      if (btHostedFieldsInstance !== undefined) {
        btHostedFieldsInstance.teardown(function (teardownErr) {
          if (teardownErr) {
            return;
          }

          braintreeCreateStoredCardInstance();
        });

        return;
      }

      braintreeCreateStoredCardInstance();

      return;
    }

    if (btHostedFieldsInstance === undefined) {
      braintreeCreateStoredCardInstance();
    } else {
      $('#braintree-payment-form-submit-button').button('enable');
    }
  }

  function braintreeCreateInstance() {
    var fields = {
      number: {
        selector: '#card-number'
      },
      expirationDate: {
        selector: '#card-exp',
        placeholder: 'MM / YYYY'
      }
    };

    if ($('#card-cvv').length === 1) {
      fields.cvv = {
        selector: '#card-cvv'
      };
    }

    braintree.hostedFields.create({
      client: btClientInstance,
      styles: {
        ':focus': {
          'color': 'black'
        },
        '.valid': {
          'color': '#8bdda8'
        }
      },
      fields: fields
    }, function (hostedFieldsErr, hostedFieldsInstance) {
      if (hostedFieldsErr) {
        $('#btCardStatus').html('{$error_unavailable}');

        if ($('#btCardStatus').is(':hidden')) {
          $('#btCardStatus').show();
        }

        $('#braintree-payment-form-submit-button').hide();

        window.location = '{$url_not_available}';

        return;
      }

      btHostedFieldsInstance = hostedFieldsInstance;

      $('#braintree-payment-form-submit-button').button('enable');
    });
  }

  function braintreeCreateStoredCardInstance() {
    if ($('#card-token-cvv').length === 1) {
      braintree.hostedFields.create({
        client: btClientInstance,
        styles: {
          ':focus': {
            'color': 'black'
          },
          '.valid': {
            'color': '#8bdda8'
          }
        },
        fields: {
          cvv: {
            selector: '#card-token-cvv'
          }
        }
      }, function (hostedFieldsErr, hostedFieldsInstance) {
        if (hostedFieldsErr) {
          $('#btCardStatus').html('{$error_unavailable}');

          if ($('#btCardStatus').is(':hidden')) {
            $('#btCardStatus').show();
          }

          $('#braintree-payment-form-submit-button').hide();

          window.location = '{$url_not_available}';

          return;
        }

        btHostedFieldsInstance = hostedFieldsInstance;

        $('#braintree-payment-form-submit-button').button('enable');
      });
    } else {
      btHostedFieldsInstance = undefined;

      $('#braintree-payment-form-submit-button').button('enable');
    }
  }
});
</script>
EOD;

      $js_scripts = '<script src="https://js.braintreegateway.com/web/3.2.0/js/client.min.js"></script>' .
                    '<script src="https://js.braintreegateway.com/web/3.2.0/js/hosted-fields.min.js"></script>';

      if ((OSCOM_APP_PAYPAL_BRAINTREE_CC_THREE_D_SECURE === '1') || (OSCOM_APP_PAYPAL_BRAINTREE_CC_THREE_D_SECURE === '2')) {
        $js_scripts .= '<script src="https://js.braintreegateway.com/web/3.2.0/js/three-d-secure.min.js"></script>';
      }

      if ($this->templateClassExists()) {
        $GLOBALS['oscTemplate']->addBlock($js_scripts, 'footer_scripts');
      } else {
        $js .= $js_scripts;
      }

      return $js;
    }

    function isPaymentTypeAccepted($type) {
      return in_array($type, $this->payment_types);
    }
  }
?>
