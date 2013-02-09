<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_login extends app {
    public function __construct() {
      global $session_started, $OSCOM_PDO, $messageStack, $breadcrumb;

// redirect the customer to a friendly cookie-must-be-enabled page if cookies are disabled (or the session has not started)
      if ( $session_started === false ) {
        tep_redirect(tep_href_link(FILENAME_COOKIE_USAGE));
      }

      $error = false;

      if ( isset($_GET['action']) && ($_GET['action'] == 'process') && isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken']) ) {
        $email_address = isset($_POST['email_address']) ? trim($_POST['email_address']) : null;
        $password = isset($_POST['password']) ? trim($_POST['password']) : null;

// Check if email exists
        $Qcheck = $OSCOM_PDO->prepare('select customers_id, customers_firstname, customers_password, customers_email_address, customers_default_address_id from :table_customers where customers_email_address = :customers_email_address');
        $Qcheck->bindValue(':customers_email_address', $email_address);
        $Qcheck->execute();

        if ( $Qcheck->fetch() === false ) {
          $error = true;
        } else {
// Check that password is good
          if ( !tep_validate_password($password, $Qcheck->value('customers_password')) ) {
            $error = true;
          } else {
            if ( SESSION_RECREATE == 'True' ) {
              tep_session_recreate();
            }

// migrate old hashed password to new phpass password
            if ( tep_password_type($Qcheck->value('customers_password')) != 'phpass' ) {
              $Qupdate = $OSCOM_PDO->prepare('update :table_customers set customers_password = :customers_password where customers_id = :customers_id');
              $Qupdate->bindValue(':customers_password', tep_encrypt_password($password));
              $Qupdate->bindInt(':customers_id', $Qcheck->valueInt('customers_id'));
              $Qupdate->execute();
            }

            $Qcountry = $OSCOM_PDO->prepare('select entry_country_id, entry_zone_id from :table_address_book where customers_id = :customers_id and address_book_id = :address_book_id');
            $Qcountry->bindInt(':customers_id', $Qcheck->valueInt('customers_id'));
            $Qcountry->bindInt(':address_book_id', $Qcheck->valueInt('customers_default_address_id'));
            $Qcountry->execute();

            $_SESSION['customer_id'] = $Qcheck->valueInt('customers_id');
            $_SESSION['customer_default_address_id'] = $Qcheck->valueInt('customers_default_address_id');
            $_SESSION['customer_first_name'] = $Qcheck->value('customers_firstname');
            $_SESSION['customer_country_id'] = $Qcountry->valueInt('entry_country_id');
            $_SESSION['customer_zone_id'] = $Qcountry->valueInt('entry_zone_id');

            $Qupdate = $OSCOM_PDO->prepare('update :table_customers_info set customers_info_date_of_last_logon = now(), customers_info_number_of_logons = customers_info_number_of_logons+1, password_reset_key = null, password_reset_date = null where customers_info_id = :customers_info_id');
            $Qupdate->bindInt(':customers_info_id', $_SESSION['customer_id']);
            $Qupdate->execute();

// reset session token
            $_SESSION['sessiontoken'] = md5(tep_rand() . tep_rand() . tep_rand() . tep_rand());

// restore cart contents
            $_SESSION['cart']->restore_contents();

            if ( sizeof($_SESSION['navigation']->snapshot) > 0 ) {
              $origin_href = tep_href_link($_SESSION['navigation']->snapshot['page'], tep_array_to_string($_SESSION['navigation']->snapshot['get'], array(session_name())), $_SESSION['navigation']->snapshot['mode']);

              $_SESSION['navigation']->clear_snapshot();

              tep_redirect($origin_href);
            } else {
              tep_redirect(tep_href_link(FILENAME_DEFAULT));
            }
          }
        }
      }

      if ( $error === true ) {
        $messageStack->add('login', TEXT_LOGIN_ERROR);
      }

      $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_LOGIN, '', 'SSL'));
    }
  }
?>
