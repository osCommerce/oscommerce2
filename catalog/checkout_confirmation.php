<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

// if the customer is not logged on, redirect them to the login page
  if (!tep_session_is_registered('customer_id')) {
    $navigation->set_snapshot(array('mode' => 'SSL', 'page' => FILENAME_CHECKOUT_PAYMENT));
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

// if there is nothing in the customers cart, redirect them to the shopping cart page
  if ($cart->count_contents() < 1) {
    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
  }

// avoid hack attempts during the checkout procedure by checking the internal cartID
  if (isset($cart->cartID) && tep_session_is_registered('cartID')) {
    if ($cart->cartID != $cartID) {
      tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    }
  }

// if no shipping method has been selected, redirect the customer to the shipping method selection page
  if (!tep_session_is_registered('shipping')) {
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  }

  if (!tep_session_is_registered('payment')) tep_session_register('payment');
  if (isset($HTTP_POST_VARS['payment'])) $payment = $HTTP_POST_VARS['payment'];

  if (!tep_session_is_registered('comments')) tep_session_register('comments');
  if (isset($HTTP_POST_VARS['comments']) && tep_not_null($HTTP_POST_VARS['comments'])) {
    $comments = tep_db_prepare_input($HTTP_POST_VARS['comments']);
  }

// load the selected payment module
  require(DIR_WS_CLASSES . 'payment.php');
  $payment_modules = new payment($payment);

  require(DIR_WS_CLASSES . 'order.php');
  $order = new order;

  $payment_modules->update_status();

  if ( ($payment_modules->selected_module != $payment) || ( is_array($payment_modules->modules) && (sizeof($payment_modules->modules) > 1) && !is_object($$payment) ) || (is_object($$payment) && ($$payment->enabled == false)) ) {
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_NO_PAYMENT_MODULE_SELECTED), 'SSL'));
  }

  if (is_array($payment_modules->modules)) {
    $payment_modules->pre_confirmation_check();
  }

// load the selected shipping module
  require(DIR_WS_CLASSES . 'shipping.php');
  $shipping_modules = new shipping($shipping);

  require(DIR_WS_CLASSES . 'order_total.php');
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
      tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
    }
  }

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CHECKOUT_CONFIRMATION);

  $breadcrumb->add(NAVBAR_TITLE_1, tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2);

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<?php
  if ($messageStack->size('checkout_confirmation') > 0) {
    echo $messageStack->output('checkout_confirmation');
  }

  if (isset($$payment->form_action_url)) {
    $form_action_url = $$payment->form_action_url;
  } else {
    $form_action_url = tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');
  }

  echo tep_draw_form('checkout_confirmation', $form_action_url, 'post');
?>

<div class="contentContainer">

  <table class="table table-striped">
    <thead>
      <tr>
      <?php
      if (sizeof($order->info['tax_groups']) > 1) {
        ?>
        <th colspan="2"><?php echo '<strong>' . HEADING_PRODUCTS . '</strong> ' . tep_draw_button(TEXT_EDIT, 'glyphicon glyphicon-edit', tep_href_link(FILENAME_SHOPPING_CART), NULL, NULL, 'pull-right btn-default btn-xs' ); ?></th>
        <th align="right"><strong><?php echo HEADING_TAX; ?></strong></th>
        <th align="right"><strong><?php echo HEADING_TOTAL; ?></strong></th>
        <?php
      }
      else {
        ?>
        <th colspan="3"><?php echo '<strong>' . HEADING_PRODUCTS . '</strong> ' . tep_draw_button(TEXT_EDIT, 'glyphicon glyphicon-edit', tep_href_link(FILENAME_SHOPPING_CART), NULL, NULL, 'pull-right btn-default btn-xs' ); ?></th>
        <?php
      }
      ?>
      </tr>
    <thead>
    <tbody>
    <?php
    for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
      echo '          <tr>' . "\n" .
           '            <td align="right" valign="top" width="30">' . $order->products[$i]['qty'] . '&nbsp;x</td>' . "\n" .
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
  <table class="pull-right">
    <?php
    if (MODULE_ORDER_TOTAL_INSTALLED) {
      echo $order_total_modules->output();
    }
    ?>
  </table>
  
  <div class="clearfix"></div>
        
  <div class="page-header">
    <h4><?php echo HEADING_SHIPPING_INFORMATION; ?></h4>
  </div>

  <div class="contentText">
    <div class="row">
      <div class="col-sm-4">
        <div class="panel panel-primary">
          <div class="panel-heading"><?php echo '<strong>' . HEADING_DELIVERY_ADDRESS . '</strong>' . tep_draw_button(TEXT_EDIT, 'glyphicon glyphicon-edit', tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'), NULL, NULL, 'pull-right btn-default btn-xs' ); ?></div>

          <div class="panel-body">
            <?php echo tep_address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br />'); ?>
          </div>
        </div>
      </div>
      <?php
      if ($order->info['shipping_method']) {
        ?>
        <div class="col-sm-4">
          <div class="panel panel-info">
            <div class="panel-heading"><?php echo '<strong>' . HEADING_SHIPPING_METHOD . '</strong>' . tep_draw_button(TEXT_EDIT, 'glyphicon glyphicon-edit', tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'), NULL, NULL, 'pull-right btn-default btn-xs' ); ?></div>

            <div class="panel-body">
              <?php echo $order->info['shipping_method']; ?>
            </div>
          </div>
        </div>
        <?php
      }
      ?>
        
    </div>
    
    <div class="clearfix"></div>
  </div>

  <div class="page-header">
    <h4><?php echo HEADING_BILLING_INFORMATION; ?></h4>
  </div>

  <div class="contentText">
  
    <div class="row">

      <?php
      if (is_array($payment_modules->modules)) {
        if ($confirmation = $payment_modules->confirmation()) {
          ?>
          <div class="col-sm-4 pull-right">
            <div class="panel panel-default">
              <div class="panel-heading"><?php echo HEADING_PAYMENT_INFORMATION; ?></div>

              <div class="panel-body">
                <table class="table table-striped table-condensed">
                  <thead>
                    <tr>
                      <td colspan="4"><?php echo $confirmation['title']; ?></td>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                  if (isset($confirmation['fields'])) {
                    for ($i=0, $n=sizeof($confirmation['fields']); $i<$n; $i++) {
                      ?>

                      <tr>
                        <td class="main"><?php echo $confirmation['fields'][$i]['title']; ?></td>
                        <td class="main"><?php echo $confirmation['fields'][$i]['field']; ?></td>
                      </tr>

                      <?php
                    }
                  }
                  ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <?php
        }
      }
      ?>

      <div class="col-sm-4">
        <div class="panel panel-primary">
          <div class="panel-heading"><?php echo '<strong>' . HEADING_BILLING_ADDRESS . '</strong>' . tep_draw_button(TEXT_EDIT, 'glyphicon glyphicon-edit', tep_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL'), NULL, NULL, 'pull-right btn-default btn-xs' ); ?></div>

          <div class="panel-body">
            <?php echo tep_address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br />'); ?>
          </div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="panel panel-info">
          <div class="panel-heading"><?php echo '<strong>' . HEADING_PAYMENT_METHOD . '</strong>' . tep_draw_button(TEXT_EDIT, 'glyphicon glyphicon-edit', tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'), NULL, NULL, 'pull-right btn-default btn-xs' ); ?></div>

          <div class="panel-body">
            <?php echo $order->info['payment_method']; ?>
          </div>
        </div>
      </div>
    </div>
    
    <div class="clearfix"></div>
    
  </div>

<?php
  if (tep_not_null($order->info['comments'])) {
?>

  <div class="page-header">
    <h4><?php echo '<strong>' . HEADING_ORDER_COMMENTS . '</strong> ' . tep_draw_button(TEXT_EDIT, 'glyphicon glyphicon-edit', tep_href_link(FILENAME_CHECKOUT_PAYMENT), NULL, NULL, 'pull-right btn-default btn-xs' ); ?></h4>
  </div>

  <div class="contentText">
    <blockquote>
      <small><?php echo nl2br(tep_output_string_protected($order->info['comments'])) . tep_draw_hidden_field('comments', $order->info['comments']); ?></small>
    </blockquote>
  </div>

<?php
  }
?>

  <div class="contentText">
    <div style="float: left; width: 60%; padding-top: 5px; padding-left: 15%;">
      <div id="coProgressBar" style="height: 5px;"></div>

      <table border="0" width="100%" cellspacing="0" cellpadding="2">
        <tr>
          <td align="center" width="33%" class="checkoutBarFrom"><?php echo '<a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL') . '" class="checkoutBarFrom">' . CHECKOUT_BAR_DELIVERY . '</a>'; ?></td>
          <td align="center" width="33%" class="checkoutBarFrom"><?php echo '<a href="' . tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL') . '" class="checkoutBarFrom">' . CHECKOUT_BAR_PAYMENT . '</a>'; ?></td>
          <td align="center" width="33%" class="checkoutBarCurrent"><?php echo CHECKOUT_BAR_CONFIRMATION; ?></td>
        </tr>
      </table>
    </div>

    <div>

<?php
  if (is_array($payment_modules->modules)) {
    echo $payment_modules->process_button();
  }

  echo tep_draw_button(IMAGE_BUTTON_CONFIRM_ORDER, 'glyphicon glyphicon-ok', null, 'primary', null, 'btn-success btn-block');
?>

    </div>
  </div>

</div>

<script type="text/javascript">
$('#coProgressBar').progressbar({
  value: 100
});
</script>

</form>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
