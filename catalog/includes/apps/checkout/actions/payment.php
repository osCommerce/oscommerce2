<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_checkout_action_payment {
    public static function execute(app $app) {
      global $total_weight, $total_count, $payment_modules, $breadcrumb;

      $app->setContentFile('payment.php');

// Stock Check
      if ( (STOCK_CHECK == 'true') && (STOCK_ALLOW_CHECKOUT != 'true') ) {
        foreach ( $_SESSION['cart']->get_products() as $p ) {
          if ( tep_check_stock($p['id'], $p['quantity']) ) {
            tep_redirect(tep_href_link('cart'));
          }
        }
      }

      $total_weight = $_SESSION['cart']->show_weight();
      $total_count = $_SESSION['cart']->count_contents();

// load all enabled payment modules
      $payment_modules = new payment;

      $breadcrumb->add(NAVBAR_TITLE_PAYMENT, tep_href_link('checkout', 'payment', 'SSL'));
    }
  }
?>
