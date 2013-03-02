<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_login_process {
    public static function execute(app $app) {
      global $OSCOM_NavigationHistory, $OSCOM_PDO, $messageStack;

      if ( isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken']) ) {
        $error = false;

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
          if ( !osc_validate_password($password, $Qcheck->value('customers_password')) ) {
            $error = true;
          } else {
            if ( SESSION_RECREATE == 'True' ) {
              osc_session_recreate();
            }

// migrate old hashed password to new phpass password
            if ( osc_password_type($Qcheck->value('customers_password')) != 'phpass' ) {
              $Qupdate = $OSCOM_PDO->prepare('update :table_customers set customers_password = :customers_password where customers_id = :customers_id');
              $Qupdate->bindValue(':customers_password', osc_encrypt_password($password));
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
            $_SESSION['sessiontoken'] = md5(osc_rand() . osc_rand() . osc_rand() . osc_rand());

// restore cart contents
            $_SESSION['cart']->restore_contents();

            if ( $OSCOM_NavigationHistory->hasSnapshot() ) {
              $origin_href = $OSCOM_NavigationHistory->getSnapshotURL(true);

              $OSCOM_NavigationHistory->resetSnapshot();

              osc_redirect($origin_href);
            } else {
              osc_redirect(osc_href_link());
            }
          }
        }

        if ( $error === true ) {
          $messageStack->add('login', TEXT_LOGIN_ERROR);
        }
      }
    }
  }
?>
