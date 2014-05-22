<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class cm_paypal_login {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function cm_paypal_login() {
      global $HTTP_GET_VARS, $PHP_SELF;

      $this->signature = 'paypal|paypal_login|1.0|2.3';

      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CONTENT_PAYPAL_LOGIN_TITLE;
      $this->description = MODULE_CONTENT_PAYPAL_LOGIN_DESCRIPTION;

      if ( defined('MODULE_CONTENT_PAYPAL_LOGIN_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_PAYPAL_LOGIN_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_PAYPAL_LOGIN_STATUS == 'True');

        if ( basename($GLOBALS['PHP_SELF']) == 'modules_content.php' ) {
          $this->description .= $this->getTestLinkInfo();

          $this->description .= $this->getShowUrlsInfo();

          if ( MODULE_CONTENT_PAYPAL_LOGIN_SERVER_TYPE == 'Sandbox' ) {
            $this->title .= ' [Sandbox]';
          }

          if ( !function_exists('curl_init') ) {
            $this->description = '<div class="secWarning">' . MODULE_CONTENT_PAYPAL_LOGIN_ERROR_ADMIN_CURL . '</div>' . $this->description;

            $this->enabled = false;
          }

          if ( $this->enabled === true ) {
            if ( !tep_not_null(MODULE_CONTENT_PAYPAL_LOGIN_CLIENT_ID) || !tep_not_null(MODULE_CONTENT_PAYPAL_LOGIN_SECRET) ) {
              $this->description = '<div class="secWarning">' . MODULE_CONTENT_PAYPAL_LOGIN_ERROR_ADMIN_CONFIGURATION . '</div>' . $this->description;
            }
          }
        }
      }

      if ( defined('FILENAME_MODULES') && ($PHP_SELF == 'modules_content.php') && isset($HTTP_GET_VARS['action']) && ($HTTP_GET_VARS['action'] == 'install') && isset($HTTP_GET_VARS['subaction']) && ($HTTP_GET_VARS['subaction'] == 'conntest') ) {
        echo $this->getTestConnectionResult();
        exit;
      }
    }

    function execute() {
      global $HTTP_GET_VARS, $oscTemplate;

      if ( tep_not_null(MODULE_CONTENT_PAYPAL_LOGIN_CLIENT_ID) && tep_not_null(MODULE_CONTENT_PAYPAL_LOGIN_SECRET) ) {
        if ( isset($HTTP_GET_VARS['action']) ) {
          if ( $HTTP_GET_VARS['action'] == 'paypal_login' ) {
            $this->preLogin();
          } elseif ( $HTTP_GET_VARS['action'] == 'paypal_login_process' ) {
            $this->postLogin();
          }
        }

        $scopes = cm_paypal_login_get_attributes();
        $use_scopes = array('openid');

        foreach ( explode(';', MODULE_CONTENT_PAYPAL_LOGIN_ATTRIBUTES) as $a ) {
          foreach ( $scopes as $group => $attributes ) {
            foreach ( $attributes as $attribute => $scope ) {
              if ( $a == $attribute ) {
                if ( !in_array($scope, $use_scopes) ) {
                  $use_scopes[] = $scope;
                }
              }
            }
          }
        }

        ob_start();
        include(DIR_WS_MODULES . 'content/' . $this->group . '/templates/paypal_login.php');
        $template = ob_get_clean();

        $oscTemplate->addContent($template, $this->group);
      }
    }

    function preLogin() {
      global $HTTP_GET_VARS, $paypal_login_access_token, $paypal_login_customer_id, $sendto, $billto;

      $return_url = tep_href_link(FILENAME_LOGIN, '', 'SSL');

      if ( isset($HTTP_GET_VARS['code']) ) {
        $paypal_login_customer_id = false;

        $params = array('code' => $HTTP_GET_VARS['code']);

        $response_token = $this->getToken($params);

        if ( !isset($response_token['access_token']) && isset($response_token['refresh_token']) ) {
          $params = array('refresh_token' => $response_token['refresh_token']);

          $response_token = $this->getRefreshToken($params);
        }

        if ( isset($response_token['access_token']) ) {
          $params = array('access_token' => $response_token['access_token']);

          $response = $this->getUserInfo($params);

          if ( isset($response['email']) ) {
            $paypal_login_access_token = $response_token['access_token'];
            tep_session_register('paypal_login_access_token');

            $force_login = false;

// check if e-mail address exists in database and login or create customer account
            if ( !tep_session_is_registered('customer_id') ) {
              $customer_id = 0;
              $customer_default_address_id = 0;

              $force_login = true;

              $email_address = tep_db_prepare_input($response['email']);

              $check_query = tep_db_query("select customers_id from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "' limit 1");
              if (tep_db_num_rows($check_query)) {
                $check = tep_db_fetch_array($check_query);

                $customer_id = (int)$check['customers_id'];
              } else {
                $customers_firstname = tep_db_prepare_input($response['given_name']);
                $customers_lastname = tep_db_prepare_input($response['family_name']);

                $sql_data_array = array('customers_firstname' => $customers_firstname,
                                        'customers_lastname' => $customers_lastname,
                                        'customers_email_address' => $email_address,
                                        'customers_telephone' => '',
                                        'customers_fax' => '',
                                        'customers_newsletter' => '0',
                                        'customers_password' => '');

                if ($this->hasAttribute('phone') && isset($response['phone_number']) && tep_not_null($response['phone_number'])) {
                  $customers_telephone = tep_db_prepare_input($response['phone_number']);

                  $sql_data_array['customers_telephone'] = $customers_telephone;
                }

                tep_db_perform(TABLE_CUSTOMERS, $sql_data_array);

                $customer_id = (int)tep_db_insert_id();

                tep_db_query("insert into " . TABLE_CUSTOMERS_INFO . " (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) values ('" . (int)$customer_id . "', '0', now())");
              }
            }

// check if paypal shipping address exists in the address book
            $ship_firstname = tep_db_prepare_input($response['given_name']);
            $ship_lastname = tep_db_prepare_input($response['family_name']);
            $ship_address = tep_db_prepare_input($response['address']['street_address']);
            $ship_city = tep_db_prepare_input($response['address']['locality']);
            $ship_zone = tep_db_prepare_input($response['address']['region']);
            $ship_zone_id = 0;
            $ship_postcode = tep_db_prepare_input($response['address']['postal_code']);
            $ship_country = tep_db_prepare_input($response['address']['country']);
            $ship_country_id = 0;
            $ship_address_format_id = 1;

            $country_query = tep_db_query("select countries_id, address_format_id from " . TABLE_COUNTRIES . " where countries_iso_code_2 = '" . tep_db_input($ship_country) . "' limit 1");
            if (tep_db_num_rows($country_query)) {
              $country = tep_db_fetch_array($country_query);

              $ship_country_id = $country['countries_id'];
              $ship_address_format_id = $country['address_format_id'];
            }

            if ($ship_country_id > 0) {
              $zone_query = tep_db_query("select zone_id from " . TABLE_ZONES . " where zone_country_id = '" . (int)$ship_country_id . "' and (zone_name = '" . tep_db_input($ship_zone) . "' or zone_code = '" . tep_db_input($ship_zone) . "') limit 1");
              if (tep_db_num_rows($zone_query)) {
                $zone = tep_db_fetch_array($zone_query);

                $ship_zone_id = $zone['zone_id'];
              }
            }

            $check_query = tep_db_query("select address_book_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and entry_firstname = '" . tep_db_input($ship_firstname) . "' and entry_lastname = '" . tep_db_input($ship_lastname) . "' and entry_street_address = '" . tep_db_input($ship_address) . "' and entry_postcode = '" . tep_db_input($ship_postcode) . "' and entry_city = '" . tep_db_input($ship_city) . "' and (entry_state = '" . tep_db_input($ship_zone) . "' or entry_zone_id = '" . (int)$ship_zone_id . "') and entry_country_id = '" . (int)$ship_country_id . "' limit 1");
            if (tep_db_num_rows($check_query)) {
              $check = tep_db_fetch_array($check_query);

              $sendto = $check['address_book_id'];
            } else {
              $sql_data_array = array('customers_id' => $customer_id,
                                      'entry_firstname' => $ship_firstname,
                                      'entry_lastname' => $ship_lastname,
                                      'entry_street_address' => $ship_address,
                                      'entry_postcode' => $ship_postcode,
                                      'entry_city' => $ship_city,
                                      'entry_country_id' => $ship_country_id);

              if (ACCOUNT_STATE == 'true') {
                if ($ship_zone_id > 0) {
                  $sql_data_array['entry_zone_id'] = $ship_zone_id;
                  $sql_data_array['entry_state'] = '';
                } else {
                  $sql_data_array['entry_zone_id'] = '0';
                  $sql_data_array['entry_state'] = $ship_zone;
                }
              }

              tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);

              $address_id = tep_db_insert_id();

              $sendto = $address_id;

              if ($customer_default_address_id < 1) {
                tep_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id = '" . (int)$address_id . "' where customers_id = '" . (int)$customer_id . "'");
                $customer_default_address_id = $address_id;
              }
            }

            if ($force_login == true) {
              $paypal_login_customer_id = $customer_id;
            } else {
              $paypal_login_customer_id = false;
            }

            if ( !tep_session_is_registered('paypal_login_customer_id') ) {
              tep_session_register('paypal_login_customer_id');
            }

            $billto = $sendto;

            if ( !tep_session_is_registered('sendto') ) {
              tep_session_register('sendto');
            }

            if ( !tep_session_is_registered('billto') ) {
              tep_session_register('billto');
            }

            $return_url = tep_href_link(FILENAME_LOGIN, 'action=paypal_login_process', 'SSL');
          }
        }
      }

      echo '<script>window.opener.location.href="' . str_replace('&amp;', '&', $return_url) . '";window.close();</script>';

      exit;
    }

    function postLogin() {
      global $paypal_login_customer_id, $login_customer_id, $language, $payment;

      if ( tep_session_is_registered('paypal_login_customer_id') ) {
        if ( $paypal_login_customer_id !== false ) {
          $login_customer_id = $paypal_login_customer_id;
        }

        tep_session_unregister('paypal_login_customer_id');
      }

// Register PayPal Express Checkout as the default payment method
      if ( !tep_session_is_registered('payment') || ($payment != 'paypal_express') ) {
        if (defined('MODULE_PAYMENT_INSTALLED') && tep_not_null(MODULE_PAYMENT_INSTALLED)) {
          if ( in_array('paypal_express.php', explode(';', MODULE_PAYMENT_INSTALLED)) ) {
            if ( !class_exists('paypal_express') ) {
              include(DIR_WS_LANGUAGES . $language . '/modules/payment/paypal_express.php');
              include(DIR_WS_MODULES . 'payment/paypal_express.php');
            }

            $ppe = new paypal_express();

            if ( $ppe->enabled ) {
              $payment = 'paypal_express';
              tep_session_register('payment');
            }
          }
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CONTENT_PAYPAL_LOGIN_STATUS');
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
      $params = array('MODULE_CONTENT_PAYPAL_LOGIN_STATUS' => array('title' => 'Enable Log In with PayPal',
                                                                    'desc' => 'Do you want to enable the Log In with PayPal module?',
                                                                    'value' => 'True',
                                                                    'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_CONTENT_PAYPAL_LOGIN_CLIENT_ID' => array('title' => 'Client ID',
                                                                       'desc' => 'Your PayPal Application Client ID.'),
                      'MODULE_CONTENT_PAYPAL_LOGIN_SECRET' => array('title' => 'Secret',
                                                                    'desc' => 'Your PayPal Application Secret.'),
                      'MODULE_CONTENT_PAYPAL_LOGIN_THEME' => array('title' => 'Theme',
                                                                   'desc' => 'Which theme should be used for the button?',
                                                                   'value' => 'Blue',
                                                                   'set_func' => 'tep_cfg_select_option(array(\'Blue\', \'Neutral\'), '),
                      'MODULE_CONTENT_PAYPAL_LOGIN_ATTRIBUTES' => array('title' => 'Information Requested From Customers',
                                                                    'desc' => 'The attributes the customer must share with you.',
                                                                    'value' => implode(';', $this->get_default_attributes()),
                                                                    'use_func' => 'cm_paypal_login_show_attributes',
                                                                    'set_func' => 'cm_paypal_login_edit_attributes('),
                      'MODULE_CONTENT_PAYPAL_LOGIN_SERVER_TYPE' => array('title' => 'Server Type',
                                                                         'desc' => 'Which server should be used? Live for production or Sandbox for testing.',
                                                                         'value' => 'Live',
                                                                         'set_func' => 'tep_cfg_select_option(array(\'Live\', \'Sandbox\'), '),
                      'MODULE_CONTENT_PAYPAL_LOGIN_VERIFY_SSL' => array('title' => 'Verify SSL Certificate',
                                                                        'desc' => 'Verify gateway server SSL certificate on connection?',
                                                                        'value' => 'True',
                                                                        'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_CONTENT_PAYPAL_LOGIN_PROXY' => array('title' => 'Proxy Server',
                                                                   'desc' => 'Send API requests through this proxy server. (host:port, eg: 123.45.67.89:8080 or proxy.example.com:8080)'),
                      'MODULE_CONTENT_PAYPAL_LOGIN_CONTENT_WIDTH' => array('title' => 'Content Width',
                                                                           'desc' => 'Should the content be shown in a full or half width container?',
                                                                           'value' => 'Full',
                                                                           'set_func' => 'tep_cfg_select_option(array(\'Full\', \'Half\'), '),
                      'MODULE_CONTENT_PAYPAL_LOGIN_SORT_ORDER' => array('title' => 'Sort order of display',
                                                                        'desc' => 'Sort order of display. Lowest is displayed first.',
                                                                        'value' => '0'));

      return $params;
    }

    function sendRequest($url, $parameters = null) {
      $server = parse_url($url);

      if ( !isset($server['port']) ) {
        $server['port'] = ($server['scheme'] == 'https') ? 443 : 80;
      }

      if ( !isset($server['path']) ) {
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
      curl_setopt($curl, CURLOPT_ENCODING, '');

      if ( MODULE_CONTENT_PAYPAL_LOGIN_VERIFY_SSL == 'True' ) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

        if ( file_exists(DIR_FS_CATALOG . 'ext/modules/payment/paypal/paypal.com.crt') ) {
          curl_setopt($curl, CURLOPT_CAINFO, DIR_FS_CATALOG . 'ext/modules/payment/paypal/paypal.com.crt');
        } elseif ( file_exists(DIR_FS_CATALOG . 'includes/cacert.pem') ) {
          curl_setopt($curl, CURLOPT_CAINFO, DIR_FS_CATALOG . 'includes/cacert.pem');
        }
      } else {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      }

      if ( tep_not_null(MODULE_CONTENT_PAYPAL_LOGIN_PROXY) ) {
        curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, true);
        curl_setopt($curl, CURLOPT_PROXY, MODULE_CONTENT_PAYPAL_LOGIN_PROXY);
      }

      $result = curl_exec($curl);

      curl_close($curl);

      return $result;
    }

    function getToken($params) {
      if ( MODULE_CONTENT_PAYPAL_LOGIN_SERVER_TYPE == 'Live' ) {
        $api_server = 'api.paypal.com';
      } else {
        $api_server = 'api.sandbox.paypal.com';
      }

      $parameters = array('client_id' => MODULE_CONTENT_PAYPAL_LOGIN_CLIENT_ID,
                          'client_secret' => MODULE_CONTENT_PAYPAL_LOGIN_SECRET,
                          'grant_type' => 'authorization_code',
                          'code' => $params['code'],
                          'redirect_uri' => str_replace('&amp;', '&', tep_href_link(FILENAME_LOGIN, 'action=paypal_login', 'SSL')));

      $post_string = '';

      foreach ($parameters as $key => $value) {
        $post_string .= $key . '=' . urlencode(utf8_encode(trim($value))) . '&';
      }

      $post_string = substr($post_string, 0, -1);

      $result = $this->sendRequest('https://' . $api_server . '/v1/identity/openidconnect/tokenservice', $post_string);

      $result_array = json_decode($result, true);

      return $result_array;
    }

    function getRefreshToken($params) {
      if ( MODULE_CONTENT_PAYPAL_LOGIN_SERVER_TYPE == 'Live' ) {
        $api_server = 'api.paypal.com';
      } else {
        $api_server = 'api.sandbox.paypal.com';
      }

      $parameters = array('client_id' => MODULE_CONTENT_PAYPAL_LOGIN_CLIENT_ID,
                          'client_secret' => MODULE_CONTENT_PAYPAL_LOGIN_SECRET,
                          'grant_type' => 'refresh_token',
                          'refresh_token' => $params['refresh_token']);

      $post_string = '';

      foreach ($parameters as $key => $value) {
        $post_string .= $key . '=' . urlencode(utf8_encode(trim($value))) . '&';
      }

      $post_string = substr($post_string, 0, -1);

      $result = $this->sendRequest('https://' . $api_server . '/v1/identity/openidconnect/tokenservice', $post_string);

      $result_array = json_decode($result, true);

      return $result_array;
    }

    function getUserInfo($params) {
      if ( MODULE_CONTENT_PAYPAL_LOGIN_SERVER_TYPE == 'Live' ) {
        $api_server = 'api.paypal.com';
      } else {
        $api_server = 'api.sandbox.paypal.com';
      }

      $result = $this->sendRequest('https://' . $api_server . '/v1/identity/openidconnect/userinfo/?schema=openid&access_token=' . $params['access_token']);

      $result_array = json_decode($result, true);

      return $result_array;
    }

    function getTestLinkInfo() {
      $dialog_title = MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_TITLE;
      $dialog_button_close = MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_BUTTON_CLOSE;
      $dialog_success = MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_SUCCESS;
      $dialog_failed = MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_FAILED;
      $dialog_error = MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_ERROR;
      $dialog_connection_time = MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_TIME;

      $test_url = tep_href_link('modules_content.php', 'module=' . $this->code . '&action=install&subaction=conntest');

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

      $info = '<p><img src="images/icons/locked.gif" border="0">&nbsp;<a href="javascript:openTestConnectionDialog();" style="text-decoration: underline; font-weight: bold;">' . MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_LINK_TITLE . '</a></p>' .
              '<div id="testConnectionDialog" style="display: none;"><p>';

      if ( MODULE_CONTENT_PAYPAL_LOGIN_SERVER_TYPE == 'Live' ) {
        $info .= 'Live Server:<br />https://api.paypal.com';
      } else {
        $info .= 'Sandbox Server:<br />https://api.sandbox.paypal.com';
      }

      $info .= '</p><div id="testConnectionDialogProgress"><p>' . MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_GENERAL_TEXT . '</p><div id="tcdprogressbar"></div></div></div>' .
               $js;

      return $info;
    }

    function getTestConnectionResult() {
      $params = array('code' => 'oscom2_conn_test');

      $response = $this->getToken($params);

      if ( is_array($response) && isset($response['error']) ) {
        return 1;
      }

      return -1;
    }

    function getShowUrlsInfo() {
      $dialog_title = MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_URLS_TITLE;
      $dialog_button_close = MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_URLS_BUTTON_CLOSE;

      $js = <<<EOD
<script type="text/javascript">
function openShowUrlsDialog() {
  var d = $('<div>').html($('#showUrlsDialog').html()).dialog({
    autoOpen: false,
    modal: true,
    title: '{$dialog_title}',
    buttons: {
      '{$dialog_button_close}': function () {
        $(this).dialog('destroy');
      }
    },
    width: 600
  });

  d.dialog('open');
}
</script>
EOD;

      $info = '<p><img src="images/icon_info.gif" border="0">&nbsp;<a href="javascript:openShowUrlsDialog();" style="text-decoration: underline; font-weight: bold;">' . MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_URLS_LINK_TITLE . '</a></p>' .
              '<div id="showUrlsDialog" style="display: none;">' .
              '  <p><strong>' . MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_URLS_RETURN_TEXT . '</strong><br /><br />' . htmlspecialchars(str_replace('&amp;', '&', tep_catalog_href_link('login.php', 'action=paypal_login', 'SSL'))) . '</p>' .
              '  <p><strong>' . MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_URLS_PRIVACY_TEXT . '</strong><br /><br />' . htmlspecialchars(str_replace('&amp;', '&', tep_catalog_href_link('privacy.php', '', 'SSL'))) . '</p>' .
              '  <p><strong>' . MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_URLS_TERMS_TEXT . '</strong><br /><br />' . htmlspecialchars(str_replace('&amp;', '&', tep_catalog_href_link('conditions.php', '', 'SSL'))) . '</p>' .
              '</div>' .
              $js;

      return $info;
    }

    function hasAttribute($attribute) {
      return in_array($attribute, explode(';', MODULE_CONTENT_PAYPAL_LOGIN_ATTRIBUTES));
    }

    function get_default_attributes() {
      $data = array();

      foreach ( cm_paypal_login_get_attributes() as $group => $attributes ) {
        foreach ( $attributes as $attribute => $scope ) {
          $data[] = $attribute;
        }
      }

      return $data;
    }
  }

  function cm_paypal_login_get_attributes() {
    return array('personal' => array('full_name' => 'profile',
                                     'date_of_birth' => 'profile',
                                     'age_range' => 'https://uri.paypal.com/services/paypalattributes',
                                     'gender' => 'profile'),
                 'address' => array('email_address' => 'email',
                                    'street_address' => 'address',
                                    'city' => 'address',
                                    'state' => 'address',
                                    'country' => 'address',
                                    'zip_code' => 'address',
                                    'phone' => 'phone'),
                 'account' => array('account_status' => 'https://uri.paypal.com/services/paypalattributes',
                                    'account_type' => 'https://uri.paypal.com/services/paypalattributes',
                                    'account_creation_date' => 'https://uri.paypal.com/services/paypalattributes',
                                    'time_zone' => 'profile',
                                    'locale' => 'profile',
                                    'language' => 'profile'),
                 'checkout' => array('seamless_checkout' => 'https://uri.paypal.com/services/expresscheckout'));
  }

  function cm_paypal_login_get_required_attributes() {
    return array('full_name',
                 'email_address',
                 'street_address',
                 'city',
                 'state',
                 'country',
                 'zip_code');
  }

  function cm_paypal_login_show_attributes($text) {
    $active = explode(';', $text);

    $output = '';

    foreach ( cm_paypal_login_get_attributes() as $group => $attributes ) {
      foreach ( $attributes as $attribute => $scope ) {
        if ( in_array($attribute, $active) ) {
          $output .= constant('MODULE_CONTENT_PAYPAL_LOGIN_ATTR_' . $attribute) . '<br />';
        }
      }
    }

    if ( !empty($output) ) {
      $output = substr($output, 0, -6);
    }

    return $output;
  }

  function cm_paypal_login_edit_attributes($values, $key) {
    $values_array = explode(';', $values);

    $required_array = cm_paypal_login_get_required_attributes();

    $output = '';

    foreach ( cm_paypal_login_get_attributes() as $group => $attributes ) {
      $output .= '<strong>' . constant('MODULE_CONTENT_PAYPAL_LOGIN_ATTR_GROUP_' . $group) . '</strong><br />';

      foreach ( $attributes as $attribute => $scope ) {
        if ( in_array($attribute, $required_array) ) {
          $output .= tep_draw_radio_field('cm_paypal_login_attributes_tmp_' . $attribute, $attribute, true) . '&nbsp;';
        } else {
          $output .= tep_draw_checkbox_field('cm_paypal_login_attributes[]', $attribute, in_array($attribute, $values_array)) . '&nbsp;';
        }

        $output .= constant('MODULE_CONTENT_PAYPAL_LOGIN_ATTR_' . $attribute) . '<br />';
      }
    }

    if (!empty($output)) {
      $output = '<br />' . substr($output, 0, -6);
    }

    $output .= tep_draw_hidden_field('configuration[' . $key . ']', '', 'id="cmpl_attributes"');

    $output .= '<script type="text/javascript">
                function cmpl_update_cfg_value() {
                  var cmpl_selected_attributes = \'\';

                  if ($(\'input[name^="cm_paypal_login_attributes_tmp_"]\').length > 0) {
                    $(\'input[name^="cm_paypal_login_attributes_tmp_"]\').each(function() {
                      cmpl_selected_attributes += $(this).attr(\'value\') + \';\';
                    });
                  }

                  if ($(\'input[name="cm_paypal_login_attributes[]"]\').length > 0) {
                    $(\'input[name="cm_paypal_login_attributes[]"]:checked\').each(function() {
                      cmpl_selected_attributes += $(this).attr(\'value\') + \';\';
                    });
                  }

                  if (cmpl_selected_attributes.length > 0) {
                    cmpl_selected_attributes = cmpl_selected_attributes.substring(0, cmpl_selected_attributes.length - 1);
                  }

                  $(\'#cmpl_attributes\').val(cmpl_selected_attributes);
                }

                $(function() {
                  cmpl_update_cfg_value();

                  if ($(\'input[name="cm_paypal_login_attributes[]"]\').length > 0) {
                    $(\'input[name="cm_paypal_login_attributes[]"]\').change(function() {
                      cmpl_update_cfg_value();
                    });
                  }
                });
                </script>';

    return $output;
  }
?>
