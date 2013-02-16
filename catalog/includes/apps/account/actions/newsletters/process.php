<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_newsletters_process {
    public static function execute(app $app) {
      global $OSCOM_PDO, $newsletter, $messageStack;

      if ( isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken']) ) {
        if ( isset($_POST['newsletter_general']) && is_numeric($_POST['newsletter_general']) ) {
          $newsletter_general = trim($_POST['newsletter_general']);
        } else {
          $newsletter_general = '0';
        }

        if ( $newsletter_general != $newsletter['customers_newsletter'] ) {
          $newsletter_general = (($newsletter['customers_newsletter'] == '1') ? '0' : '1');

          $OSCOM_PDO->perform('customers', array('customers_newsletter' => (int)$newsletter_general), array('customers_id' => (int)$_SESSION['customer_id']));
        }

        $messageStack->add_session('account', SUCCESS_NEWSLETTER_UPDATED, 'success');

        tep_redirect(tep_href_link('account', '', 'SSL'));
      }
    }
  }
?>
