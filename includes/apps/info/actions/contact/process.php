<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_info_action_contact_process {
    public static function execute(app $app) {
      global $OSCOM_Customer, $messageStack;

      if ( isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken']) ) {
        $error = false;

        $name = isset($_POST['name']) ? trim($_POST['name']) : null;
        $email_address = isset($_POST['email']) ? trim($_POST['email']) : null;
        $enquiry = isset($_POST['enquiry']) ? trim($_POST['enquiry']) : null;

        if ( !osc_validate_email($email_address) ) {
          $error = true;

          $messageStack->add('contact', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
        }

        $actionRecorder = new actionRecorder('ar_contact_us', ($OSCOM_Customer->isLoggedOn() ? $OSCOM_Customer->getID() : null), $name);

        if ( !$actionRecorder->canPerform() ) {
          $error = true;

          $actionRecorder->record(false);

          $messageStack->add('contact', sprintf(ERROR_ACTION_RECORDER, (defined('MODULE_ACTION_RECORDER_CONTACT_US_EMAIL_MINUTES') ? (int)MODULE_ACTION_RECORDER_CONTACT_US_EMAIL_MINUTES : 15)));
        }

        if ( $error === false ) {
          osc_mail(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, EMAIL_SUBJECT, $enquiry, $name, $email_address);

          $actionRecorder->record();

          osc_redirect(osc_href_link('info', 'contact&success'));
        }
      }
    }
  }
?>
