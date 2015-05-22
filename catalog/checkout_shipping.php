<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');
  require('includes/classes/http_client.php');

// if the customer is not logged on, redirect them to the login page
  if (!isset($_SESSION['customer_id'])) {
    $_SESSION['navigation']->set_snapshot();
    tep_redirect(OSCOM::link('login.php', '', 'SSL'));
  }

// if there is nothing in the customers cart, redirect them to the shopping cart page
  if ($_SESSION['cart']->count_contents() < 1) {
    tep_redirect(OSCOM::link('shopping_cart.php'));
  }

// if no shipping destination address was selected, use the customers own address as default
  if (!isset($_SESSION['sendto'])) {
    $_SESSION['sendto'] = $_SESSION['customer_default_address_id'];
  } else {
// verify the selected shipping address
    if ( (is_array($_SESSION['sendto']) && empty($_SESSION['sendto'])) || is_numeric($_SESSION['sendto']) ) {
      $Qcheck = $OSCOM_Db->prepare('select address_book_id from :table_address_book where address_book_id = :address_book_id and customers_id = :customers_id');
      $Qcheck->bindInt(':address_book_id', $_SESSION['sendto']);
      $Qcheck->bindInt(':customers_id', $_SESSION['customer_id']);
      $Qcheck->execute();

      if ($Qcheck->fetch() === false) {
        $_SESSION['sendto'] = $_SESSION['customer_default_address_id'];
        if (isset($_SESSION['shipping'])) unset($_SESSION['shipping']);
      }
    }
  }

  require(DIR_WS_CLASSES . 'order.php');
  $order = new order;

// register a random ID in the session to check throughout the checkout procedure
// against alterations in the shopping cart contents
  if (isset($_SESSION['cartID']) && ($_SESSION['cartID'] != $_SESSION['cart']->cartID) && isset($_SESSION['shipping'])) {
    unset($_SESSION['shipping']);
  }

  $_SESSION['cartID'] = $_SESSION['cart']->cartID = $_SESSION['cart']->generate_cart_id();

// if the order contains only virtual products, forward the customer to the billing page as
// a shipping address is not needed
  if ($order->content_type == 'virtual') {
    $_SESSION['shipping'] = false;
    $_SESSION['sendto'] = false;
    tep_redirect(OSCOM::link('checkout_payment.php', '', 'SSL'));
  }

  $total_weight = $_SESSION['cart']->show_weight();
  $total_count = $_SESSION['cart']->count_contents();

// load all enabled shipping modules
  require(DIR_WS_CLASSES . 'shipping.php');
  $shipping_modules = new shipping;

  if ( defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') ) {
    $pass = false;

    switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
      case 'national':
        if ($order->delivery['country_id'] == STORE_COUNTRY) {
          $pass = true;
        }
        break;
      case 'international':
        if ($order->delivery['country_id'] != STORE_COUNTRY) {
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

      include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/order_total/ot_shipping.php');
    }
  } else {
    $free_shipping = false;
  }

// process the selected shipping method
  if ( isset($_POST['action']) && ($_POST['action'] == 'process') && isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken']) ) {
    if (tep_not_null($_POST['comments'])) {
      $_SESSION['comments'] = HTML::sanitize($_POST['comments']);
    }

    if ( (tep_count_shipping_modules() > 0) || ($free_shipping == true) ) {
      if ( (isset($_POST['shipping'])) && (strpos($_POST['shipping'], '_')) ) {
        $_SESSION['shipping'] = $_POST['shipping'];

        list($module, $method) = explode('_', $_SESSION['shipping']);
        if ( is_object($$module) || ($_SESSION['shipping'] == 'free_free') ) {
          if ($_SESSION['shipping'] == 'free_free') {
            $quote[0]['methods'][0]['title'] = FREE_SHIPPING_TITLE;
            $quote[0]['methods'][0]['cost'] = '0';
          } else {
            $quote = $shipping_modules->quote($method, $module);
          }
          if (isset($quote['error'])) {
            unset($_SESSION['shipping']);
          } else {
            if ( (isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost'])) ) {
              $_SESSION['shipping'] = array('id' => $_SESSION['shipping'],
                                            'title' => (($free_shipping == true) ?  $quote[0]['methods'][0]['title'] : $quote[0]['module'] . ' (' . $quote[0]['methods'][0]['title'] . ')'),
                                            'cost' => $quote[0]['methods'][0]['cost']);

              tep_redirect(OSCOM::link('checkout_payment.php', '', 'SSL'));
            }
          }
        } else {
          unset($_SESSION['shipping']);
        }
      }
    } else {
      if ( defined('SHIPPING_ALLOW_UNDEFINED_ZONES') && (SHIPPING_ALLOW_UNDEFINED_ZONES == 'False') ) {
        unset($_SESSION['shipping']);
      } else {
        $_SESSION['shipping'] = false;

        tep_redirect(OSCOM::link('checkout_payment.php', '', 'SSL'));
      }
    }
  }

// get all available shipping quotes
  $quotes = $shipping_modules->quote();

// if no shipping method has been selected, automatically select the cheapest method.
// if the modules status was changed when none were available, to save on implementing
// a javascript force-selection method, also automatically select the cheapest shipping
// method if more than one module is now enabled
  if ( !isset($_SESSION['shipping']) || ( isset($_SESSION['shipping']) && ($_SESSION['shipping'] === false) && (tep_count_shipping_modules() > 1) ) ) $_SESSION['shipping'] = $shipping_modules->cheapest();

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/checkout_shipping.php');

  if ( defined('SHIPPING_ALLOW_UNDEFINED_ZONES') && (SHIPPING_ALLOW_UNDEFINED_ZONES == 'False') && (!isset($_SESSION['shipping']) || ($_SESSION['shipping'] === false)) ) {
    $messageStack->add_session('checkout_address', ERROR_NO_SHIPPING_AVAILABLE_TO_SHIPPING_ADDRESS);

    tep_redirect(OSCOM::link('checkout_shipping_address.php', '', 'SSL'));
  }

  $breadcrumb->add(NAVBAR_TITLE_1, OSCOM::link('checkout_shipping.php', '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2, OSCOM::link('checkout_shipping.php', '', 'SSL'));

  require('includes/template_top.php');
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<?php echo tep_draw_form('checkout_address', OSCOM::link('checkout_shipping.php', '', 'SSL'), 'post', 'class="form-horizontal" role="form"', true) . tep_draw_hidden_field('action', 'process'); ?>

<div class="contentContainer">
  <div class="page-header">
    <h4><?php echo TABLE_HEADING_SHIPPING_ADDRESS; ?></h4>
  </div>

  <div class="contentText row">
    <div class="col-sm-8">
      <div class="alert alert-warning">
        <?php echo TEXT_CHOOSE_SHIPPING_DESTINATION; ?>
      </div>
      <div class="clearfix"></div>
      <?php echo tep_draw_button(IMAGE_BUTTON_CHANGE_ADDRESS, 'glyphicon glyphicon-home', OSCOM::link('checkout_shipping_address.php', '', 'SSL'), null, null, 'btn-default btn-block'); ?>
      <br>
      <div class="clearfix"></div>
    </div>
    <div class="col-sm-4">
      <div class="panel panel-primary">
        <div class="panel-heading"><?php echo TITLE_SHIPPING_ADDRESS; ?></div>

        <div class="panel-body">
          <?php echo tep_address_label($_SESSION['customer_id'], $_SESSION['sendto'], true, ' ', '<br />'); ?>
        </div>
      </div>
    </div>
  </div>

  <div class="clearfix"></div>

<?php
  if (tep_count_shipping_modules() > 0) {
?>

  <div class="page-header">
    <h4><?php echo TABLE_HEADING_SHIPPING_METHOD; ?></h4>
  </div>

<?php
    if (sizeof($quotes) > 1 && sizeof($quotes[0]) > 1) {
?>

  <div class="contentText">
    <div class="alert alert-warning">
      <div class="pull-right">
        <?php echo '<strong>' . TITLE_PLEASE_SELECT . '</strong>'; ?>
      </div>

      <?php echo TEXT_CHOOSE_SHIPPING_METHOD; ?>
    </div>
  </div>

<?php
    } elseif ($free_shipping == false) {
?>

  <div class="contentText">
    <div class="alert alert-info"><?php echo TEXT_ENTER_SHIPPING_INFORMATION; ?></div>
  </div>

<?php
    }
?>

  <div class="contentText">
<?php
    if ($free_shipping == true) {
?>
      <div class="contentText">
        <div class="panel panel-success">
          <div class="panel-heading"><strong><?php echo FREE_SHIPPING_TITLE; ?></strong>&nbsp;<?php echo $quotes[$i]['icon']; ?></div>
          <div class="panel-body">
            <?php echo sprintf(FREE_SHIPPING_DESCRIPTION, $currencies->format(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) . tep_draw_hidden_field('shipping', 'free_free'); ?>
          </div>
        </div>
      </div>

<?php
  } else {
 ?>
    <table class="table table-striped table-condensed table-hover">
      <tbody>
 <?php
      for ($i=0, $n=sizeof($quotes); $i<$n; $i++) {
        for ($j=0, $n2=sizeof($quotes[$i]['methods']); $j<$n2; $j++) {
?>
        <tr>
        <td>
          <strong><?php echo $quotes[$i]['module']; ?></strong>
          <?php
          if (isset($quotes[$i]['icon']) && tep_not_null($quotes[$i]['icon'])) echo '&nbsp;' . $quotes[$i]['icon'];

          if (isset($quotes[$i]['error'])) {
            echo '<div class="help-block">' . $quotes[$i]['error'] . '</div>';
          }

          if (tep_not_null($quotes[$i]['methods'][$j]['title'])) echo '<div class="help-block">' . $quotes[$i]['methods'][$j]['title'] . '</div>';
          ?>
          </td>

<?php
            if ( ($n > 1) || ($n2 > 1) ) {
?>

        <td align="right">
          <?php
          if (isset($quotes[$i]['error'])) {
            // nothing
            echo '&nbsp;';
          }
          else {
            echo $currencies->format(tep_add_tax($quotes[$i]['methods'][$j]['cost'], (isset($quotes[$i]['tax']) ? $quotes[$i]['tax'] : 0))); ?>&nbsp;&nbsp;<?php echo HTML::radioField('shipping', $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id'], null, 'required aria-required="true"');
          }
          ?>
        </td>

<?php
            } else {
?>

        <td align="right"><?php echo $currencies->format(tep_add_tax($quotes[$i]['methods'][$j]['cost'], (isset($quotes[$i]['tax']) ? $quotes[$i]['tax'] : 0))) . tep_draw_hidden_field('shipping', $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id']); ?></td>

<?php
            }
?>

        </tr>
<?php
     }
   }
?>

      </tbody>
    </table>
<?php

    }
?>


  </div>

<?php
  }
?>

  <hr>

  <div class="contentText">
    <div class="form-group">
      <label for="inputComments" class="control-label col-sm-4"><?php echo TABLE_HEADING_COMMENTS; ?></label>
      <div class="col-sm-8">
        <?php
        echo HTML::textareaField('comments', 60, 5, (isset($_SESSION['comments']) ? $_SESSION['comments'] : ''), 'id="inputComments" placeholder="' . ENTRY_COMMENTS_TEXT . '"');
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
          <button type="button" class="btn btn-primary btn-circle">1</button>
          <p><?php echo CHECKOUT_BAR_DELIVERY; ?></p>
        </div>
        <div class="stepwizard-step">
          <button type="button" class="btn btn-default btn-circle" disabled="disabled">2</button>
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
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
