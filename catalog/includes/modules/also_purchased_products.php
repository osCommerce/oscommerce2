<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  if (isset($HTTP_GET_VARS['products_id'])) {
    $orders_query = tep_db_query("select p.products_id, p.products_image from " . TABLE_ORDERS_PRODUCTS . " opa, " . TABLE_ORDERS_PRODUCTS . " opb, " . TABLE_ORDERS . " o, " . TABLE_PRODUCTS . " p where opa.products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and opa.orders_id = opb.orders_id and opb.products_id != '" . (int)$HTTP_GET_VARS['products_id'] . "' and opb.products_id = p.products_id and opb.orders_id = o.orders_id and p.products_status = '1' group by p.products_id order by o.date_purchased desc limit " . MAX_DISPLAY_ALSO_PURCHASED);
    $num_products_ordered = tep_db_num_rows($orders_query);
    if ($num_products_ordered >= MIN_DISPLAY_ALSO_PURCHASED) {
      $counter = 0;
      $col = 0;

      $also_pur_prods_content = '<table border="0" width="100%" cellspacing="0" cellpadding="2" class="ui-widget-content ui-corner-bottom">';
      while ($orders = tep_db_fetch_array($orders_query)) {
        $counter++;

        $orders['products_name'] = tep_get_products_name($orders['products_id']);

        if ($col === 0) {
          $also_pur_prods_content .= '<tr>';
        }

        $also_pur_prods_content .= '<td width="33%" valign="top" align="center"><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $orders['products_id']) . '">' . tep_image(DIR_WS_IMAGES . $orders['products_image'], $orders['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a><br /><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $orders['products_id']) . '">' . $orders['products_name'] . '</a></td>';

        $col ++;

        if (($col > 2) || ($counter == $num_products_ordered)) {
          $also_pur_prods_content .= '</tr>';

          $col = 0;
        }
      }

      $also_pur_prods_content .= '</table>';
?>

  <br />

  <div class="ui-widget infoBoxContainer">
    <div class="ui-widget-header ui-corner-top infoBoxHeading">
      <span><?php echo TEXT_ALSO_PURCHASED_PRODUCTS; ?></span>
    </div>

    <?php echo $also_pur_prods_content; ?>
  </div>

<?php
    }
  }
?>
