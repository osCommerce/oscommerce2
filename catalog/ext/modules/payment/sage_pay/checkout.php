<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTTP;
  use OSC\OM\OSCOM;

  chdir('../../../../');
  require('includes/application_top.php');

// if the customer is not logged on, redirect them to the login page
  if (!isset($_SESSION['customer_id'])) {
    $_SESSION['navigation']->set_snapshot(array('page' => 'checkout_payment.php'));
    OSCOM::redirect('login.php');
  }

// if there is nothing in the customers cart, redirect them to the shopping cart page
  if ($_SESSION['cart']->count_contents() < 1) {
    OSCOM::redirect('shopping_cart.php');
  }

// avoid hack attempts during the checkout procedure by checking the internal cartID
  if (isset($_SESSION['cart']->cartID) && isset($_SESSION['cartID'])) {
    if ($_SESSION['cart']->cartID != $_SESSION['cartID']) {
      OSCOM::redirect('checkout_shipping.php');
    }
  }

// if no shipping method has been selected, redirect the customer to the shipping method selection page
  if (!isset($_SESSION['shipping'])) {
    OSCOM::redirect('checkout_shipping.php');
  }

  if (!isset($_SESSION['payment']) || (($_SESSION['payment'] != 'sage_pay_direct') && ($_SESSION['payment'] != 'sage_pay_server')) || (($_SESSION['payment'] == 'sage_pay_server') && !isset($_SESSION['sage_pay_server_nexturl']))) {
    OSCOM::redirect('checkout_payment.php');
  }

// load the selected payment module
  require('includes/classes/payment.php');
  $payment_modules = new payment($_SESSION['payment']);

  require('includes/classes/order.php');
  $order = new order;

  $payment_modules->update_status();

  if ( ( is_array($payment_modules->modules) && (sizeof($payment_modules->modules) > 1) && !is_object($GLOBALS[$_SESSION['payment']]) ) || (is_object($GLOBALS[$_SESSION['payment']]) && ($GLOBALS[$_SESSION['payment']]->enabled == false)) ) {
    OSCOM::redirect('checkout_payment.php', 'error_message=' . urlencode(OSCOM::getDef('error_no_payment_module_selected')));
  }

  if (is_array($payment_modules->modules)) {
    $payment_modules->pre_confirmation_check();
  }

// load the selected shipping module
  require('includes/classes/shipping.php');
  $shipping_modules = new shipping($_SESSION['shipping']);

  require('includes/classes/order_total.php');
  $order_total_modules = new order_total;
  $order_total_modules->process();

// Stock Check
  $any_out_of_stock = false;
  if (STOCK_CHECK == 'true') {
    for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
      if (tep_check_stock($order->products[$i]['id'], $order->products[$i]['qty'])) {
        $any_out_of_stock = true;
      }
    }
    // Out of Stock
    if ( (STOCK_ALLOW_CHECKOUT != 'true') && ($any_out_of_stock == true) ) {
      OSCOM::redirect('shopping_cart.php');
    }
  }

  $OSCOM_Language->loadDefinitions('checkout_confirmation');

  $breadcrumb->add(NAVBAR_TITLE_1, OSCOM::link('checkout_shipping.php'));
  $breadcrumb->add(NAVBAR_TITLE_2);

  if ($_SESSION['payment'] == 'sage_pay_direct') {
    $iframe_url = OSCOM::link('ext/modules/payment/sage_pay/direct_3dauth.php');
  } else {
    $iframe_url = $_SESSION['sage_pay_server_nexturl'];
  }

  if ( !is_file($oscTemplate->getFile('template_top.php')) ) {
    HTTP::redirect($iframe_url);
  }

  require($oscTemplate->getFile('template_top.php'));
?>

    <iframe src="<?php echo $iframe_url; ?>" width="100%" height="600" frameborder="0">
      <p>Your browser does not support iframes.</p>
    </iframe>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
