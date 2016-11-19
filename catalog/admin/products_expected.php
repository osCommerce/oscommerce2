<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\DateTime;
  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  if (!isset($_GET['page']) || !is_numeric($_GET['page'])) {
    $_GET['page'] = 1;
  }

  $OSCOM_Db->exec('update :table_products set products_date_available = "" where to_days(now()) > to_days(products_date_available)');

  require($oscTemplate->getFile('template_top.php'));
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
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
                <td class="dataTableHeadingContent"><?php echo OSCOM::getDef('table_heading_products'); ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo OSCOM::getDef('table_heading_date_expected'); ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo OSCOM::getDef('table_heading_action'); ?>&nbsp;</td>
              </tr>
<?php
  $Qproducts = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS pd.products_id, pd.products_name, p.products_date_available from :table_products_description pd, :table_products p where p.products_id = pd.products_id and p.products_date_available != "" and pd.language_id = :language_id order by p.products_date_available desc limit :page_set_offset, :page_set_max_results');
  $Qproducts->bindInt(':language_id', $OSCOM_Language->getId());
  $Qproducts->setPageSet(MAX_DISPLAY_SEARCH_RESULTS);
  $Qproducts->execute();

  while ($Qproducts->fetch()) {
    if ((!isset($_GET['pID']) || (isset($_GET['pID']) && ((int)$_GET['pID'] === $Qproducts->valueInt('products_id')))) && !isset($pInfo)) {
      $pInfo = new objectInfo($Qproducts->toArray());
    }

    if (isset($pInfo) && is_object($pInfo) && ($Qproducts->valueInt('products_id') === (int)$pInfo->products_id)) {
      echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_CATEGORIES, 'pID=' . $Qproducts->valueInt('products_id') . '&action=new_product') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_PRODUCTS_EXPECTED, 'page=' . $_GET['page'] . '&pID=' . $Qproducts->valueInt('products_id')) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $Qproducts->value('products_name'); ?></td>
                <td class="dataTableContent" align="center"><?php echo DateTime::toShort($Qproducts->value('products_date_available')); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($pInfo) && is_object($pInfo) && ($Qproducts->valueInt('products_id') === (int)$pInfo->products_id)) { echo HTML::image(OSCOM::linkImage('icon_arrow_right.gif')); } else { echo '<a href="' . OSCOM::link(FILENAME_PRODUCTS_EXPECTED, 'page=' . $_GET['page'] . '&pID=' . $Qproducts->valueInt('products_id')) . '">' . HTML::image(OSCOM::linkImage('icon_info.gif'), OSCOM::getDef('image_icon_info')) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $Qproducts->getPageSetLabel(OSCOM::getDef('text_display_number_of_products_expected')); ?></td>
                    <td class="smallText" align="right"><?php echo $Qproducts->getPageSetLinks(); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  if (isset($pInfo) && is_object($pInfo)) {
    $heading[] = array('text' => '<strong>' . $pInfo->products_name . '</strong>');

    $contents[] = array('align' => 'center', 'text' => HTML::button(OSCOM::getDef('image_edit'), 'fa fa-edit', OSCOM::link(FILENAME_CATEGORIES, 'pID=' . $pInfo->products_id . '&action=new_product')));
    $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_date_expected') . ' ' . DateTime::toShort($pInfo->products_date_available));
  }

  if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
    </table>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
