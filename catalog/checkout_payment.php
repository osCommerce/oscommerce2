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
  if (!isset($_SESSION['customer_id'])) {
    $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

// if there is nothing in the customers cart, redirect them to the shopping cart page
  if ($_SESSION['cart']->count_contents() < 1) {
    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
  }

// if no shipping method has been selected, redirect the customer to the shipping method selection page
  if (!isset($_SESSION['shipping'])) {
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  }

// avoid hack attempts during the checkout procedure by checking the internal cartID
  if (isset($_SESSION['cart']->cartID) && isset($_SESSION['cartID'])) {
    if ($_SESSION['cart']->cartID != $cartID) {
      tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    }
  }

// Stock Check
  if ( (STOCK_CHECK == 'true') && (STOCK_ALLOW_CHECKOUT != 'true') ) {
    $products = $_SESSION['cart']->get_products();
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      if (tep_check_stock($products[$i]['id'], $products[$i]['quantity'])) {
        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
        break;
      }
    }
  }

// if no billing destination address was selected, use the customers own address as default
  if (!isset($_SESSION['billto'])) {
    tep_session_register('billto');
    $billto = $customer_default_address_id;
  } else {
// verify the selected billing address
    if ( (is_array($billto) && empty($billto)) || is_numeric($billto) ) {
      $check_address_query = tep_db_query("select count(*) as total from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)$billto . "'");
      $check_address = tep_db_fetch_array($check_address_query);

      if ($check_address['total'] != '1') {
        $billto = $customer_default_address_id;
        if (isset($_SESSION['payment'])) unset($_SESSION['payment']);
      }
    }
  }

  require(DIR_WS_CLASSES . 'order.php');
  $order = new order;

  if (!isset($_SESSION['comments'])) tep_session_register('comments');
  if (isset($_POST['comments']) && tep_not_null($_POST['comments'])) {
    $comments = tep_db_prepare_input($_POST['comments']);
  }

  $total_weight = $_SESSION['cart']->show_weight();
  $total_count = $_SESSION['cart']->count_contents();

// load all enabled payment modules
  require(DIR_WS_CLASSES . 'payment.php');
  $payment_modules = new payment;

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . FILENAME_CHECKOUT_PAYMENT);

  $breadcrumb->add(NAVBAR_TITLE_1, tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<?php echo $payment_modules->javascript_validation(); ?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<?php echo tep_draw_form('checkout_payment', tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'), 'post', 'class="form-horizontal" role="form"', true); ?>

<div class="contentContainer">

<?php
  if (isset($_GET['payment_error']) && is_object(${$_GET['payment_error']}) && ($error = ${$_GET['payment_error']}->get_error())) {
?>

  <div class="contentText">
    <?php echo '<strong>' . tep_output_string_protected($error['title']) . '</strong>'; ?>

    <p class="messageStackError"><?php echo tep_output_string_protected($error['error']); ?></p>
  </div>

<?php
  }
?>

  <div class="page-header">
    <h4><?php echo TABLE_HEADING_BILLING_ADDRESS; ?></h4>
  </div>

  <div class="contentText row">
    <div class="col-sm-8">
      <div class="alert alert-warning">
        <?php echo TEXT_SELECTED_BILLING_DESTINATION; ?>
      </div>
      <div class="clearfix"></div>
      <?php echo tep_draw_button(IMAGE_BUTTON_CHANGE_ADDRESS, 'glyphicon glyphicon-home', tep_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL'), null, null, 'btn-default btn-block'); ?>
      <br>
      <div class="clearfix"></div>
    </div>
    <div class="col-sm-4">
      <div class="panel panel-primary">
        <div class="panel-heading"><?php echo TITLE_BILLING_ADDRESS; ?></div>

        <div class="panel-body">
          <?php echo tep_address_label($customer_id, $billto, true, ' ', '<br />'); ?>
        </div>
      </div>
    </div>
  </div>

  <div class="clearfix"></div>

  <div class="page-header">
    <h4><?php echo TABLE_HEADING_PAYMENT_METHOD; ?></h4>
  </div>

<?php
  $selection = $payment_modules->selection();

  if (sizeof($selection) > 1) {
?>

  <div class="contentText">
    <div class="alert alert-warning">
      <div class="pull-right">
        <?php echo '<strong>' . TITLE_PLEASE_SELECT . '</strong>'; ?>
      </div>

      <?php echo TEXT_SELECT_PAYMENT_METHOD; ?>
    </div>
  </div>

<?php
    } else {
?>

  <div class="contentText">
    <div class="alert alert-info"><?php echo TEXT_ENTER_PAYMENT_INFORMATION; ?></div>
  </div>

<?php
    }
?>

  <div class="contentText">

    <table class="table table-striped table-condensed table-hover">
      <tbody>
<?php
  $radio_buttons = 0;
  for ($i=0, $n=sizeof($selection); $i<$n; $i++) {
?>



<?php
    if ( ($selection[$i]['id'] == $payment) || ($n == 1) ) {
      echo '      <tr id="defaultSelected" class="moduleRowSelected">' . "\n";
    } else {
      echo '      <tr class="moduleRow">' . "\n";
    }
?>

        <td><strong><?php echo $selection[$i]['module']; ?></strong></td>
        <td align="right">

<?php
    if (sizeof($selection) > 1) {
      echo tep_draw_radio_field('payment', $selection[$i]['id'], ($selection[$i]['id'] == $payment));
    } else {
      echo tep_draw_hidden_field('payment', $selection[$i]['id']);
    }
?>

        </td>
      </tr>

<?php
    if (isset($selection[$i]['error'])) {
?>

      <tr>
        <td colspan="2"><?php echo $selection[$i]['error']; ?></td>
      </tr>

<?php
    } elseif (isset($selection[$i]['fields']) && is_array($selection[$i]['fields'])) {
?>

      <tr>
        <td colspan="2"><table border="0" cellspacing="0" cellpadding="2">

<?php
      for ($j=0, $n2=sizeof($selection[$i]['fields']); $j<$n2; $j++) {
?>

          <tr>
            <td>1<?php echo $selection[$i]['fields'][$j]['title']; ?></td>
            <td>2<?php echo $selection[$i]['fields'][$j]['field']; ?></td>
          </tr>

<?php
      }
?>

        </table></td>
      </tr>

<?php
    }
?>



<?php
    $radio_buttons++;
  }
?>
      </tbody>
    </table>

  </div>

  <hr>

  <div class="contentText">
    <div class="form-group">
      <label for="inputComments" class="control-label col-xs-4"><?php echo TABLE_HEADING_COMMENTS; ?></label>
      <div class="col-xs-8">
        <?php
        echo tep_draw_textarea_field('comments', 'soft', 60, 5, $comments, 'id="inputComments" placeholder="' . TABLE_HEADING_COMMENTS . '"');
        ?>
      </div>
    </div>
  </div>

  <div class="contentText">
    <div><?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'glyphicon glyphicon-chevron-right', null, 'primary', null, 'btn-success btn-block'); ?></div>
  </div>

  <div class="clearfix"></div>

  <div class="contentText">
    <div class="stepwizard">
      <div class="stepwizard-row">
        <div class="stepwizard-step">
          <a href="<?php echo tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'); ?>"><button type="button" class="btn btn-default btn-circle">1</button></a>
          <p><a href="<?php echo tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'); ?>"><?php echo CHECKOUT_BAR_DELIVERY; ?></a></p>
        </div>
        <div class="stepwizard-step">
          <button type="button" class="btn btn-primary btn-circle">2</button>
          <p><?php echo CHECKOUT_BAR_PAYMENT; ?></p>
        </div>
        <div class="stepwizard-step">
          <button type="button" class="btn btn-default btn-circle" disabled="disabled">3</button>
          <p><?php echo CHECKOUT_BAR_CONFIRMATION; ?></p>
        </div>
      </div>
    </div>
  </div>

</div>

</form>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
