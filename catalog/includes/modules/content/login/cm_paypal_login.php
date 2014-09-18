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

  class cm_paypal_login {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;
    var $_app;

    function cm_paypal_login() {
      global $PHP_SELF;

      $this->_app = new OSCOM_PayPal();

      $this->signature = 'paypal|paypal_login|4.0|2.3';

      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CONTENT_PAYPAL_LOGIN_TITLE;
      $this->description = MODULE_CONTENT_PAYPAL_LOGIN_DESCRIPTION;

      if ( defined('OSCOM_APP_PAYPAL_LOGIN_STATUS') ) {
        $this->sort_order = OSCOM_APP_PAYPAL_LOGIN_SORT_ORDER;
        $this->enabled = in_array(OSCOM_APP_PAYPAL_LOGIN_STATUS, array('1', '0'));

        $this->description = '<div align="center">' . $this->_app->drawButton('Manage App', tep_href_link('paypal.php', 'action=configure&module=LOGIN'), 'primary', null, true) . '</div><br />' . $this->description;

        if ( basename($GLOBALS['PHP_SELF']) == 'modules_content.php' ) {
          if ( OSCOM_APP_PAYPAL_LOGIN_STATUS == '0' ) {
            $this->title .= ' [Sandbox]';
          }

          if ( !function_exists('curl_init') ) {
            $this->description = '<div class="secWarning">' . MODULE_CONTENT_PAYPAL_LOGIN_ERROR_ADMIN_CURL . '</div>' . $this->description;

            $this->enabled = false;
          }

          if ( $this->enabled === true ) {
            if ( !tep_not_null(OSCOM_APP_PAYPAL_LOGIN_CLIENT_ID) || !tep_not_null(OSCOM_APP_PAYPAL_LOGIN_SECRET) ) {
              $this->description = '<div class="secWarning">' . MODULE_CONTENT_PAYPAL_LOGIN_ERROR_ADMIN_CONFIGURATION . '</div>' . $this->description;
            }
          }
        }
      }
    }

    function execute() {
      global $HTTP_GET_VARS, $oscTemplate;

      if ( tep_not_null(OSCOM_APP_PAYPAL_LOGIN_CLIENT_ID) && tep_not_null(OSCOM_APP_PAYPAL_LOGIN_SECRET) ) {
        if ( isset($HTTP_GET_VARS['action']) ) {
          if ( $HTTP_GET_VARS['action'] == 'paypal_login' ) {
            $this->preLogin();
          } elseif ( $HTTP_GET_VARS['action'] == 'paypal_login_process' ) {
            $this->postLogin();
          }
        }

        $scopes = cm_paypal_login_get_attributes();
        $use_scopes = array('openid');

        foreach ( explode(';', OSCOM_APP_PAYPAL_LOGIN_ATTRIBUTES) as $a ) {
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

        $params = array('code' => $HTTP_GET_VARS['code'],
                        'redirect_uri' => str_replace('&amp;', '&', tep_href_link(FILENAME_LOGIN, 'action=paypal_login', 'SSL')));

        $response_token = $this->_app->getApiResult('LOGIN', 'GrantToken', $params);

        if ( !isset($response_token['access_token']) && isset($response_token['refresh_token']) ) {
          $params = array('refresh_token' => $response_token['refresh_token']);

          $response_token = $this->_app->getApiResult('LOGIN', 'RefreshToken', $params);
        }

        if ( isset($response_token['access_token']) ) {
          $params = array('access_token' => $response_token['access_token']);

          $response = $this->_app->getApiResult('LOGIN', 'UserInfo', $params);

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
      return defined('OSCOM_APP_PAYPAL_LOGIN_STATUS');
    }

    function install() {
      tep_redirect(tep_href_link('paypal.php', 'action=configure&subaction=install&module=LOGIN'));
    }

    function remove() {
      tep_redirect(tep_href_link('paypal.php', 'action=configure&subaction=uninstall&module=LOGIN'));
    }

    function keys() {
      return array('OSCOM_APP_PAYPAL_LOGIN_CONTENT_WIDTH', 'OSCOM_APP_PAYPAL_LOGIN_SORT_ORDER');
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

      if ( OSCOM_APP_PAYPAL_LOGIN_STATUS == '1' ) {
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
      return in_array($attribute, explode(';', OSCOM_APP_PAYPAL_LOGIN_ATTRIBUTES));
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
?>
