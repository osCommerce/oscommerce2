<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_newsletters_process {
    public static function execute(app $app) {
      global $OSCOM_Customer, $OSCOM_MessageStack, $OSCOM_PDO, $newsletter;

      if ( isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken']) ) {
        if ( isset($_POST['newsletter_general']) && is_numeric($_POST['newsletter_general']) ) {
          $newsletter_general = trim($_POST['newsletter_general']);
        } else {
          $newsletter_general = '0';
        }

        if ( $newsletter_general != $newsletter['customers_newsletter'] ) {
          $newsletter_general = (($newsletter['customers_newsletter'] == '1') ? '0' : '1');

          $OSCOM_PDO->perform('customers', array('customers_newsletter' => (int)$newsletter_general), array('customers_id' => (int)$OSCOM_Customer->getID()));
        }

        $OSCOM_MessageStack->addSuccess('account', SUCCESS_NEWSLETTER_UPDATED);

        osc_redirect(osc_href_link('account', '', 'SSL'));
      }
    }
  }
?>
