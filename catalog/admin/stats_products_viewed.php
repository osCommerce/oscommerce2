<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  if (!isset($_GET['page']) || !is_numeric($_GET['page'])) {
    $_GET['page'] = 1;
  }

  require($oscTemplate->getFile('template_top.php'));
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo OSCOM::getDef('heading_title'); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo OSCOM::getDef('table_heading_number'); ?></td>
                <td class="dataTableHeadingContent"><?php echo OSCOM::getDef('table_heading_products'); ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo OSCOM::getDef('table_heading_viewed'); ?>&nbsp;</td>
              </tr>
<?php
  $rows = 0;

  $Qproducts = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS p.products_id, pd.products_name, pd.products_viewed, l.name from :table_products p, :table_products_description pd, :table_languages l where pd.products_viewed > 0 and p.products_id = pd.products_id and l.languages_id = pd.language_id order by pd.products_viewed desc limit :page_set_offset, :page_set_max_results');
  $Qproducts->setPageSet(MAX_DISPLAY_SEARCH_RESULTS);
  $Qproducts->execute();

  while ($Qproducts->fetch()) {
    $rows++;

    if (strlen($rows) < 2) {
      $rows = '0' . $rows;
    }
?>
              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href='<?php echo OSCOM::link(FILENAME_CATEGORIES, 'action=new_product_preview&read=only&pID=' . $Qproducts->valueInt('products_id') . '&origin=' . FILENAME_STATS_PRODUCTS_VIEWED . '&page=' . $_GET['page']); ?>'">
                <td class="dataTableContent"><?php echo $rows; ?>.</td>
                <td class="dataTableContent"><?php echo '<a href="' . OSCOM::link(FILENAME_CATEGORIES, 'action=new_product_preview&read=only&pID=' . $Qproducts->valueInt('products_id') . '&origin=' . FILENAME_STATS_PRODUCTS_VIEWED . '&page=' . $_GET['page']) . '">' . $Qproducts->value('products_name') . '</a> (' . $Qproducts->value('name') . ')'; ?></td>
                <td class="dataTableContent" align="center"><?php echo $Qproducts->valueInt('products_viewed'); ?>&nbsp;</td>
              </tr>
<?php
  }
?>
            </table></td>
          </tr>
          <tr>
            <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="smallText" valign="top"><?php echo $Qproducts->getPageSetLabel(OSCOM::getDef('text_display_number_of_products')); ?></td>
                <td class="smallText" align="right"><?php echo $Qproducts->getPageSetLinks(); ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
    </table>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
