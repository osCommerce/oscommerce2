<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_checkout_action_shipping {
    public static function execute(app $app) {
      global $OSCOM_Breadcrumb, $OSCOM_Customer, $OSCOM_PDO, $order, $total_weight, $total_count, $shipping_modules, $free_shipping, $quotes;

// if the order contains only virtual products, forward the customer to the checkout page as
// a shipping address is not needed
      if ( $_SESSION['cart']->get_content_type() == 'virtual' ) {
        osc_redirect(osc_href_link('checkout', null, 'SSL'));
      }

      $app->setContentFile('shipping.php');

      $OSCOM_Breadcrumb->add(NAVBAR_TITLE_SHIPPING, osc_href_link('checkout', 'shipping', 'SSL'));

      if ( $OSCOM_Customer->hasDefaultAddress() ) {
// register a random ID in the session to check throughout the checkout procedure
// against alterations in the shopping cart contents
        if ( isset($_SESSION['cartID']) && ($_SESSION['cartID'] != $_SESSION['cart']->cartID) && isset($_SESSION['shipping']) ) {
          unset($_SESSION['shipping']);
        }

        $_SESSION['cartID'] = $_SESSION['cart']->cartID = $_SESSION['cart']->generate_cart_id();

        $total_weight = $_SESSION['cart']->show_weight();
        $total_count = $_SESSION['cart']->count_contents();

// load all enabled shipping modules
        $shipping_modules = new shipping;

        if ( defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') ) {
          $pass = false;

          switch ( MODULE_ORDER_TOTAL_SHIPPING_DESTINATION ) {
            case 'national':
              if ( $order->delivery['country_id'] == STORE_COUNTRY ) {
                $pass = true;
              }
              break;

            case 'international':
              if ( $order->delivery['country_id'] != STORE_COUNTRY ) {
                $pass = true;
              }
              break;

            case 'both':
              $pass = true;
              break;
          }

          $free_shipping = false;

          if ( ($pass == true) && ($order->info['total'] >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) ) {
            $free_shipping = true;

            include(DIR_FS_CATALOG . DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/order_total/ot_shipping.php');
          }
        } else {
          $free_shipping = false;
        }

// get all available shipping quotes
        $quotes = $shipping_modules->quote();
      } else {
// No customer default address
        if ( !isset($_GET['address']) ) {
          osc_redirect(osc_href_link('checkout', 'shipping&address', 'SSL'));
        }
      }
    }
  }
?>
