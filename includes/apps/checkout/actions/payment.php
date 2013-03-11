<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_checkout_action_payment {
    public static function execute(app $app) {
      global $OSCOM_Breadcrumb, $OSCOM_MessageStack, $total_weight, $total_count, $payment_modules;

      $app->setContentFile('payment.php');

      $total_weight = $_SESSION['cart']->show_weight();
      $total_count = $_SESSION['cart']->count_contents();

// load all enabled payment modules
      $payment_modules = new payment;

      if ( isset($_GET['payment_error']) && is_object($GLOBALS[$_GET['payment_error']]) && ($error = $GLOBALS[$_GET['payment_error']]->get_error()) ) {
        $OSCOM_MessageStack->addError('payment_error', '<strong>' . osc_output_string_protected($error['title']) . '</strong><br />' . osc_output_string_protected($error['error']));
      }

      $OSCOM_Breadcrumb->add(NAVBAR_TITLE_PAYMENT, osc_href_link('checkout', 'payment', 'SSL'));
    }
  }
?>
