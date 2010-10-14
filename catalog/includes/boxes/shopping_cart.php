<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  $cart_contents_string = '';

  if ($cart->count_contents() > 0) {
    $cart_contents_string = '<table border="0" width="100%" cellspacing="0" cellpadding="0" class="ui-widget-content infoBoxContents">';
    $products = $cart->get_products();
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      $cart_contents_string .= '<tr><td align="right" valign="top">';

      if ((tep_session_is_registered('new_products_id_in_cart')) && ($new_products_id_in_cart == $products[$i]['id'])) {
        $cart_contents_string .= '<span class="newItemInCart">';
      }

      $cart_contents_string .= $products[$i]['quantity'] . '&nbsp;x&nbsp;';

      if ((tep_session_is_registered('new_products_id_in_cart')) && ($new_products_id_in_cart == $products[$i]['id'])) {
        $cart_contents_string .= '</span>';
      }

      $cart_contents_string .= '</td><td valign="top"><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products[$i]['id']) . '">';

      if ((tep_session_is_registered('new_products_id_in_cart')) && ($new_products_id_in_cart == $products[$i]['id'])) {
        $cart_contents_string .= '<span class="newItemInCart">';
      }

      $cart_contents_string .= $products[$i]['name'];

      if ((tep_session_is_registered('new_products_id_in_cart')) && ($new_products_id_in_cart == $products[$i]['id'])) {
        $cart_contents_string .= '</span>';
      }

      $cart_contents_string .= '</a></td></tr>';

      if ((tep_session_is_registered('new_products_id_in_cart')) && ($new_products_id_in_cart == $products[$i]['id'])) {
        tep_session_unregister('new_products_id_in_cart');
      }
    }

    $cart_contents_string .= '<tr><td colspan="2" style="padding-top: 5px; padding-bottom: 2px;">' . tep_draw_separator() . '</td></tr>' .
                             '<tr><td colspan="2" align="right">' . $currencies->format($cart->show_total()) . '</td></tr>' .
                             '</table>';
  } else {
    $cart_contents_string .= '<div class="ui-widget-content infoBoxContents">' . BOX_SHOPPING_CART_EMPTY . '</div>';
  }
?>

<div class="ui-widget infoBoxContainer">
  <div class="ui-widget-header infoBoxHeading"><?php echo '<a href="' . tep_href_link(FILENAME_SHOPPING_CART) . '">' . BOX_HEADING_SHOPPING_CART . '</a>'; ?></div>

<?php
  echo  $cart_contents_string;
?>

</div>
