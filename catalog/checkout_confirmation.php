<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

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

  if (isset($_POST['payment'])) $_SESSION['payment'] = $_POST['payment'];

  if (isset($_POST['comments']) && tep_not_null($_POST['comments'])) {
    $_SESSION['comments'] = HTML::sanitize($_POST['comments']);
  }

// load the selected payment module
  require('includes/classes/payment.php');
  $payment_modules = new payment($_SESSION['payment']);

  require('includes/classes/order.php');
  $order = new order;

  $payment_modules->update_status();

  if (strpos($payment_modules->selected_module, '\\') !== false) {
    $code = 'Payment_' . str_replace('\\', '_', $payment_modules->selected_module);

    if (Registry::exists($code)) {
      $OSCOM_PM = Registry::get($code);
    }
  } elseif (isset($_SESSION['payment']) && is_object($GLOBALS[$_SESSION['payment']])) {
    $OSCOM_PM = $GLOBALS[$_SESSION['payment']];
  }

  if ( !isset($OSCOM_PM) || ($payment_modules->selected_module != $_SESSION['payment']) || ($OSCOM_PM->enabled == false) ) {
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

  $breadcrumb->add(OSCOM::getDef('navbar_title_1'), OSCOM::link('checkout_shipping.php'));
  $breadcrumb->add(OSCOM::getDef('navbar_title_2'));

  require($oscTemplate->getFile('template_top.php'));
?>

<div class="page-header">
  <h1><?php echo OSCOM::getDef('heading_title'); ?></h1>
</div>

<?php
  if ($messageStack->size('checkout_confirmation') > 0) {
    echo $messageStack->output('checkout_confirmation');
  }

  if (isset($OSCOM_PM->form_action_url)) {
    $form_action_url = $OSCOM_PM->form_action_url;
  } else {
    $form_action_url = OSCOM::link('checkout_process.php');
  }

  echo HTML::form('checkout_confirmation', $form_action_url, 'post');
?>

<div class="contentContainer">
  <div class="contentText">

    <div class="panel panel-default">
      <div class="panel-heading"><?php echo '<strong>' . OSCOM::getDef('heading_products') . '</strong>' . HTML::button(OSCOM::getDef('text_edit'), 'fa fa-edit', OSCOM::link('shopping_cart.php'), null, 'pull-right btn-info btn-xs' ); ?></div>
      <div class="panel-body">
    <table width="100%" class="table-hover order_confirmation">
     <tbody>

<?php
  for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
    echo '          <tr>' . "\n" .
         '            <td align="right" valign="top" width="30">' . $order->products[$i]['qty'] . '&nbsp;x&nbsp;</td>' . "\n" .
         '            <td valign="top">' . $order->products[$i]['name'];

    if (STOCK_CHECK == 'true') {
      echo tep_check_stock($order->products[$i]['id'], $order->products[$i]['qty']);
    }

    if ( (isset($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0) ) {
      for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
        echo '<br /><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'] . '</i></small></nobr>';
      }
    }

    echo '</td>' . "\n";

    if (sizeof($order->info['tax_groups']) > 1) echo '            <td valign="top" align="right">' . tep_display_tax_value($order->products[$i]['tax']) . '%</td>' . "\n";

    echo '            <td align="right" valign="top">' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . '</td>' . "\n" .
         '          </tr>' . "\n";
  }
?>


        </tbody>
      </table>
      <hr>
      <table width="100%" class="pull-right">

<?php
  if (MODULE_ORDER_TOTAL_INSTALLED) {
    echo $order_total_modules->output();
  }
?>

        </table>
            </div>
    </div>



  </div>

  <div class="clearfix"></div>

  <div class="row">
    <?php
    if ($_SESSION['sendto'] != false) {
      ?>
      <div class="col-sm-4">
        <div class="panel panel-info">
          <div class="panel-heading"><?php echo '<strong>' . OSCOM::getDef('heading_delivery_address') . '</strong>' . HTML::button(OSCOM::getDef('text_edit'), 'fa fa-edit', OSCOM::link('checkout_shipping_address.php'), null, 'pull-right btn-info btn-xs' ); ?></div>
          <div class="panel-body">
            <?php echo tep_address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br />'); ?>
          </div>
        </div>
      </div>
      <?php
    }
    ?>
    <div class="col-sm-4">
      <div class="panel panel-warning">
        <div class="panel-heading"><?php echo '<strong>' . OSCOM::getDef('heading_billing_address') . '</strong>' . HTML::button(OSCOM::getDef('text_edit'), 'fa fa-edit', OSCOM::link('checkout_payment_address.php'), null, 'pull-right btn-info btn-xs' ); ?></div>
        <div class="panel-body">
          <?php echo tep_address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br />'); ?>
        </div>
      </div>
    </div>
    <div class="col-sm-4">
      <?php
      if ($order->info['shipping_method']) {
        ?>
        <div class="panel panel-info">
          <div class="panel-heading"><?php echo '<strong>' . OSCOM::getDef('heading_shipping_method') . '</strong>' . HTML::button(OSCOM::getDef('text_edit'), 'fa fa-edit', OSCOM::link('checkout_shipping.php'), null, 'pull-right btn-info btn-xs' ); ?></div>
          <div class="panel-body">
            <?php echo $order->info['shipping_method']; ?>
          </div>
        </div>
        <?php
      }
      ?>
      <div class="panel panel-warning">
        <div class="panel-heading"><?php echo '<strong>' . OSCOM::getDef('heading_payment_method') . '</strong>' . HTML::button(OSCOM::getDef('text_edit'), 'fa fa-edit', OSCOM::link('checkout_payment.php'), null, 'pull-right btn-info btn-xs' ); ?></div>
        <div class="panel-body">
          <?php echo $order->info['payment_method']; ?>
        </div>
      </div>
    </div>


  </div>


<?php
  if (is_array($payment_modules->modules)) {
    if ($confirmation = $payment_modules->confirmation()) {
      if (isset($confirmation['content'])) {
        echo '<div class="checkoutPaymentInput">' . $confirmation['content'] . '</div>';
      } else {
?>

  <hr>

  <h2><?php echo OSCOM::getDef('heading_payment_information'); ?></h2>

  <div class="contentText row">

<?php
        if (isset($confirmation['title'])) {
          echo '<div class="col-sm-6">';
          echo '  <div class="alert alert-danger">';
          echo $confirmation['title'];
          echo '  </div>';
          echo '</div>';
        }

        if (isset($confirmation['fields'])) {
          echo '<div class="col-sm-6">';
          echo '  <div class="alert alert-info">';
          for ($i=0, $n=sizeof($confirmation['fields']); $i<$n; $i++) {
            echo $confirmation['fields'][$i]['title'] . ' ' . $confirmation['fields'][$i]['field'];
          }
          echo '  </div>';
          echo '</div>';
        }
?>

  </div>

<?php
      }
?>

  <div class="clearfix"></div>

<?php
    }
  }

  if (tep_not_null($order->info['comments'])) {
?>
  <hr>

  <h2><?php echo '<strong>' . OSCOM::getDef('heading_order_comments') . '</strong> ' . HTML::button(OSCOM::getDef('text_edit'), 'fa fa-edit', OSCOM::link('checkout_payment.php'), null, 'pull-right btn-info btn-xs' ); ?></h2>

  <blockquote>
    <?php echo nl2br(HTML::outputProtected($order->info['comments'])) . HTML::hiddenField('comments', $order->info['comments']); ?>
  </blockquote>

<?php
  }
?>

  <div class="buttonSet">
    <div class="text-right">
      <?php
      if (is_array($payment_modules->modules)) {
        echo $payment_modules->process_button();
      }
      echo HTML::button(OSCOM::getDef('image_button_pay_total_now', ['total' => $currencies->format($order->info['total'], true, $order->info['currency'], $order->info['currency_value'])]), 'fa fa-ok', null, array('params' => 'data-button="payNow"'), 'btn-success');
      ?>
    </div>
  </div>

  <div class="clearfix"></div>

  <div class="contentText">
    <div class="stepwizard">
      <div class="stepwizard-row">
        <div class="stepwizard-step">
          <a href="<?php echo OSCOM::link('checkout_shipping.php'); ?>"><button type="button" class="btn btn-default btn-circle">1</button></a>
          <p><a href="<?php echo OSCOM::link('checkout_shipping.php'); ?>"><?php echo OSCOM::getDef('checkout_bar_delivery'); ?></a></p>
        </div>
        <div class="stepwizard-step">
          <a href="<?php echo OSCOM::link('checkout_payment.php'); ?>"><button type="button" class="btn btn-default btn-circle">2</button></a>
          <p><a href="<?php echo OSCOM::link('checkout_payment.php'); ?>"><?php echo OSCOM::getDef('checkout_bar_payment'); ?></a></p>
        </div>
        <div class="stepwizard-step">
          <button type="button" class="btn btn-primary btn-circle">3</button>
          <p><?php echo OSCOM::getDef('checkout_bar_confirmation'); ?></p>
        </div>
      </div>
    </div>
  </div>

</div>
<script>
$('form[name="checkout_confirmation"] button[data-button="payNow"]').data('orig-button-text', $('form[name="checkout_confirmation"] button[data-button="payNow"]').html());

$('form[name="checkout_confirmation"]').submit(function() {
  $('form[name="checkout_confirmation"] button[data-button="payNow"]').html('<?php echo addslashes(OSCOM::getDef('image_button_pay_total_processing')); ?>').prop('disabled', true);
});
</script>

</form>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
