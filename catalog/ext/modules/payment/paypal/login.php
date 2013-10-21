<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../');
  require('includes/application_top.php');

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CREATE_ACCOUNT);

  if ( file_exists(DIR_WS_LANGUAGES . $language . '/modules/content/cm_login_paypal_login.php') ) {
    include(DIR_WS_LANGUAGES . $language . '/modules/content/cm_login_paypal_login.php');
  }

  include(DIR_WS_MODULES . 'content/cm_login_paypal_login.php');

  $cm_paypal_login = new cm_login_paypal_login();

  if ( $cm_paypal_login->isEnabled() ) {
    if ( isset($HTTP_GET_VARS['code']) ) {
      $params = array('code' => $HTTP_GET_VARS['code']);

      $response_token = $cm_paypal_login->getToken($params);

      if ( !isset($response_token['access_token']) && isset($response_token['refresh_token']) ) {
        $params = array('refresh_token' => $response_token['refresh_token']);

        $response_token = $cm_paypal_login->getRefreshToken($params);
      }

      if ( isset($response_token['access_token']) ) {
        $params = array('access_token' => $response_token['access_token']);

        $response = $cm_paypal_login->getUserInfo($params);

        if ( isset($response['email']) ) {
          $paypal_login_access_token = $response_token['access_token'];
          tep_session_register('paypal_login_access_token');

          $force_login = false;

// check if e-mail address exists in database and login or create customer account
          if (!tep_session_is_registered('customer_id')) {
            $customer_id = 0;
            $customer_default_address_id = 0;

            $force_login = true;

            $email_address = tep_db_prepare_input($response['email']);

            $check_query = tep_db_query("select * from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "' limit 1");
            if (tep_db_num_rows($check_query)) {
              $check = tep_db_fetch_array($check_query);

              $customer_id = $check['customers_id'];
              $customers_firstname = $check['customers_firstname'];
              $customer_default_address_id = $check['customers_default_address_id'];
            } else {
              $customers_firstname = tep_db_prepare_input($response['given_name']);
              $customers_lastname = tep_db_prepare_input($response['family_name']);

              $customer_password = tep_create_random_value(max(ENTRY_PASSWORD_MIN_LENGTH, 8));

              $sql_data_array = array('customers_firstname' => $customers_firstname,
                                      'customers_lastname' => $customers_lastname,
                                      'customers_email_address' => $email_address,
                                      'customers_telephone' => '',
                                      'customers_fax' => '',
                                      'customers_newsletter' => '0',
                                      'customers_password' => tep_encrypt_password($customer_password));

              if (isset($response['phone_number']) && tep_not_null($response['phone_number'])) {
                $customers_telephone = tep_db_prepare_input($response['phone_number']);

                $sql_data_array['customers_telephone'] = $customers_telephone;
              }

              tep_db_perform(TABLE_CUSTOMERS, $sql_data_array);

              $customer_id = tep_db_insert_id();

              tep_db_query("insert into " . TABLE_CUSTOMERS_INFO . " (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) values ('" . (int)$customer_id . "', '0', now())");

// build the message content
              $name = $customers_firstname . ' ' . $customers_lastname;
              $email_text = sprintf(EMAIL_GREET_NONE, $customers_firstname) . EMAIL_WELCOME . sprintf(MODULE_CONTENT_PAYPAL_LOGIN_EMAIL_PASSWORD, $email_address, $customer_password) . EMAIL_TEXT . EMAIL_CONTACT . EMAIL_WARNING;
              tep_mail($name, $email_address, EMAIL_SUBJECT, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
            }

            if (SESSION_RECREATE == 'True') {
              tep_session_recreate();
            }

            $customer_first_name = $customers_firstname;
            tep_session_register('customer_id');
            tep_session_register('customer_first_name');

// reset session token
            $sessiontoken = md5(tep_rand() . tep_rand() . tep_rand() . tep_rand());
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
            $customer_country_id = $ship_country_id;
            $customer_zone_id = $ship_zone_id;
            tep_session_register('customer_default_address_id');
            tep_session_register('customer_country_id');
            tep_session_register('customer_zone_id');

            $billto = $sendto;

// restore cart contents
            $cart->restore_contents();
          }
        }
      }
    }
  }

  if (sizeof($navigation->snapshot) > 0) {
    $redirect_url = tep_href_link($navigation->snapshot['page'], tep_array_to_string($navigation->snapshot['get'], array(tep_session_name())), $navigation->snapshot['mode']);
    $navigation->clear_snapshot();
  } else {
    $redirect_url = tep_href_link(FILENAME_DEFAULT);
  }
?>

<script>
window.opener.location.href = "<?php echo $redirect_url; ?>";

window.close();
</script>

<?php
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
