<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_create_account extends app {
    public function __construct() {
      global $OSCOM_PDO, $process, $entry_state_has_zones, $country, $messageStack, $breadcrumb;

      $process = false;

      if ( isset($_POST['action']) && ($_POST['action'] == 'process') && isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken']) ) {
        $process = true;

        if (ACCOUNT_GENDER == 'true') $gender = isset($_POST['gender']) ? trim($_POST['gender']) : null;
        $firstname = isset($_POST['firstname']) ? trim($_POST['firstname']) : null;
        $lastname = isset($_POST['lastname']) ? trim($_POST['lastname']) : null;
        if (ACCOUNT_DOB == 'true') $dob = isset($_POST['dob']) ? trim($_POST['dob']) : null;
        $email_address = isset($_POST['email_address']) ? trim($_POST['email_address']) : null;
        if (ACCOUNT_COMPANY == 'true') $company = isset($_POST['company']) ? trim($_POST['company']) : null;
        $street_address = isset($_POST['street_address']) ? trim($_POST['street_address']) : null;
        if (ACCOUNT_SUBURB == 'true') $suburb = isset($_POST['suburb']) ? trim($_POST['suburb']) : null;
        $postcode = isset($_POST['postcode']) ? trim($_POST['postcode']) : null;
        $city = isset($_POST['city']) ? trim($_POST['city']) : null;

        if ( ACCOUNT_STATE == 'true' ) {
          $state = isset($_POST['state']) ? trim($_POST['state']) : null;
          $zone_id = isset($_POST['zone_id']) ? trim($_POST['zone_id']) : null;
        }

        $country = isset($_POST['country']) ? trim($_POST['country']) : null;
        $telephone = isset($_POST['telephone']) ? trim($_POST['telephone']) : null;
        $fax = isset($_POST['fax']) ? trim($_POST['fax']) : null;
        $newsletter = isset($_POST['newsletter']) ? trim($_POST['newsletter']) : null;
        $password = isset($_POST['password']) ? trim($_POST['password']) : null;
        $confirmation = isset($_POST['confirmation']) ? trim($_POST['confirmation']) : null;

        $error = false;

        if ( ACCOUNT_GENDER == 'true' ) {
          if ( ($gender != 'm') && ($gender != 'f') ) {
            $error = true;

            $messageStack->add('create_account', ENTRY_GENDER_ERROR);
          }
        }

        if ( strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH ) {
          $error = true;

          $messageStack->add('create_account', ENTRY_FIRST_NAME_ERROR);
        }

        if ( strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH ) {
          $error = true;

          $messageStack->add('create_account', ENTRY_LAST_NAME_ERROR);
        }

        if ( ACCOUNT_DOB == 'true' ) {
          if ( !is_numeric(tep_date_raw($dob)) || (@checkdate(substr(tep_date_raw($dob), 4, 2), substr(tep_date_raw($dob), 6, 2), substr(tep_date_raw($dob), 0, 4)) == false) ) {
            $error = true;

            $messageStack->add('create_account', ENTRY_DATE_OF_BIRTH_ERROR);
          }
        }

        if ( strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH ) {
          $error = true;

          $messageStack->add('create_account', ENTRY_EMAIL_ADDRESS_ERROR);
        } elseif ( !tep_validate_email($email_address) ) {
          $error = true;

          $messageStack->add('create_account', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
        } else {
          $Qcheck = $OSCOM_PDO->prepare('select customers_email_address from :table_customers where customers_email_address = :customers_email_address limit 1');
          $Qcheck->bindValue(':customers_email_address', $email_address);
          $Qcheck->execute();

          if ( $Qcheck->fetch() !== false ) {
            $error = true;

            $messageStack->add('create_account', ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
          }
        }

        if ( strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH ) {
          $error = true;

          $messageStack->add('create_account', ENTRY_STREET_ADDRESS_ERROR);
        }

        if ( strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH ) {
          $error = true;

          $messageStack->add('create_account', ENTRY_POST_CODE_ERROR);
        }

        if ( strlen($city) < ENTRY_CITY_MIN_LENGTH ) {
          $error = true;

          $messageStack->add('create_account', ENTRY_CITY_ERROR);
        }

        if ( !is_numeric($country) ) {
          $error = true;

          $messageStack->add('create_account', ENTRY_COUNTRY_ERROR);
        }

        if ( ACCOUNT_STATE == 'true' ) {
          $zone_id = 0;

          $Qcheck = $OSCOM_PDO->prepare('select zone_country_id from :table_zones where zone_country_id = :zone_country_id limit 1');
          $Qcheck->bindInt(':zone_country_id', $country);
          $Qcheck->execute();

          $entry_state_has_zones = ($Qcheck->fetch() !== false);

          if ( $entry_state_has_zones === true ) {
            $Qzone = $OSCOM_PDO->prepare('select distinct zone_id from :table_zones where zone_country_id = :zone_country_id and (zone_name = :zone_name or zone_code = :zone_code)');
            $Qzone->bindInt(':zone_country_id', $country);
            $Qzone->bindValue(':zone_name', $state);
            $Qzone->bindValue(':zone_code', $state);
            $Qzone->execute();

            $result = $Qzone->fetchAll();

            if ( count($result) === 1 ) {
              $zone_id = (int)$result[0]['zone_id'];
            } else {
              $error = true;

              $messageStack->add('create_account', ENTRY_STATE_ERROR_SELECT);
            }
          } else {
            if ( strlen($state) < ENTRY_STATE_MIN_LENGTH ) {
              $error = true;

              $messageStack->add('create_account', ENTRY_STATE_ERROR);
            }
          }
        }

        if ( strlen($telephone) < ENTRY_TELEPHONE_MIN_LENGTH ) {
          $error = true;

          $messageStack->add('create_account', ENTRY_TELEPHONE_NUMBER_ERROR);
        }

        if ( strlen($password) < ENTRY_PASSWORD_MIN_LENGTH ) {
          $error = true;

          $messageStack->add('create_account', ENTRY_PASSWORD_ERROR);
        } elseif ( $password != $confirmation ) {
          $error = true;

          $messageStack->add('create_account', ENTRY_PASSWORD_ERROR_NOT_MATCHING);
        }

        if ( $error === false ) {
          $sql_data_array = array('customers_firstname' => $firstname,
                                  'customers_lastname' => $lastname,
                                  'customers_email_address' => $email_address,
                                  'customers_telephone' => $telephone,
                                  'customers_fax' => $fax,
                                  'customers_newsletter' => $newsletter,
                                  'customers_password' => tep_encrypt_password($password));

          if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $gender;
          if (ACCOUNT_DOB == 'true') $sql_data_array['customers_dob'] = tep_date_raw($dob);

          $OSCOM_PDO->perform('customers', $sql_data_array);

          $_SESSION['customer_id'] = $OSCOM_PDO->lastInsertId();

          $sql_data_array = array('customers_id' => $_SESSION['customer_id'],
                                  'entry_firstname' => $firstname,
                                  'entry_lastname' => $lastname,
                                  'entry_street_address' => $street_address,
                                  'entry_postcode' => $postcode,
                                  'entry_city' => $city,
                                  'entry_country_id' => $country);

          if (ACCOUNT_GENDER == 'true') $sql_data_array['entry_gender'] = $gender;
          if (ACCOUNT_COMPANY == 'true') $sql_data_array['entry_company'] = $company;
          if (ACCOUNT_SUBURB == 'true') $sql_data_array['entry_suburb'] = $suburb;

          if ( ACCOUNT_STATE == 'true' ) {
            if ( $zone_id > 0 ) {
              $sql_data_array['entry_zone_id'] = $zone_id;
              $sql_data_array['entry_state'] = '';
            } else {
              $sql_data_array['entry_zone_id'] = '0';
              $sql_data_array['entry_state'] = $state;
            }
          }

          $OSCOM_PDO->perform('address_book', $sql_data_array);

          $address_id = $OSCOM_PDO->lastInsertId();

          $OSCOM_PDO->perform('customers', array('customers_default_address_id' => (int)$address_id), array('customers_id' => (int)$_SESSION['customer_id']));

          $OSCOM_PDO->perform('customers_info', array('customers_info_id' => (int)$_SESSION['customer_id'],
                                                      'customers_info_number_of_logons' => '0',
                                                      'customers_info_date_account_created' => 'now()'));

          $_SESSION['customer_first_name'] = $firstname;
          $_SESSION['customer_default_address_id'] = $address_id;
          $_SESSION['customer_country_id'] = $country;
          $_SESSION['customer_zone_id'] = $zone_id;

// reset session token
          $_SESSION['sessiontoken'] = md5(tep_rand() . tep_rand() . tep_rand() . tep_rand());

// restore cart contents
          $_SESSION['cart']->restore_contents();

          if ( SESSION_RECREATE == 'True' ) {
            tep_session_recreate();
          }

// build the message content
          $name = $firstname . ' ' . $lastname;

          if ( ACCOUNT_GENDER == 'true' ) {
            if ( $gender == 'm' ) {
              $email_text = sprintf(EMAIL_GREET_MR, $lastname);
            } else {
              $email_text = sprintf(EMAIL_GREET_MS, $lastname);
            }
          } else {
            $email_text = sprintf(EMAIL_GREET_NONE, $firstname);
          }

          $email_text .= EMAIL_WELCOME . EMAIL_TEXT . EMAIL_CONTACT . EMAIL_WARNING;

          tep_mail($name, $email_address, EMAIL_SUBJECT, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

          tep_redirect(tep_href_link(FILENAME_CREATE_ACCOUNT, 'success', 'SSL'));
        }
      }

      $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'));
    }
  }
?>
