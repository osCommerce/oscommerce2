<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  if (isset($_GET['products_id'])) {
    $orders_query = tep_db_query("select p.products_id, p.products_image, pd.products_name from " . TABLE_ORDERS_PRODUCTS . " opa, " . TABLE_ORDERS_PRODUCTS . " opb, " . TABLE_ORDERS . " o, " . TABLE_PRODUCTS . " p LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id where opa.products_id = '" . (int)$_GET['products_id'] . "' and opa.orders_id = opb.orders_id and opb.products_id != '" . (int)$_GET['products_id'] . "' and opb.products_id = p.products_id and opb.orders_id = o.orders_id and p.products_status = '1' and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' group by p.products_id order by o.date_purchased desc limit " . MAX_DISPLAY_ALSO_PURCHASED);
    $num_products_ordered = tep_db_num_rows($orders_query);
    if ($num_products_ordered >= MIN_DISPLAY_ALSO_PURCHASED) {

      $also_pur_prods_content = NULL;
?>
    <h3><?php echo TEXT_ALSO_PURCHASED_PRODUCTS; ?></h3>

    <div class="row">      

<?php   while ($orders = tep_db_fetch_array($orders_query)) { ?>
        <div class="col-sm-6 col-md-4">
          <div class="thumbnail">
          <a href="<?php echo tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $orders['products_id']) . '">' . tep_image(DIR_WS_IMAGES . $orders['products_image'], $orders['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT); ?></a>
            <div class="caption">
              <p class="text-center"><a href="<?php echo tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $orders['products_id']) . '">' . $orders['products_name']; ?></a></p>
            </div>
          </div>
        </div>
<?php   } ?>

    </div>

<?php
    }
  }
?>
