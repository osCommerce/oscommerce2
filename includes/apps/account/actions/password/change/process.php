<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_password_change_process {
    public static function execute(app $app) {
      global $OSCOM_Customer, $OSCOM_MessageStack, $OSCOM_PDO;

      if ( isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken']) ) {
        $password_current = isset($_POST['password_current']) ? trim($_POST['password_current']) : null;
        $password_new = isset($_POST['password_new']) ? trim($_POST['password_new']) : null;
        $password_confirmation = isset($_POST['password_confirmation']) ? trim($_POST['password_confirmation']) : null;

        $error = false;

        if ( strlen($password_new) < ENTRY_PASSWORD_MIN_LENGTH ) {
          $error = true;

          $OSCOM_MessageStack->addError('account_password', ENTRY_PASSWORD_NEW_ERROR);
        } elseif ( $password_new != $password_confirmation ) {
          $error = true;

          $OSCOM_MessageStack->addError('account_password', ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING);
        }

        if ( $error === false ) {
          $Qpw = $OSCOM_PDO->prepare('select customers_password from :table_customers where customers_id = :customers_id');
          $Qpw->bindInt(':customers_id', $OSCOM_Customer->getID());
          $Qpw->execute();

          if ( osc_validate_password($password_current, $Qpw->value('customers_password')) ) {
            $OSCOM_PDO->perform('customers', array('customers_password' => osc_encrypt_password($password_new)), array('customers_id' => $OSCOM_Customer->getID()));

            $OSCOM_PDO->perform('customers_info', array('customers_info_date_account_last_modified' => 'now()'), array('customers_info_id' => $OSCOM_Customer->getID()));

            $OSCOM_MessageStack->addSuccess('account', SUCCESS_PASSWORD_UPDATED);

            osc_redirect(osc_href_link('account', '', 'SSL'));
          } else {
            $error = true;

            $OSCOM_MessageStack->addError('account_password', ERROR_CURRENT_PASSWORD_NOT_MATCHING);
          }
        }
      }

      osc_redirect(osc_href_link('account', 'password&change', 'SSL'));
    }
  }
?>
