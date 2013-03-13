<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_address_book_process {
    public static function execute(app $app) {
      global $OSCOM_Customer, $OSCOM_MessageStack, $OSCOM_PDO, $process, $gender, $entry_state_has_zones, $country;

      $app->setContentFile('address_book_process.php');

      if ( isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken']) ) {
        $process = true;

        if (ACCOUNT_GENDER == 'true') $gender = isset($_POST['gender']) ? trim($_POST['gender']) : null;
        $firstname = isset($_POST['firstname']) ? trim($_POST['firstname']) : null;
        $lastname = isset($_POST['lastname']) ? trim($_POST['lastname']) : null;
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

        $error = false;

        if ( ACCOUNT_GENDER == 'true' ) {
          if ( ($gender != 'm') && ($gender != 'f') ) {
            $error = true;

            $OSCOM_MessageStack->addError('addressbook', ENTRY_GENDER_ERROR);
          }
        }

        if ( strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH ) {
          $error = true;

          $OSCOM_MessageStack->addError('addressbook', ENTRY_FIRST_NAME_ERROR);
        }

        if ( strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH ) {
          $error = true;

          $OSCOM_MessageStack->addError('addressbook', ENTRY_LAST_NAME_ERROR);
        }

        if ( strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH ) {
          $error = true;

          $OSCOM_MessageStack->addError('addressbook', ENTRY_STREET_ADDRESS_ERROR);
        }

        if ( strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH ) {
          $error = true;

          $OSCOM_MessageStack->addError('addressbook', ENTRY_POST_CODE_ERROR);
        }

        if ( strlen($city) < ENTRY_CITY_MIN_LENGTH ) {
          $error = true;

          $OSCOM_MessageStack->addError('addressbook', ENTRY_CITY_ERROR);
        }

        if ( !is_numeric($country) ) {
          $error = true;

          $OSCOM_MessageStack->addError('addressbook', ENTRY_COUNTRY_ERROR);
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

              $OSCOM_MessageStack->addError('addressbook', ENTRY_STATE_ERROR_SELECT);
            }
          } else {
            if ( strlen($state) < ENTRY_STATE_MIN_LENGTH ) {
              $error = true;

              $OSCOM_MessageStack->addError('addressbook', ENTRY_STATE_ERROR);
            }
          }
        }

        if ( $error === false ) {
          $sql_data_array = array('entry_firstname' => $firstname,
                                  'entry_lastname' => $lastname,
                                  'entry_street_address' => $street_address,
                                  'entry_postcode' => $postcode,
                                  'entry_city' => $city,
                                  'entry_country_id' => $country);

          if (ACCOUNT_GENDER == 'true') $sql_data_array['entry_gender'] = $gender;
          if (ACCOUNT_COMPANY == 'true') $sql_data_array['entry_company'] = $company;
          if (ACCOUNT_SUBURB == 'true') $sql_data_array['entry_suburb'] = $suburb;

          if (ACCOUNT_STATE == 'true') {
            if ( $zone_id > 0 ) {
              $sql_data_array['entry_zone_id'] = $zone_id;
              $sql_data_array['entry_state'] = '';
            } else {
              $sql_data_array['entry_zone_id'] = '0';
              $sql_data_array['entry_state'] = $state;
            }
          }

          if ( isset($_GET['id']) && is_numeric($_GET['id']) ) {
            $Qcheck = $OSCOM_PDO->prepare('select address_book_id from :table_address_book where address_book_id = :address_book_id and customers_id = :customers_id');
            $Qcheck->bindInt(':address_book_id', $_GET['id']);
            $Qcheck->bindInt(':customers_id', $OSCOM_Customer->getID());
            $Qcheck->execute();

            if ( $Qcheck->fetch() !== false ) {
              $OSCOM_PDO->perform('address_book', $sql_data_array, array('address_book_id' => $_GET['id'], 'customers_id' => $OSCOM_Customer->getID()));

// reregister session variables
              if ( (isset($_POST['primary']) && ($_POST['primary'] == 'on')) || ($_GET['id'] == $OSCOM_Customer->getDefaultAddressID()) ) {
                $OSCOM_Customer->setCountryID($country);
                $OSCOM_Customer->setZoneID(($zone_id > 0) ? (int)$zone_id : '0');
                $OSCOM_Customer->setDefaultAddressID($_GET['id']);
              }

              $OSCOM_MessageStack->addSuccess('addressbook', SUCCESS_ADDRESS_BOOK_ENTRY_UPDATED);
            }
          } else {
            if ( osc_count_customer_address_book_entries() < MAX_ADDRESS_BOOK_ENTRIES ) {
              $sql_data_array['customers_id'] = (int)$OSCOM_Customer->getID();

              $OSCOM_PDO->perform('address_book', $sql_data_array);

              $new_address_book_id = $OSCOM_PDO->lastInsertId();

// reregister session variables
              if ( !$OSCOM_Customer->hasDefaultAddress() || (isset($_POST['primary']) && ($_POST['primary'] == 'on')) ) {
                $OSCOM_Customer->setCountryID($country);
                $OSCOM_Customer->setZoneID(($zone_id > 0) ? (int)$zone_id : '0');
                $OSCOM_Customer->setDefaultAddressID($new_address_book_id);

                $OSCOM_MessageStack->addSuccess('addressbook', SUCCESS_ADDRESS_BOOK_ENTRY_UPDATED);
              }
            }
          }

          osc_redirect(osc_href_link('account', 'address_book', 'SSL'));
        }
      } else {
        osc_redirect(osc_href_link('account', 'address_book', 'SSL'));
      }
    }
  }
?>
