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
  use OSC\OM\Registry;

  require("includes/application_top.php");

  if ($_SESSION['cart']->count_contents() > 0) {
    include(DIR_WS_CLASSES . 'payment.php');
    $payment_modules = new payment;
  }

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/shopping_cart.php');

  $breadcrumb->add(NAVBAR_TITLE, OSCOM::link('shopping_cart.php'));

  require('includes/template_top.php');
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<?php
  if ($messageStack->size('product_action') > 0) {
    echo $messageStack->output('product_action');
  }
?>

<?php
  if ($_SESSION['cart']->count_contents() > 0) {
?>

<?php echo HTML::form('cart_quantity', OSCOM::link('shopping_cart.php', 'action=update_product')); ?>

<div class="contentContainer">

  <div class="contentText">

<?php
    $any_out_of_stock = 0;
    $products = $_SESSION['cart']->get_products();
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
// Push all attributes information in an array
      if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
        foreach($products[$i]['attributes'] as $option => $value) {
          echo HTML::hiddenField('id[' . $products[$i]['id'] . '][' . $option . ']', $value);
          $Qattributes = $OSCOM_Db->prepare('select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix
                                             from :table_products_options popt, :table_products_options_values poval, :table_products_attributes pa
                                             where pa.products_id = :products_id
                                             and pa.options_id = :options_id
                                             and pa.options_id = popt.products_options_id
                                             and pa.options_values_id = :options_values_id
                                             and pa.options_values_id = poval.products_options_values_id
                                             and popt.language_id = :language_id
                                             and popt.language_id = poval.language_id');
          $Qattributes->bindInt(':products_id', $products[$i]['id']);
          $Qattributes->bindInt(':options_id', $option);
          $Qattributes->bindInt(':options_values_id', $value);
          $Qattributes->bindInt(':language_id', $_SESSION['languages_id']);
          $Qattributes->execute();

          $products[$i][$option]['products_options_name'] = $Qattributes->value('products_options_name');
          $products[$i][$option]['options_values_id'] = $value;
          $products[$i][$option]['products_options_values_name'] = $Qattributes->value('products_options_values_name');
          $products[$i][$option]['options_values_price'] = $Qattributes->value('options_values_price');
          $products[$i][$option]['price_prefix'] = $Qattributes->value('price_prefix');
        }
      }
    }
?>

    <table class="table table-striped table-condensed">
      <tbody>
<?php
    $products_name = NULL;
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      $products_name .= '<tr>';

      $products_name .= '  <td valign="top" align="center"><a href="' . OSCOM::link('product_info.php', 'products_id=' . $products[$i]['id']) . '">' . HTML::image(DIR_WS_IMAGES . $products[$i]['image'], $products[$i]['name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a></td>' .
                        '  <td valign="top"><a href="' . OSCOM::link('product_info.php', 'products_id=' . $products[$i]['id']) . '"><strong>' . $products[$i]['name'] . '</strong></a>';

      if (STOCK_CHECK == 'true') {
        $stock_check = tep_check_stock($products[$i]['id'], $products[$i]['quantity']);
        if (tep_not_null($stock_check)) {
          $any_out_of_stock = 1;

          $products_name .= $stock_check;
        }
      }

      if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
        foreach($products[$i]['attributes'] as $option => $value) {
          $products_name .= '<br /><small><i> - ' . $products[$i][$option]['products_options_name'] . ' ' . $products[$i][$option]['products_options_values_name'] . '</i></small>';
        }
      }

      $products_name .= '<br>' . HTML::inputField('cart_quantity[]', $products[$i]['quantity'], 'style="width: 65px;" min="0"', 'number') . HTML::hiddenField('products_id[]', $products[$i]['id']) . ' ' . HTML::button(NULL, 'fa fa-refresh', NULL, NULL, NULL, 'btn-info btn-xs') . ' ' . HTML::button(NULL, 'fa fa-remove', OSCOM::link('shopping_cart.php', 'products_id=' . $products[$i]['id'] . '&action=remove_product'), NULL, NULL, 'btn-danger btn-xs');

      $products_name .= '</td>';

      $products_name .= '  <td align="right" valign="top"><strong>' . $currencies->display_price($products[$i]['final_price'], tep_get_tax_rate($products[$i]['tax_class_id']), $products[$i]['quantity']) . '</strong></td>' .
                        '</tr>';
    }
    echo $products_name;
?>

      </tbody>
    </table>

    <p class="text-right"><strong><?php echo SUB_TITLE_SUB_TOTAL; ?> <?php echo $currencies->format($_SESSION['cart']->show_total()); ?></strong></p>

<?php
    if ($any_out_of_stock == 1) {
      if (STOCK_ALLOW_CHECKOUT == 'true') {
?>

    <div class="alert alert-warning"><?php echo OUT_OF_STOCK_CAN_CHECKOUT; ?></div>

<?php
      } else {
?>

    <div class="alert alert-danger"><?php echo OUT_OF_STOCK_CANT_CHECKOUT; ?></div>

<?php
      }
    }
?>

  </div>

  <div class="buttonSet">
    <div class="text-right"><?php echo HTML::button(IMAGE_BUTTON_CHECKOUT, 'fa fa-angle-right', OSCOM::link('checkout_shipping.php', '', 'SSL'), 'primary', null, 'btn-success'); ?></div>
  </div>

<?php
    $checkout_buttons = Registry::get('Hooks')->call('Cart', 'AdditionalCheckoutButtons', null, 'display');

    if (!empty($checkout_buttons)) {
      echo '<div class="clearfix"></div>';
      echo '<p class="text-right">' . TEXT_ALTERNATIVE_CHECKOUT_METHODS . '</p>';

      foreach ($checkout_buttons as $button) {
        echo '<p class="text-right">' . $button . '</p>';
      }
    }
?>

</div>

</form>

<?php
  } else {
?>

<div class="alert alert-danger">
  <?php echo TEXT_CART_EMPTY; ?>
</div>

<p class="text-right"><?php echo HTML::button(IMAGE_BUTTON_CONTINUE, 'fa fa-angle-right', OSCOM::link('index.php'), 'primary', NULL, 'btn-danger'); ?></p>

<?php
  }

  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
