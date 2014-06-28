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

  class paypal_express {
    var $code, $title, $description, $enabled, $_app;

    function paypal_express() {
      global $PHP_SELF, $order, $payment, $request_type;

      $this->_app = new OSCOM_PayPal();

      $this->signature = 'paypal|paypal_express|' . $this->_app->getVersion() . '|2.3';
      $this->api_version = $this->_app->getApiVersion();

      $this->code = 'paypal_express';
      $this->title = MODULE_PAYMENT_PAYPAL_EXPRESS_TEXT_TITLE;
      $this->public_title = MODULE_PAYMENT_PAYPAL_EXPRESS_TEXT_PUBLIC_TITLE;
      $this->description = MODULE_PAYMENT_PAYPAL_EXPRESS_TEXT_DESCRIPTION;
      $this->sort_order = defined('OSCOM_APP_PAYPAL_EC_SORT_ORDER') ? OSCOM_APP_PAYPAL_EC_SORT_ORDER : 0;
      $this->enabled = defined('OSCOM_APP_PAYPAL_EC_STATUS') && in_array(OSCOM_APP_PAYPAL_EC_STATUS, array('1', '0')) ? true : false;
      $this->order_status = defined('OSCOM_APP_PAYPAL_EC_ORDER_STATUS_ID') && ((int)OSCOM_APP_PAYPAL_EC_ORDER_STATUS_ID > 0) ? (int)OSCOM_APP_PAYPAL_EC_ORDER_STATUS_ID : 0;

      if ( defined('OSCOM_APP_PAYPAL_EC_STATUS') ) {
        $this->description = '<div align="center">' . $this->_app->drawButton('Manage App', tep_href_link('paypal.php', 'action=configure&module=EC'), 'primary', null, true) . '</div><br />' . $this->description;

        if ( OSCOM_APP_PAYPAL_EC_STATUS == '0' ) {
          $this->title .= ' [Sandbox]';
          $this->public_title .= ' (' . $this->code . '; Sandbox)';
        }
      }

      if ( !function_exists('curl_init') ) {
        $this->description = '<div class="secWarning">' . MODULE_PAYMENT_PAYPAL_EXPRESS_ERROR_ADMIN_CURL . '</div>' . $this->description;

        $this->enabled = false;
      }

      if ( $this->enabled === true ) {
        if ( !$this->_app->hasCredentials('EC') ) {
          $this->description = '<div class="secWarning">' . MODULE_PAYMENT_PAYPAL_EXPRESS_ERROR_ADMIN_CONFIGURATION . '</div>' . $this->description;

          $this->enabled = false;
        }
      }

      if ( $this->enabled === true ) {
        if ( isset($order) && is_object($order) ) {
          $this->update_status();
        }
      }

      if ( defined('FILENAME_SHOPPING_CART') && (basename($PHP_SELF) == FILENAME_SHOPPING_CART) ) {
        if ( OSCOM_APP_PAYPAL_EC_CHECKOUT_FLOW == '1' ) {
          if ( isset($request_type) && ($request_type != 'SSL') && (ENABLE_SSL == true) ) {
            tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, tep_get_all_get_params(), 'SSL'));
          }

          header('X-UA-Compatible: IE=edge', true);
        }
      }

// When changing the shipping address due to no shipping rates being available, head straight to the checkout confirmation page
      if ( defined('FILENAME_CHECKOUT_PAYMENT') && (basename($PHP_SELF) == FILENAME_CHECKOUT_PAYMENT) && tep_session_is_registered('ppec_right_turn') ) {
        tep_session_unregister('ppec_right_turn');

        if ( tep_session_is_registered('payment') && ($payment == $this->code) ) {
          tep_redirect(tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'));
        }
      }
    }

    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)OSCOM_APP_PAYPAL_EC_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . OSCOM_APP_PAYPAL_EC_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
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
      global $cart;

      if (OSCOM_APP_PAYPAL_EC_CHECKOUT_IMAGE == '1') {
        if (OSCOM_APP_PAYPAL_EC_STATUS == '1') {
          $image_button = 'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image';
        } else {
          $image_button = 'https://fpdbs.sandbox.paypal.com/dynamicimageweb?cmd=_dynamic-image';
        }

        $params = array('locale=' . MODULE_PAYMENT_PAYPAL_EXPRESS_LANGUAGE_LOCALE);

        if ( $this->_app->hasCredentials('EC') ) {
          $response_array = $this->_app->getApiResult('EC', 'GetPalDetails');

          if (isset($response_array['PAL'])) {
            $params[] = 'pal=' . $response_array['PAL'];
            $params[] = 'ordertotal=' . $this->_app->formatCurrencyRaw($cart->show_total());
          }
        }

        if (!empty($params)) {
          $image_button .= '&' . implode('&', $params);
        }
      } else {
        $image_button = MODULE_PAYMENT_PAYPAL_EXPRESS_BUTTON;
      }

      $button_title = tep_output_string_protected(MODULE_PAYMENT_PAYPAL_EXPRESS_TEXT_BUTTON);

      if ( OSCOM_APP_PAYPAL_EC_STATUS == '0' ) {
        $button_title .= ' (' . $this->code . '; Sandbox)';
      }

      $string = '<a href="' . tep_href_link('ext/modules/payment/paypal/express.php', '', 'SSL') . '" data-paypal-button="true"><img src="' . $image_button . '" border="0" alt="" title="' . $button_title . '" /></a>';

      if ( OSCOM_APP_PAYPAL_EC_CHECKOUT_FLOW == '1' ) {
        $string .= <<<EOD
<script>
(function(d, s, id){
  var js, ref = d.getElementsByTagName(s)[0];
  if (!d.getElementById(id)){
    js = d.createElement(s); js.id = id; js.async = true;
    js.src = "//www.paypalobjects.com/js/external/paypal.v1.js";
    ref.parentNode.insertBefore(js, ref);
  }
}(document, "script", "paypal-js"));
</script>
EOD;
      }

      return $string;
    }

    function javascript_validation() {
      return false;
    }

    function selection() {
      return array('id' => $this->code,
                   'module' => $this->public_title);
    }

    function pre_confirmation_check() {
      global $ppe_token, $ppe_secret, $messageStack, $order;

      if (!tep_session_is_registered('ppe_token')) {
        tep_redirect(tep_href_link('ext/modules/payment/paypal/express.php', '', 'SSL'));
      }

      $response_array = $this->_app->getApiResult('EC', 'GetExpressCheckoutDetails', array('TOKEN' => $ppe_token));

      if ( !in_array($response_array['ACK'], array('Success', 'SuccessWithWarning')) ) {
        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, 'error_message=' . stripslashes($response_array['L_LONGMESSAGE0']), 'SSL'));
      } elseif ( !tep_session_is_registered('ppe_secret') || ($response_array['PAYMENTREQUEST_0_CUSTOM'] != $ppe_secret) ) {
        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
      }

      if ( tep_session_is_registered('ppe_order_total_check') ) {
        $messageStack->add('checkout_confirmation', '<span id="PayPalNotice">' . MODULE_PAYMENT_PAYPAL_EXPRESS_NOTICE_CHECKOUT_CONFIRMATION . '</span><script>$("#PayPalNotice").parent().css({backgroundColor: "#fcf8e3", border: "1px #faedd0 solid", color: "#a67d57", padding: "5px" });</script>', 'paypal');
      }

      $order->info['payment_method'] = '<img src="https://www.paypalobjects.com/webstatic/mktg/Logo/pp-logo-100px.png" border="0" alt="PayPal Logo" style="padding: 3px;" />';
    }

    function confirmation() {
      global $comments;

      if (!isset($comments)) {
        $comments = null;
      }

      $confirmation = false;

      if (empty($comments)) {
        $confirmation = array('fields' => array(array('title' => MODULE_PAYMENT_PAYPAL_EXPRESS_TEXT_COMMENTS,
                                                      'field' => tep_draw_textarea_field('ppecomments', 'soft', '60', '5', $comments))));
      }

      return $confirmation;
    }

    function process_button() {
      return false;
    }

    function before_process() {
      global $customer_id, $order, $sendto, $ppe_token, $ppe_payerid, $ppe_secret, $ppe_order_total_check, $HTTP_POST_VARS, $comments, $response_array;

      if (!tep_session_is_registered('ppe_token')) {
        tep_redirect(tep_href_link('ext/modules/payment/paypal/express.php', '', 'SSL'));
      }

      $response_array = $this->_app->getApiResult('EC', 'GetExpressCheckoutDetails', array('TOKEN' => $ppe_token));

      if ( in_array($response_array['ACK'], array('Success', 'SuccessWithWarning')) ) {
        if ( !tep_session_is_registered('ppe_secret') || ($response_array['PAYMENTREQUEST_0_CUSTOM'] != $ppe_secret) ) {
          tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
        } elseif ( ($response_array['PAYMENTREQUEST_0_AMT'] != $this->_app->formatCurrencyRaw($order->info['total'])) && !tep_session_is_registered('ppe_order_total_check') ) {
          tep_session_register('ppe_order_total_check');
          $ppe_order_total_check = true;

          tep_redirect(tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'));
        }
      } else {
        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, 'error_message=' . stripslashes($response_array['L_LONGMESSAGE0']), 'SSL'));
      }

      if ( tep_session_is_registered('ppe_order_total_check') ) {
        tep_session_unregister('ppe_order_total_check');
      }

      if (empty($comments)) {
        if (isset($HTTP_POST_VARS['ppecomments']) && tep_not_null($HTTP_POST_VARS['ppecomments'])) {
          $comments = tep_db_prepare_input($HTTP_POST_VARS['ppecomments']);

          $order->info['comments'] = $comments;
        }
      }

      $params = array('TOKEN' => $ppe_token,
                      'PAYERID' => $ppe_payerid,
                      'PAYMENTREQUEST_0_AMT' => $this->_app->formatCurrencyRaw($order->info['total']),
                      'PAYMENTREQUEST_0_CURRENCYCODE' => $order->info['currency']);

      if (is_numeric($sendto) && ($sendto > 0)) {
        $params['PAYMENTREQUEST_0_SHIPTONAME'] = $order->delivery['firstname'] . ' ' . $order->delivery['lastname'];
        $params['PAYMENTREQUEST_0_SHIPTOSTREET'] = $order->delivery['street_address'];
        $params['PAYMENTREQUEST_0_SHIPTOCITY'] = $order->delivery['city'];
        $params['PAYMENTREQUEST_0_SHIPTOSTATE'] = tep_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']);
        $params['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'] = $order->delivery['country']['iso_code_2'];
        $params['PAYMENTREQUEST_0_SHIPTOZIP'] = $order->delivery['postcode'];
      }

      $response_array = $this->_app->getApiResult('EC', 'DoExpressCheckoutPayment', $params);

      if ( !in_array($response_array['ACK'], array('Success', 'SuccessWithWarning')) ) {
        if ( $response_array['L_ERRORCODE0'] == '10486' ) {
          if (OSCOM_APP_PAYPAL_EC_STATUS == '1') {
            $paypal_url = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout';
          } else {
            $paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout';
          }

          $paypal_url .= '&token=' . $ppe_token;

          tep_redirect($paypal_url);
        }

        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, 'error_message=' . stripslashes($response_array['L_LONGMESSAGE0']), 'SSL'));
      }
    }

    function after_process() {
      global $response_array, $insert_id, $ppe_payerstatus, $ppe_addressstatus;

      $pp_result = 'Transaction ID: ' . tep_output_string_protected($response_array['PAYMENTINFO_0_TRANSACTIONID']) . "\n" .
                   'Payer Status: ' . tep_output_string_protected($ppe_payerstatus) . "\n" .
                   'Address Status: ' . tep_output_string_protected($ppe_addressstatus) . "\n" .
                   'Payment Status: ' . tep_output_string_protected($response_array['PAYMENTINFO_0_PAYMENTSTATUS']) . "\n" .
                   'Payment Type: ' . tep_output_string_protected($response_array['PAYMENTINFO_0_PAYMENTTYPE']) . "\n" .
                   'Pending Reason: ' . tep_output_string_protected($response_array['PAYMENTINFO_0_PENDINGREASON']);

      $sql_data_array = array('orders_id' => $insert_id,
                              'orders_status_id' => OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID,
                              'date_added' => 'now()',
                              'customer_notified' => '0',
                              'comments' => $pp_result);

      tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

      tep_session_unregister('ppe_token');
      tep_session_unregister('ppe_payerid');
      tep_session_unregister('ppe_payerstatus');
      tep_session_unregister('ppe_addressstatus');
      tep_session_unregister('ppe_secret');
    }

    function get_error() {
      return false;
    }

    function check() {
      $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'OSCOM_APP_PAYPAL_EC_STATUS'");
      if ( tep_db_num_rows($check_query) ) {
        $check = tep_db_fetch_array($check_query);

        return tep_not_null($check['configuration_value']);
      }

      return false;
    }

    function install() {
      tep_redirect(tep_href_link('paypal.php', 'action=configure&subaction=install&module=EC'));
    }

    function remove($fromApp = false) {
      if ( !$fromApp ) {
        tep_redirect(tep_href_link('paypal.php', 'action=configure&subaction=uninstall&module=EC'));
      }

      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", array_merge($this->keys(), $this->keys(true))) . "')");
    }

    function keys($fromApp = false) {
      $params = array('OSCOM_APP_PAYPAL_EC_STATUS',
                      'OSCOM_APP_PAYPAL_EC_CHECKOUT_FLOW',
                      'OSCOM_APP_PAYPAL_EC_ACCOUNT_OPTIONAL',
                      'OSCOM_APP_PAYPAL_EC_INSTANT_UPDATE',
                      'OSCOM_APP_PAYPAL_EC_CHECKOUT_IMAGE',
                      'OSCOM_APP_PAYPAL_EC_PAGE_STYLE',
                      'OSCOM_APP_PAYPAL_EC_TRANSACTION_METHOD',
                      'OSCOM_APP_PAYPAL_EC_ORDER_STATUS_ID',
                      'OSCOM_APP_PAYPAL_EC_ZONE');

      if ( $fromApp == false ) {
        $params = array('OSCOM_APP_PAYPAL_EC_SORT_ORDER');
      }

      return $params;
    }

    function getProductType($id, $attributes) {
      foreach ( $attributes as $a ) {
        $virtual_check_query = tep_db_query("select pad.products_attributes_id from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad where pa.products_id = '" . (int)$id . "' and pa.options_values_id = '" . (int)$a['value_id'] . "' and pa.products_attributes_id = pad.products_attributes_id limit 1");

        if ( tep_db_num_rows($virtual_check_query) == 1 ) {
          return 'Digital';
        }
      }

      return 'Physical';
    }

    function getTestLinkInfo() {
      $dialog_title = MODULE_PAYMENT_PAYPAL_EXPRESS_DIALOG_CONNECTION_TITLE;
      $dialog_button_close = MODULE_PAYMENT_PAYPAL_EXPRESS_DIALOG_CONNECTION_BUTTON_CLOSE;
      $dialog_success = MODULE_PAYMENT_PAYPAL_EXPRESS_DIALOG_CONNECTION_SUCCESS;
      $dialog_failed = MODULE_PAYMENT_PAYPAL_EXPRESS_DIALOG_CONNECTION_FAILED;
      $dialog_error = MODULE_PAYMENT_PAYPAL_EXPRESS_DIALOG_CONNECTION_ERROR;
      $dialog_connection_time = MODULE_PAYMENT_PAYPAL_EXPRESS_DIALOG_CONNECTION_TIME;

      $test_url = tep_href_link(FILENAME_MODULES, 'set=payment&module=' . $this->code . '&action=install&subaction=conntest');

      $js = <<<EOD
<script>
if ( typeof jQuery == 'undefined' ) {
  document.write('<scr' + 'ipt src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></scr' + 'ipt>');
}

if ( typeof jQuery.ui == 'undefined' ) {
  document.write('<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/redmond/jquery-ui.css" />');
  document.write('<scr' + 'ipt src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></scr' + 'ipt>');
}
</script>

<script>
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

      $info = '<p><img src="images/icons/locked.gif" border="0">&nbsp;<a href="javascript:openTestConnectionDialog();" style="text-decoration: underline; font-weight: bold;">' . MODULE_PAYMENT_PAYPAL_EXPRESS_DIALOG_CONNECTION_LINK_TITLE . '</a></p>' .
              '<div id="testConnectionDialog" style="display: none;"><p>';

      if ( OSCOM_APP_PAYPAL_EC_STATUS == '1' ) {
        $info .= 'Live Server:<br />https://api-3t.paypal.com/nvp';
      } else {
        $info .= 'Sandbox Server:<br />https://api-3t.sandbox.paypal.com/nvp';
      }

      $info .= '</p><div id="testConnectionDialogProgress"><p>' . MODULE_PAYMENT_PAYPAL_EXPRESS_DIALOG_CONNECTION_GENERAL_TEXT . '</p><div id="tcdprogressbar"></div></div></div>' .
               $js;

      return $info;
    }

    function getTestConnectionResult() {
      $params = array('PAYMENTREQUEST_0_CURRENCYCODE' => DEFAULT_CURRENCY,
                      'PAYMENTREQUEST_0_AMT' => '1.00');

      $response_array = $this->setExpressCheckout($params);

      if ( is_array($response_array) && isset($response_array['ACK']) ) {
        return 1;
      }

      return -1;
    }
  }
?>
