<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_password_reset {
    public static function execute(app $app) {
      global $OSCOM_Breadcrumb, $OSCOM_MessageStack, $OSCOM_PDO, $Qc;

      if ( isset($_GET['initiated']) ) {
        return true;
      }

      $error = false;

      if ( !isset($_GET['e']) || !isset($_GET['k']) ) {
        $error = true;

        $OSCOM_MessageStack->addError('password_forgotten', TEXT_NO_RESET_LINK_FOUND);
      }

      if ( $error == false ) {
        $email_address = isset($_GET['e']) ? trim($_GET['e']) : null;
        $password_key = isset($_GET['k']) ? trim($_GET['k']) : null;

        if ( (strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) || (osc_validate_email($email_address) == false) ) {
          $error = true;

          $OSCOM_MessageStack->addError('password_forgotten', TEXT_NO_EMAIL_ADDRESS_FOUND);
        } elseif ( strlen($password_key) != 40 ) {
          $error = true;

          $OSCOM_MessageStack->addError('password_forgotten', TEXT_NO_RESET_LINK_FOUND);
        } else {
          $Qc = $OSCOM_PDO->prepare('select c.customers_id, c.customers_email_address, ci.password_reset_key, ci.password_reset_date from :table_customers c, :table_customers_info ci where c.customers_email_address = :customers_email_address and c.customers_id = ci.customers_info_id limit 1');
          $Qc->bindValue(':customers_email_address', $email_address);
          $Qc->execute();

          if ( $Qc->fetch() !== false ) {
            if ( (strlen($Qc->value('password_reset_key')) != 40) || ($Qc->value('password_reset_key') != $password_key) || (strtotime($Qc->value('password_reset_date') . ' +1 day') <= time()) ) {
              $error = true;

              $OSCOM_MessageStack->addError('password_forgotten', TEXT_NO_RESET_LINK_FOUND);
            }
          } else {
            $error = true;

            $OSCOM_MessageStack->addError('password_forgotten', TEXT_NO_EMAIL_ADDRESS_FOUND);
          }
        }
      }

      if ( $error == true ) {
        osc_redirect(osc_href_link('account', 'password&forgotten'));
      }

      $app->setContentFile('password_reset.php');

      $OSCOM_Breadcrumb->add(NAVBAR_TITLE_PASSWORD_RESET, osc_href_link('account', 'password&reset', 'SSL'));
    }
  }
?>
