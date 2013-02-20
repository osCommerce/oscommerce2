<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_password_reset_process {
    public static function execute(app $app) {
      global $OSCOM_PDO, $Qc, $messageStack;

      if ( isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken']) ) {
        $password_new = isset($_POST['password']) ? trim($_POST['password']) : null;
        $password_confirmation = isset($_POST['confirmation']) ? trim($_POST['confirmation']) : null;

        if ( strlen($password_new) < ENTRY_PASSWORD_MIN_LENGTH ) {
          $error = true;

          $messageStack->add('password_reset', ENTRY_PASSWORD_NEW_ERROR);
        } elseif ( $password_new != $password_confirmation ) {
          $error = true;

          $messageStack->add('password_reset', ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING);
        }

        if ( $error == false ) {
          $OSCOM_PDO->perform('customers', array('customers_password' => tep_encrypt_password($password_new)), array('customers_id' => $Qc->valueInt('customers_id')));

          $OSCOM_PDO->perform('customers_info', array('customers_info_date_account_last_modified' => 'now()', 'password_reset_key' => 'null', 'password_reset_date' => 'null'), array('customers_info_id' => $Qc->valueInt('customers_id')));

          $messageStack->add_session('login', SUCCESS_PASSWORD_RESET, 'success');

          tep_redirect(tep_href_link('account', 'login', 'SSL'));
        }
      }
    }
  }
?>
