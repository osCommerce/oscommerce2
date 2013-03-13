<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_checkout_action_payment_process {
    public static function execute(app $app) {
      if ( isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken']) ) {
        if ( osc_not_null($_POST['comments']) ) {
          $_SESSION['comments'] = trim($_POST['comments']);
        }

        if ( isset($_POST['payment']) ) {
          $_SESSION['payment'] = $_POST['payment'];
        }
      }

      osc_redirect(osc_href_link('checkout', '', 'SSL'));
    }
  }
?>
