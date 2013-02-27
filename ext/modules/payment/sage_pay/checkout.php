<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../');
  require('includes/application_top.php');

// if the customer is not logged on, redirect them to the login page
  if (!isset($_SESSION['customer_id'])) {
    $OSCOM_NavigationHistory->setSnapshot(array('application' => 'checkout', 'action' => 'payment', 'mode' => 'SSL'));

    osc_redirect(osc_href_link('account', 'login', 'SSL'));
  }

// if there is nothing in the customers cart, redirect them to the shopping cart page
  if ($_SESSION['cart']->count_contents() < 1) {
    osc_redirect(osc_href_link('cart'));
  }

// avoid hack attempts during the checkout procedure by checking the internal cartID
  if (isset($_SESSION['cart']->cartID) && isset($_SESSION['cartID'])) {
    if ($_SESSION['cart']->cartID != $_SESSION['cartID']) {
      osc_redirect(osc_href_link('checkout', 'shipping', 'SSL'));
    }
  }

// if no shipping method has been selected, redirect the customer to the shipping method selection page
  if (!isset($_SESSION['shipping'])) {
    osc_redirect(osc_href_link('checkout', 'shipping', 'SSL'));
  }

  if (!isset($_SESSION['payment']) || (($_SESSION['payment'] != 'sage_pay_direct') && ($_SESSION['payment'] != 'sage_pay_server'))) {
    osc_redirect(osc_href_link('checkout', 'payment', 'SSL'));
  }

// load the selected payment module
  require(DIR_WS_CLASSES . 'payment.php');
  $payment_modules = new payment($_SESSION['payment']);

  require(DIR_WS_CLASSES . 'order.php');
  $order = new order;

  $payment_modules->update_status();

  if ( ( is_array($payment_modules->modules) && (sizeof($payment_modules->modules) > 1) && !is_object($$_SESSION['payment']) ) || (is_object($$_SESSION['payment']) && ($$_SESSION['payment']->enabled == false)) ) {
    osc_redirect(osc_href_link('checkout', 'payment&error_message=' . urlencode(ERROR_NO_PAYMENT_MODULE_SELECTED), 'SSL'));
  }

  if (is_array($payment_modules->modules)) {
    $payment_modules->pre_confirmation_check();
  }

// load the selected shipping module
  require(DIR_WS_CLASSES . 'shipping.php');
  $shipping_modules = new shipping($_SESSION['shipping']);

  require(DIR_WS_CLASSES . 'order_total.php');
  $order_total_modules = new order_total;
  $order_total_modules->process();

// Stock Check
  $any_out_of_stock = false;
  if (STOCK_CHECK == 'true') {
    for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
      if (osc_check_stock($order->products[$i]['id'], $order->products[$i]['qty'])) {
        $any_out_of_stock = true;
      }
    }
    // Out of Stock
    if ( (STOCK_ALLOW_CHECKOUT != 'true') && ($any_out_of_stock == true) ) {
      osc_redirect(osc_href_link('cart'));
    }
  }

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/checkout.php');

  $OSCOM_Breadcrumb->add(NAVBAR_TITLE_1, osc_href_link('checkout', 'shipping', 'SSL'));
  $OSCOM_Breadcrumb->add(NAVBAR_TITLE_2);

  if ($_SESSION['payment'] == 'sage_pay_direct') {
    $iframe_url = osc_href_link('ext/modules/payment/sage_pay/direct_3dauth.php', '', 'SSL');
  } else {
    $iframe_url = $_SESSION['sage_pay_server_nexturl'];
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

    <iframe src="<?php echo $iframe_url; ?>" width="100%" height="600" frameborder="0">
      <p>Your browser does not support iframes.</p>
    </iframe>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
