<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_checkout_action_payment_address_process {
    public static function execute(app $app) {
      global $OSCOM_PDO, $messageStack, $process, $entry_state_has_zones, $country;

      $error = false;
      $process = false;

      if ( isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken']) ) {
// process a new billing address
        if ( isset($_POST['firstname']) && !empty($_POST['firstname']) && isset($_POST['lastname']) && !empty($_POST['lastname']) && isset($_POST['street_address']) && !empty($_POST['street_address']) ) {
          $process = true;

          if (ACCOUNT_GENDER == 'true') $gender = isset($_POST['gender']) ? trim($_POST['gender']) : null;
          if (ACCOUNT_COMPANY == 'true') $company = isset($_POST['company']) ? trim($_POST['company']) : null;
          $firstname = trim($_POST['firstname']);
          $lastname = trim($_POST['lastname']);
          $street_address = trim($_POST['street_address']);
          if (ACCOUNT_SUBURB == 'true') $suburb = isset($_POST['suburb']) ? trim($_POST['suburb']) : null;
          $postcode = isset($_POST['postcode']) ? trim($_POST['postcode']) : null;
          $city = isset($_POST['city']) ? trim($_POST['city']) : null;
          $country = isset($_POST['country']) ? trim($_POST['country']) : null;

          if ( ACCOUNT_STATE == 'true' ) {
            $zone_id = isset($_POST['zone_id']) ? trim($_POST['zone_id']) : null;
            $state = isset($_POST['state']) ? trim($_POST['state']) : null;
          }

          if (ACCOUNT_GENDER == 'true') {
            if ( ($gender != 'm') && ($gender != 'f') ) {
              $error = true;

              $messageStack->add('checkout_address', ENTRY_GENDER_ERROR);
            }
          }

          if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
            $error = true;

            $messageStack->add('checkout_address', ENTRY_FIRST_NAME_ERROR);
          }

          if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
            $error = true;

            $messageStack->add('checkout_address', ENTRY_LAST_NAME_ERROR);
          }

          if (strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
            $error = true;

            $messageStack->add('checkout_address', ENTRY_STREET_ADDRESS_ERROR);
          }

          if (strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
            $error = true;

            $messageStack->add('checkout_address', ENTRY_POST_CODE_ERROR);
          }

          if (strlen($city) < ENTRY_CITY_MIN_LENGTH) {
            $error = true;

            $messageStack->add('checkout_address', ENTRY_CITY_ERROR);
          }

          if (ACCOUNT_STATE == 'true') {
            $zone_id = 0;

            $Qcheck = $OSCOM_PDO->prepare('select zone_id from :table_zones where zone_country_id = :zone_country_id limit 1');
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
                $zone_id = $result[0]['zone_id'];
              } else {
                $error = true;

                $messageStack->add('checkout_address', ENTRY_STATE_ERROR_SELECT);
              }
            } else {
              if (strlen($state) < ENTRY_STATE_MIN_LENGTH) {
                $error = true;

                $messageStack->add('checkout_address', ENTRY_STATE_ERROR);
              }
            }
          }

          if ( (is_numeric($country) == false) || ($country < 1) ) {
            $error = true;

            $messageStack->add('checkout_address', ENTRY_COUNTRY_ERROR);
          }

          if ($error == false) {
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

            if (ACCOUNT_STATE == 'true') {
              if ($zone_id > 0) {
                $sql_data_array['entry_zone_id'] = $zone_id;
                $sql_data_array['entry_state'] = '';
              } else {
                $sql_data_array['entry_zone_id'] = '0';
                $sql_data_array['entry_state'] = $state;
              }
            }

            $OSCOM_PDO->perform('address_book', $sql_data_array);

            $_SESSION['billto'] = $OSCOM_PDO->lastInsertId();

            if ( isset($_SESSION['payment']) ) {
              unset($_SESSION['payment']);
            }

            tep_redirect(tep_href_link('checkout', 'payment', 'SSL'));
          }
// process the selected shipping destination
        } elseif ( isset($_POST['address']) ) {
          $reset_payment = false;

          if ( isset($_SESSION['billto']) ) {
            if ( $_SESSION['billto'] != $_POST['address'] ) {
              if ( isset($_SESSION['payment']) ) {
                $reset_payment = true;
              }
            }
          }

          $_SESSION['billto'] = $_POST['address'];

          $Qcheck = $OSCOM_PDO->prepare('select address_book_id from :table_address_book where address_book_id = :address_book_id and customers_id = :customers_id');
          $Qcheck->bindInt(':address_book_id', $_SESSION['billto']);
          $Qcheck->bindInt(':customers_id', $_SESSION['customer_id']);
          $Qcheck->execute();

          if ( $Qcheck->fetch() !== false ) {
            if ( $reset_payment == true ) {
              unset($_SESSION['payment']);
            }

            tep_redirect(tep_href_link('checkout', 'payment', 'SSL'));
          } else {
            unset($_SESSION['billto']);
          }
        } else {
          $_SESSION['billto'] = $_SESSION['customer_default_address_id'];

          tep_redirect(tep_href_link('checkout', 'payment', 'SSL'));
        }
      }
    }
  }
?>
