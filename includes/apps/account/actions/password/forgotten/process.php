<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_password_forgotten_process {
    public static function execute(app $app) {
      global $OSCOM_PDO, $messageStack;

      if ( isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken']) ) {
        $email_address = isset($_POST['email_address']) ? trim($_POST['email_address']) : null;

        if ( !empty($email_address) ) {
          $Qc = $OSCOM_PDO->prepare('select customers_id, customers_firstname, customers_lastname from :table_customers where customers_email_address = :customers_email_address limit 1');
          $Qc->bindValue(':customers_email_address', $email_address);
          $Qc->execute();

          if ( $Qc->fetch() !== false ) {
            $actionRecorder = new actionRecorder('ar_reset_password', $Qc->valueInt('customers_id'), $email_address);

            if ( $actionRecorder->canPerform() ) {
              $actionRecorder->record();

              $reset_key = osc_create_random_value(40);

              $OSCOM_PDO->perform('customers_info', array('password_reset_key' => $reset_key, 'password_reset_date' => 'now()'), array('customers_info_id' => $Qc->valueInt('customers_id')));

              $reset_key_url = osc_href_link('account', 'password&reset&e=' . urlencode($email_address) . '&k=' . $reset_key, 'SSL', false);

              if ( strpos($reset_key_url, '&amp;') !== false ) {
                $reset_key_url = str_replace('&amp;', '&', $reset_key_url);
              }

              osc_mail($Qc->value('customers_firstname') . ' ' . $Qc->value('customers_lastname'), $email_address, EMAIL_PASSWORD_RESET_SUBJECT, sprintf(EMAIL_PASSWORD_RESET_BODY, $reset_key_url), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

              osc_redirect(osc_href_link('account', 'password&reset&initiated', 'SSL'));
            } else {
              $actionRecorder->record(false);

              $messageStack->add('password_forgotten', sprintf(ERROR_ACTION_RECORDER, (defined('MODULE_ACTION_RECORDER_RESET_PASSWORD_MINUTES') ? (int)MODULE_ACTION_RECORDER_RESET_PASSWORD_MINUTES : 5)));
            }
          } else {
            $messageStack->add('password_forgotten', TEXT_NO_EMAIL_ADDRESS_FOUND);
          }
        } else {
          $messageStack->add('password_forgotten', TEXT_NO_EMAIL_ADDRESS_FOUND);
        }
      }
    }
  }
?>
