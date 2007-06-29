<?php
/*
  $Id: $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2007 osCommerce

  Released under the GNU General Public License
*/
?>

<table border="0" width="100%" cellspacing="0" cellpadding="4">
  <tr class="dataTableHeadingRow">
    <td class="dataTableHeadingContent"><?php echo ADMIN_INDEX_CUSTOMERS_TITLE; ?></td>
    <td class="dataTableHeadingContent"><?php echo ADMIN_INDEX_CUSTOMERS_DATE; ?></td>
  </tr>
<?php
  $customers_query = tep_db_query("select c.customers_id, c.customers_lastname, c.customers_firstname, ci.customers_info_date_account_created from " . TABLE_CUSTOMERS . " c, " . TABLE_CUSTOMERS_INFO . " ci where c.customers_id = ci.customers_info_id order by ci.customers_info_date_account_created desc limit 6");
  while ($customers = tep_db_fetch_array($customers_query)) {
    echo '  <tr class="dataTableRow" onmouseover="rowOverEffect(this);" onmouseout="rowOutEffect(this);">' .
         '    <td class="dataTableContent"><a href="' . tep_href_link(FILENAME_CUSTOMERS, 'cID=' . (int)$customers['customers_id'] . '&action=edit') . '">' . tep_output_string_protected($customers['customers_firstname'] . ' ' . $customers['customers_lastname']) . '</td>' .
         '    <td class="dataTableContent">' . $customers['customers_info_date_account_created'] . '</td>' .
         '  </tr>';
  }
?>
</table>
