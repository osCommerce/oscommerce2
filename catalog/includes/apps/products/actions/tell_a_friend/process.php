<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_products_action_tell_a_friend_process {
    public static function execute(app $app) {
      global $Qp, $from_name, $from_email_address, $messageStack;

      if ( isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken']) ) {
        $error = false;

        $to_email_address = isset($_POST['to_email_address']) ? trim($_POST['to_email_address']) : null;
        $to_name = isset($_POST['to_name']) ? trim($_POST['to_name']) : null;
        $message = isset($_POST['message']) ? trim($_POST['message']) : null;

        if ( empty($from_name) ) {
          $error = true;

          $messageStack->add('friend', ERROR_FROM_NAME);
        }

        if ( !tep_validate_email($from_email_address) ) {
          $error = true;

          $messageStack->add('friend', ERROR_FROM_ADDRESS);
        }

        if ( empty($to_name) ) {
          $error = true;

          $messageStack->add('friend', ERROR_TO_NAME);
        }

        if ( !tep_validate_email($to_email_address) ) {
          $error = true;

          $messageStack->add('friend', ERROR_TO_ADDRESS);
        }

        $actionRecorder = new actionRecorder('ar_tell_a_friend', (isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : null), $from_name);

        if ( !$actionRecorder->canPerform() ) {
          $error = true;

          $actionRecorder->record(false);

          $messageStack->add('friend', sprintf(ERROR_ACTION_RECORDER, (defined('MODULE_ACTION_RECORDER_TELL_A_FRIEND_EMAIL_MINUTES') ? (int)MODULE_ACTION_RECORDER_TELL_A_FRIEND_EMAIL_MINUTES : 15)));
        }

        if ( $error === false ) {
          $email_subject = sprintf(TEXT_EMAIL_SUBJECT, $from_name, STORE_NAME);
          $email_body = sprintf(TEXT_EMAIL_INTRO, $to_name, $from_name, $Qp->value('products_name'), STORE_NAME) . "\n\n";

          if ( tep_not_null($message) ) {
            $email_body .= $message . "\n\n";
          }

          $product_link = tep_href_link('products', 'id=' . $_GET['id'], 'NONSSL', false);

          if ( strpos($product_link, '&amp;') !== false ) {
            $product_link = str_replace('&amp;', '&', $product_link);
          }

          $email_body .= sprintf(TEXT_EMAIL_LINK, $product_link) . "\n\n" .
                         sprintf(TEXT_EMAIL_SIGNATURE, STORE_NAME . "\n" . HTTP_SERVER . DIR_WS_CATALOG . "\n");

          tep_mail($to_name, $to_email_address, $email_subject, $email_body, $from_name, $from_email_address);

          $actionRecorder->record();

          $messageStack->add_session('header', sprintf(TEXT_EMAIL_SUCCESSFUL_SENT, $Qp->value('products_name'), tep_output_string_protected($to_name)), 'success');

          tep_redirect(tep_href_link('products', 'id=' . $_GET['id']));
        }
      }
    }
  }
?>
