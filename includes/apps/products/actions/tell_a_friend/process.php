<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_products_action_tell_a_friend_process {
    public static function execute(app $app) {
      global $OSCOM_Customer, $OSCOM_MessageStack, $Qp, $from_name, $from_email_address;

      if ( isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken']) ) {
        $error = false;

        $to_email_address = isset($_POST['to_email_address']) ? trim($_POST['to_email_address']) : null;
        $to_name = isset($_POST['to_name']) ? trim($_POST['to_name']) : null;
        $message = isset($_POST['message']) ? trim($_POST['message']) : null;

        if ( empty($from_name) ) {
          $error = true;

          $OSCOM_MessageStack->addError('friend', ERROR_FROM_NAME);
        }

        if ( !osc_validate_email($from_email_address) ) {
          $error = true;

          $OSCOM_MessageStack->addError('friend', ERROR_FROM_ADDRESS);
        }

        if ( empty($to_name) ) {
          $error = true;

          $OSCOM_MessageStack->addError('friend', ERROR_TO_NAME);
        }

        if ( !osc_validate_email($to_email_address) ) {
          $error = true;

          $OSCOM_MessageStack->addError('friend', ERROR_TO_ADDRESS);
        }

        $actionRecorder = new actionRecorder('ar_tell_a_friend', ($OSCOM_Customer->isLoggedOn() ? $OSCOM_Customer->getID() : null), $from_name);

        if ( !$actionRecorder->canPerform() ) {
          $error = true;

          $actionRecorder->record(false);

          $OSCOM_MessageStack->addError('friend', sprintf(ERROR_ACTION_RECORDER, (defined('MODULE_ACTION_RECORDER_TELL_A_FRIEND_EMAIL_MINUTES') ? (int)MODULE_ACTION_RECORDER_TELL_A_FRIEND_EMAIL_MINUTES : 15)));
        }

        if ( $error === false ) {
          $email_subject = sprintf(TEXT_EMAIL_SUBJECT, $from_name, STORE_NAME);
          $email_body = sprintf(TEXT_EMAIL_INTRO, $to_name, $from_name, $Qp->value('products_name'), STORE_NAME) . "\n\n";

          if ( osc_not_null($message) ) {
            $email_body .= $message . "\n\n";
          }

          $product_link = osc_href_link('products', 'id=' . $_GET['id'], 'NONSSL', false);

          if ( strpos($product_link, '&amp;') !== false ) {
            $product_link = str_replace('&amp;', '&', $product_link);
          }

          $email_body .= sprintf(TEXT_EMAIL_LINK, $product_link) . "\n\n" .
                         sprintf(TEXT_EMAIL_SIGNATURE, STORE_NAME . "\n" . HTTP_SERVER . DIR_WS_CATALOG . "\n");

          osc_mail($to_name, $to_email_address, $email_subject, $email_body, $from_name, $from_email_address);

          $actionRecorder->record();

          $OSCOM_MessageStack->addSuccess('header', sprintf(TEXT_EMAIL_SUCCESSFUL_SENT, $Qp->value('products_name'), osc_output_string_protected($to_name)));

          osc_redirect(osc_href_link('products', 'id=' . $_GET['id']));
        }
      }
    }
  }
?>
