<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

// if the customer is not logged on, redirect them to the login page
  if (!isset($_SESSION['customer_id'])) {
    $_SESSION['navigation']->set_snapshot();
    OSCOM::redirect('login.php');
  }

// if there is nothing in the customers cart, redirect them to the shopping cart page
  if ($_SESSION['cart']->count_contents() < 1) {
    OSCOM::redirect('shopping_cart.php');
  }

// if no shipping method has been selected, redirect the customer to the shipping method selection page
  if (!isset($_SESSION['shipping'])) {
    OSCOM::redirect('checkout_shipping.php');
  }

// avoid hack attempts during the checkout procedure by checking the internal cartID
  if (isset($_SESSION['cart']->cartID) && isset($_SESSION['cartID'])) {
    if ($_SESSION['cart']->cartID != $_SESSION['cartID']) {
      OSCOM::redirect('checkout_shipping.php');
    }
  }

// Stock Check
  if ( (STOCK_CHECK == 'true') && (STOCK_ALLOW_CHECKOUT != 'true') ) {
    $products = $_SESSION['cart']->get_products();
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      if (tep_check_stock($products[$i]['id'], $products[$i]['quantity'])) {
        OSCOM::redirect('shopping_cart.php');
        break;
      }
    }
  }

// if no billing destination address was selected, use the customers own address as default
  if (!isset($_SESSION['billto'])) {
    $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
  } else {
// verify the selected billing address
    if ( (is_array($_SESSION['billto']) && empty($_SESSION['billto'])) || is_numeric($_SESSION['billto']) ) {
      $Qcheck = $OSCOM_Db->prepare('select address_book_id from :table_address_book where address_book_id = :address_book_id and customers_id = :customers_id');
      $Qcheck->bindInt(':address_book_id', $_SESSION['billto']);
      $Qcheck->bindInt(':customers_id', $_SESSION['customer_id']);
      $Qcheck->execute();

      if ($Qcheck->fetch() === false) {
        $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
        if (isset($_SESSION['payment'])) unset($_SESSION['payment']);
      }
    }
  }

  require('includes/classes/order.php');
  $order = new order;

  if (isset($_POST['comments']) && tep_not_null($_POST['comments'])) {
    $_SESSION['comments'] = HTML::sanitize($_POST['comments']);
  }

  $total_weight = $_SESSION['cart']->show_weight();
  $total_count = $_SESSION['cart']->count_contents();

// load all enabled payment modules
  require('includes/classes/payment.php');
  $payment_modules = new payment;

  $OSCOM_Language->loadDefinitions('checkout_payment');

  $breadcrumb->add(OSCOM::getDef('navbar_title_1'), OSCOM::link('checkout_shipping.php'));
  $breadcrumb->add(OSCOM::getDef('navbar_title_2'), OSCOM::link('checkout_payment.php'));

  require($oscTemplate->getFile('template_top.php'));
?>

<?php echo $payment_modules->javascript_validation(); ?>

<div class="page-header">
  <h1><?php echo OSCOM::getDef('heading_title'); ?></h1>
</div>

<?php echo HTML::form('checkout_payment', OSCOM::link('checkout_confirmation.php'), 'post', 'class="form-horizontal" onsubmit="return check_form();"', ['tokenize' => true]); ?>

<div class="contentContainer">

<?php
  if (isset($_GET['payment_error']) && !empty($_GET['payment_error'])) {
    $pmsel = new payment($_GET['payment_error']);

    if ($error = $pmsel->get_error()) {
?>

  <div class="contentText">
    <?php echo '<strong>' . HTML::outputProtected($error['title']) . '</strong>'; ?>

    <p class="messageStackError"><?php echo HTML::outputProtected($error['error']); ?></p>
  </div>

<?php
    }
  }
?>

  <h2><?php echo OSCOM::getDef('table_heading_billing_address'); ?></h2>

  <div class="contentText row">
    <div class="col-sm-8">
      <div class="alert alert-warning">
        <?php echo OSCOM::getDef('text_selected_billing_destination'); ?>
        <div class="clearfix"></div>
        <div class="pull-right">
          <?php echo HTML::button(OSCOM::getDef('image_button_change_address'), 'fa fa-home', OSCOM::link('checkout_payment_address.php')); ?>
        </div>
        <div class="clearfix"></div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="panel panel-primary">
        <div class="panel-heading"><?php echo OSCOM::getDef('title_billing_address'); ?></div>
        <div class="panel-body">
          <?php echo tep_address_label($_SESSION['customer_id'], $_SESSION['billto'], true, ' ', '<br />'); ?>
        </div>
      </div>
    </div>
  </div>

  <div class="clearfix"></div>

  <h2><?php echo OSCOM::getDef('table_heading_payment_method'); ?></h2>

<?php
  $selection = $payment_modules->selection();

  if (sizeof($selection) > 1) {
?>

  <div class="contentText">
    <div class="alert alert-warning">
      <div class="row">
        <div class="col-xs-8">
          <?php echo OSCOM::getDef('text_select_payment_method'); ?>
        </div>
        <div class="col-xs-4 text-right">
          <?php echo '<strong>' . OSCOM::getDef('title_please_select') . '</strong>'; ?>
        </div>
      </div>
    </div>
  </div>


<?php
    } else {
?>

  <div class="contentText">
    <div class="alert alert-info"><?php echo OSCOM::getDef('text_enter_payment_information'); ?></div>
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
      <tr class="table-selection">
        <td><strong><?php echo $selection[$i]['module']; ?></strong></td>
        <td align="right">

<?php
    if (sizeof($selection) > 1) {
      echo HTML::radioField('payment', $selection[$i]['id'], (isset($_SESSION['payment']) && ($selection[$i]['id'] == $_SESSION['payment'])), 'required aria-required="true"');
    } else {
      echo HTML::hiddenField('payment', $selection[$i]['id']);
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
            <td><?php echo $selection[$i]['fields'][$j]['title']; ?></td>
            <td><?php echo $selection[$i]['fields'][$j]['field']; ?></td>
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
      <label for="inputComments" class="control-label col-sm-4"><?php echo OSCOM::getDef('table_heading_comments'); ?></label>
      <div class="col-sm-8">
        <?php
        echo HTML::textareaField('comments', 60, 5, (isset($_SESSION['comments']) ? $_SESSION['comments'] : ''), 'id="inputComments" placeholder="' . OSCOM::getDef('table_heading_comments') . '"');
        ?>
      </div>
    </div>
  </div>

  <div class="buttonSet">
    <div class="text-right"><?php echo HTML::button(OSCOM::getDef('image_button_continue'), 'fa fa-angle-right', null, null, 'btn-success'); ?></div>
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
          <button type="button" class="btn btn-primary btn-circle">2</button>
          <p><?php echo OSCOM::getDef('checkout_bar_payment'); ?></p>
        </div>
        <div class="stepwizard-step">
          <button type="button" class="btn btn-default btn-circle" disabled="disabled">3</button>
          <p><?php echo OSCOM::getDef('checkout_bar_confirmation'); ?></p>
        </div>
      </div>
    </div>
  </div>

</div>

</form>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
