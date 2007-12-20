<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2007 osCommerce

  Released under the GNU General Public License
*/
?>

<table border="0" width="100%" cellspacing="0" cellpadding="4">
  <tr class="dataTableHeadingRow">
    <td class="dataTableHeadingContent"><?php echo ADMIN_INDEX_ORDERS_TITLE; ?></td>
    <td class="dataTableHeadingContent"><?php echo ADMIN_INDEX_ORDERS_TOTAL; ?></td>
    <td class="dataTableHeadingContent"><?php echo ADMIN_INDEX_ORDERS_DATE; ?></td>
    <td class="dataTableHeadingContent"><?php echo ADMIN_INDEX_ORDERS_STATUS; ?></td>
  </tr>
<?php
  $orders_query = tep_db_query("select o.orders_id, o.customers_name, greatest(o.date_purchased, ifnull(o.last_modified, 0)) as date_last_modified, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_TOTAL . " ot, " . TABLE_ORDERS_STATUS . " s where o.orders_id = ot.orders_id and ot.class = 'ot_total' and o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' order by date_last_modified desc limit 6");
  while ($orders = tep_db_fetch_array($orders_query)) {
    echo '  <tr class="dataTableRow" onmouseover="rowOverEffect(this);" onmouseout="rowOutEffect(this);">' .
         '    <td class="dataTableContent"><a href="' . tep_href_link(FILENAME_ORDERS, 'oID=' . (int)$orders['orders_id'] . '&action=edit') . '">' . tep_output_string_protected($orders['customers_name']) . '</td>' .
         '    <td class="dataTableContent">' . strip_tags($orders['order_total']) . '</td>' .
         '    <td class="dataTableContent">' . $orders['date_last_modified'] . '</td>' .
         '    <td class="dataTableContent">' . $orders['orders_status_name'] . '</td>' .
         '  </tr>';
  }
?>
</table>
