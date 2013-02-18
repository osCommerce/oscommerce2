<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */
?>

<h1><?php echo HEADING_TITLE; ?></h1>

<?php echo tep_draw_form('cart_quantity', tep_href_link('cart', 'action=update_product')); ?>

<div class="contentContainer">
  <h2><?php echo TABLE_HEADING_PRODUCTS; ?></h2>

  <div class="contentText">

<?php
  $any_out_of_stock = 0;
  $products = $_SESSION['cart']->get_products();
  for ($i=0, $n=sizeof($products); $i<$n; $i++) {
// Push all attributes information in an array
    if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
      while (list($option, $value) = each($products[$i]['attributes'])) {
        echo tep_draw_hidden_field('id[' . $products[$i]['id'] . '][' . $option . ']', $value);
        $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix
                                    from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                    where pa.products_id = '" . (int)$products[$i]['id'] . "'
                                    and pa.options_id = '" . (int)$option . "'
                                    and pa.options_id = popt.products_options_id
                                    and pa.options_values_id = '" . (int)$value . "'
                                    and pa.options_values_id = poval.products_options_values_id
                                    and popt.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                    and poval.language_id = '" . (int)$_SESSION['languages_id'] . "'");
        $attributes_values = tep_db_fetch_array($attributes);

        $products[$i][$option]['products_options_name'] = $attributes_values['products_options_name'];
        $products[$i][$option]['options_values_id'] = $value;
        $products[$i][$option]['products_options_values_name'] = $attributes_values['products_options_values_name'];
        $products[$i][$option]['options_values_price'] = $attributes_values['options_values_price'];
        $products[$i][$option]['price_prefix'] = $attributes_values['price_prefix'];
      }
    }
  }
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="0">

<?php
  for ($i=0, $n=sizeof($products); $i<$n; $i++) {
    echo '      <tr>';

    $products_name = '<table border="0" cellspacing="2" cellpadding="2">' .
                     '  <tr>' .
                     '    <td align="center"><a href="' . tep_href_link('products', 'id=' . $products[$i]['id']) . '">' . tep_image(DIR_WS_IMAGES . $products[$i]['image'], $products[$i]['name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a></td>' .
                     '    <td valign="top"><a href="' . tep_href_link('products', 'id=' . $products[$i]['id']) . '"><strong>' . $products[$i]['name'] . '</strong></a>';

    if (STOCK_CHECK == 'true') {
      $stock_check = tep_check_stock($products[$i]['id'], $products[$i]['quantity']);
      if (tep_not_null($stock_check)) {
        $any_out_of_stock = 1;

        $products_name .= $stock_check;
      }
    }

    if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
      reset($products[$i]['attributes']);
      while (list($option, $value) = each($products[$i]['attributes'])) {
        $products_name .= '<br /><small><i> - ' . $products[$i][$option]['products_options_name'] . ' ' . $products[$i][$option]['products_options_values_name'] . '</i></small>';
      }
    }

    $products_name .= '<br /><br />' . tep_draw_input_field('cart_quantity[]', $products[$i]['quantity'], 'size="4"') . tep_draw_hidden_field('products_id[]', $products[$i]['id']) . tep_draw_button(IMAGE_BUTTON_UPDATE, 'refresh') . '&nbsp;&nbsp;&nbsp;' . TEXT_OR . '<a href="' . tep_href_link('cart', 'products_id=' . $products[$i]['id'] . '&action=remove_product') . '">' . TEXT_REMOVE . '</a>';

    $products_name .= '    </td>' .
                      '  </tr>' .
                      '</table>';

    echo '        <td valign="top">' . $products_name . '</td>' .
         '        <td align="right" valign="top"><strong>' . $currencies->display_price($products[$i]['final_price'], tep_get_tax_rate($products[$i]['tax_class_id']), $products[$i]['quantity']) . '</strong></td>' .
         '      </tr>';
  }
?>

    </table>

    <p align="right"><strong><?php echo SUB_TITLE_SUB_TOTAL; ?> <?php echo $currencies->format($_SESSION['cart']->show_total()); ?></strong></p>

<?php
  if ($any_out_of_stock == 1) {
    if (STOCK_ALLOW_CHECKOUT == 'true') {
?>

    <p class="stockWarning" align="center"><?php echo OUT_OF_STOCK_CAN_CHECKOUT; ?></p>

<?php
    } else {
?>

    <p class="stockWarning" align="center"><?php echo OUT_OF_STOCK_CANT_CHECKOUT; ?></p>

<?php
    }
  }
?>

  </div>

  <div class="buttonSet">
    <span class="buttonAction"><?php echo tep_draw_button(IMAGE_BUTTON_CHECKOUT, 'triangle-1-e', tep_href_link('checkout', '', 'SSL'), 'primary'); ?></span>
  </div>

<?php
  $initialize_checkout_methods = $payment_modules->checkout_initialization_method();

  if (!empty($initialize_checkout_methods)) {
?>

  <p align="right" style="clear: both; padding: 15px 50px 0 0;"><?php echo TEXT_ALTERNATIVE_CHECKOUT_METHODS; ?></p>

<?php
    reset($initialize_checkout_methods);
    while (list(, $value) = each($initialize_checkout_methods)) {
?>

  <p align="right"><?php echo $value; ?></p>

<?php
    }
  }
?>

</div>

</form>
