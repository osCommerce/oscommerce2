<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  $global_query = osc_db_query("select global_product_notifications from " . TABLE_CUSTOMERS_INFO . " where customers_info_id = '" . (int)$_SESSION['customer_id'] . "'");
  $global = osc_db_fetch_array($global_query);

  if ($global['global_product_notifications'] != '1') {
    $orders_query = osc_db_query("select orders_id from " . TABLE_ORDERS . " where customers_id = '" . (int)$_SESSION['customer_id'] . "' order by date_purchased desc limit 1");
    $orders = osc_db_fetch_array($orders_query);

    $products_array = array();
    $products_query = osc_db_query("select products_id, products_name from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$orders['orders_id'] . "' order by products_name");
    while ($products = osc_db_fetch_array($products_query)) {
      $products_array[] = array('id' => $products['products_id'],
                                'text' => $products['products_name']);
    }
  }
?>

<h1><?php echo HEADING_TITLE_SUCCESS; ?></h1>

<?php echo osc_draw_form('order', osc_href_link('checkout', 'success&process', 'SSL')); ?>

<div class="contentContainer">
  <div class="contentText">
    <?php echo TEXT_SUCCESS; ?>
  </div>

  <div class="contentText">

<?php
  if ($global['global_product_notifications'] != '1') {
    echo TEXT_NOTIFY_PRODUCTS . '<br /><p class="productsNotifications">';

    $products_displayed = array();
    for ($i=0, $n=sizeof($products_array); $i<$n; $i++) {
      if (!in_array($products_array[$i]['id'], $products_displayed)) {
        echo osc_draw_checkbox_field('notify[]', $products_array[$i]['id']) . ' ' . $products_array[$i]['text'] . '<br />';
        $products_displayed[] = $products_array[$i]['id'];
      }
    }

    echo '</p>';
  }

  echo TEXT_SEE_ORDERS . '<br /><br />' . TEXT_CONTACT_STORE_OWNER;
?>

  </div>

  <div class="contentText">
    <h3><?php echo TEXT_THANKS_FOR_SHOPPING; ?></h3>
  </div>

<?php
  if (DOWNLOAD_ENABLED == 'true') {
    include(DIR_WS_MODULES . 'downloads.php');
  }
?>

  <div class="buttonSet">
    <span class="buttonAction"><?php echo osc_draw_button(IMAGE_BUTTON_CONTINUE, 'triangle-1-e', null, 'primary'); ?></span>
  </div>
</div>

</form>
