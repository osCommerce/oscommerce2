<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */
?>

<h1><?php echo HEADING_TITLE_ORDERS; ?></h1>

<div class="contentContainer">

<?php
  $orders_total = osc_count_customer_orders();

  if ($orders_total > 0) {
    $history_query_raw = "select o.orders_id, o.date_purchased, o.delivery_name, o.billing_name, ot.text as order_total, s.orders_status_name from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_TOTAL . " ot, " . TABLE_ORDERS_STATUS . " s where o.customers_id = '" . (int)$_SESSION['customer_id'] . "' and o.orders_id = ot.orders_id and ot.class = 'ot_total' and o.orders_status = s.orders_status_id and s.language_id = '" . (int)$_SESSION['languages_id'] . "' and s.public_flag = '1' order by orders_id DESC";
    $history_split = new splitPageResults($history_query_raw, MAX_DISPLAY_ORDER_HISTORY);
    $history_query = osc_db_query($history_split->sql_query);

    while ($history = osc_db_fetch_array($history_query)) {
      $products_query = osc_db_query("select count(*) as count from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$history['orders_id'] . "'");
      $products = osc_db_fetch_array($products_query);

      if (osc_not_null($history['delivery_name'])) {
        $order_type = TEXT_ORDER_SHIPPED_TO;
        $order_name = $history['delivery_name'];
      } else {
        $order_type = TEXT_ORDER_BILLED_TO;
        $order_name = $history['billing_name'];
      }
?>

  <h2><?php echo TEXT_ORDER_NUMBER . ' ' . $history['orders_id'] . ' <span class="contentText">(' . $history['orders_status_name'] . ')</span>'; ?></h2>

  <div class="contentText">
    <table border="0" width="100%" cellspacing="2" cellpadding="2">
      <tr>
        <td width="50%" valign="top"><?php echo '<strong>' . TEXT_ORDER_DATE . '</strong> ' . osc_date_long($history['date_purchased']) . '<br /><strong>' . $order_type . '</strong> ' . osc_output_string_protected($order_name); ?></td>
        <td width="30%" valign="top"><?php echo '<strong>' . TEXT_ORDER_PRODUCTS . '</strong> ' . $products['count'] . '<br /><strong>' . TEXT_ORDER_COST . '</strong> ' . strip_tags($history['order_total']); ?></td>
        <td width="20%" align="right"><?php echo osc_draw_button(SMALL_IMAGE_BUTTON_VIEW, 'document', osc_href_link('account', 'orders&info&id=' . $history['orders_id'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'SSL'), 'primary'); ?></td>
     </tr>
    </table>
  </div>

<?php
    }
?>

  <div class="contentText">
    <p style="float: right;"><?php echo TEXT_RESULT_PAGE . ' ' . $history_split->display_links(MAX_DISPLAY_PAGE_LINKS, osc_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></p>

    <p><?php echo $history_split->display_count(TEXT_DISPLAY_NUMBER_OF_ORDERS); ?></p>
  </div>

<?php
  } else {
?>

  <div class="contentText">
    <p><?php echo TEXT_NO_PURCHASES; ?></p>
  </div>

<?php
  }
?>

  <div class="buttonSet">
    <?php echo osc_draw_button(IMAGE_BUTTON_BACK, 'triangle-1-w', osc_href_link('account', '', 'SSL')); ?>
  </div>
</div>
