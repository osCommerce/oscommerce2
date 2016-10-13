<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  require('includes/classes/currencies.php');
  $currencies = new currencies();

  if (!isset($_GET['page']) || !is_numeric($_GET['page'])) {
    $_GET['page'] = 1;
  }

  require('includes/template_top.php');
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_NUMBER; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TOTAL_PURCHASED; ?>&nbsp;</td>
              </tr>
<?php
  $rows = 0;

  $Qcustomers = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS c.customers_firstname, c.customers_lastname, sum(op.products_quantity * op.final_price) as ordersum from :table_customers c, :table_orders_products op, :table_orders o where c.customers_id = o.customers_id and o.orders_id = op.orders_id group by c.customers_firstname, c.customers_lastname order by ordersum desc limit :page_set_offset, :page_set_max_results');
  $Qcustomers->setPageSet(MAX_DISPLAY_SEARCH_RESULTS);
  $Qcustomers->execute();

  while ($Qcustomers->fetch()) {
    $rows++;

    if (strlen($rows) < 2) {
      $rows = '0' . $rows;
    }
?>
              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href='<?php echo OSCOM::link(FILENAME_CUSTOMERS, 'search=' . $Qcustomers->value('customers_lastname')); ?>'">
                <td class="dataTableContent"><?php echo $rows; ?>.</td>
                <td class="dataTableContent"><?php echo '<a href="' . OSCOM::link(FILENAME_CUSTOMERS, 'search=' . $Qcustomers->value('customers_lastname')) . '">' . $Qcustomers->value('customers_firstname') . ' ' . $Qcustomers->value('customers_lastname') . '</a>'; ?></td>
                <td class="dataTableContent" align="right"><?php echo $currencies->format($Qcustomers->value('ordersum')); ?>&nbsp;</td>
              </tr>
<?php
  }
?>
            </table></td>
          </tr>
          <tr>
            <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="smallText" valign="top"><?php echo $Qcustomers->getPageSetLabel(TEXT_DISPLAY_NUMBER_OF_CUSTOMERS); ?></td>
                <td class="smallText" align="right"><?php echo $Qcustomers->getPageSetLinks(); ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
    </table>

<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
