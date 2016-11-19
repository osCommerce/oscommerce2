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

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'insert':
        $tax_zone_id = HTML::sanitize($_POST['tax_zone_id']);
        $tax_class_id = HTML::sanitize($_POST['tax_class_id']);
        $tax_rate = HTML::sanitize($_POST['tax_rate']);
        $tax_description = HTML::sanitize($_POST['tax_description']);
        $tax_priority = HTML::sanitize($_POST['tax_priority']);

        $OSCOM_Db->save('tax_rates', [
          'tax_zone_id' => (int)$tax_zone_id,
          'tax_class_id' => (int)$tax_class_id,
          'tax_rate' => $tax_rate,
          'tax_description' => $tax_description,
          'tax_priority' => (int)$tax_priority,
          'date_added' => 'now()'
        ]);

        OSCOM::redirect(FILENAME_TAX_RATES);
        break;
      case 'save':
        $tax_rates_id = HTML::sanitize($_GET['tID']);
        $tax_zone_id = HTML::sanitize($_POST['tax_zone_id']);
        $tax_class_id = HTML::sanitize($_POST['tax_class_id']);
        $tax_rate = HTML::sanitize($_POST['tax_rate']);
        $tax_description = HTML::sanitize($_POST['tax_description']);
        $tax_priority = HTML::sanitize($_POST['tax_priority']);

        $OSCOM_Db->save('tax_rates', [
          'tax_zone_id' => (int)$tax_zone_id,
          'tax_class_id' => (int)$tax_class_id,
          'tax_rate' => $tax_rate,
          'tax_description' => $tax_description,
          'tax_priority' => (int)$tax_priority,
          'last_modified' => 'now()'
        ], [
          'tax_rates_id' => (int)$tax_rates_id
        ]);

        OSCOM::redirect(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $tax_rates_id);
        break;
      case 'deleteconfirm':
        $tax_rates_id = HTML::sanitize($_GET['tID']);

        $OSCOM_Db->delete('tax_rates', ['tax_rates_id' => (int)$tax_rates_id]);

        OSCOM::redirect(FILENAME_TAX_RATES, 'page=' . $_GET['page']);
        break;
    }
  }

  require($oscTemplate->getFile('template_top.php'));
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
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
                <td class="dataTableHeadingContent"><?php echo OSCOM::getDef('table_heading_tax_rate_priority'); ?></td>
                <td class="dataTableHeadingContent"><?php echo OSCOM::getDef('table_heading_tax_class_title'); ?></td>
                <td class="dataTableHeadingContent"><?php echo OSCOM::getDef('table_heading_zone'); ?></td>
                <td class="dataTableHeadingContent"><?php echo OSCOM::getDef('table_heading_tax_rate'); ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo OSCOM::getDef('table_heading_action'); ?>&nbsp;</td>
              </tr>
<?php
  $Qrates = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS r.tax_rates_id, z.geo_zone_id, z.geo_zone_name, tc.tax_class_title, tc.tax_class_id, r.tax_priority, r.tax_rate, r.tax_description, r.date_added, r.last_modified from :table_tax_class tc, :table_tax_rates r left join :table_geo_zones z on r.tax_zone_id = z.geo_zone_id where r.tax_class_id = tc.tax_class_id limit :page_set_offset, :page_set_max_results');
  $Qrates->setPageSet(MAX_DISPLAY_SEARCH_RESULTS);
  $Qrates->execute();

  while ($Qrates->fetch()) {
    if ((!isset($_GET['tID']) || (isset($_GET['tID']) && ((int)$_GET['tID'] === $Qrates->valueInt('tax_rates_id')))) && !isset($trInfo) && (substr($action, 0, 3) != 'new')) {
      $trInfo = new objectInfo($Qrates->toArray());
    }

    if (isset($trInfo) && is_object($trInfo) && ($Qrates->valueInt('tax_rates_id') === (int)$trInfo->tax_rates_id)) {
      echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $Qrates->valueInt('tax_rates_id')) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $Qrates->value('tax_priority'); ?></td>
                <td class="dataTableContent"><?php echo $Qrates->value('tax_class_title'); ?></td>
                <td class="dataTableContent"><?php echo $Qrates->value('geo_zone_name'); ?></td>
                <td class="dataTableContent"><?php echo tep_display_tax_value($Qrates->value('tax_rate')); ?>%</td>
                <td class="dataTableContent" align="right"><?php if (isset($trInfo) && is_object($trInfo) && ($Qrates->valueInt('tax_rates_id') === (int)$trInfo->tax_rates_id)) { echo HTML::image(OSCOM::linkImage('icon_arrow_right.gif'), ''); } else { echo '<a href="' . OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $Qrates->valueInt('tax_rates_id')) . '">' . HTML::image(OSCOM::linkImage('icon_info.gif'), OSCOM::getDef('image_icon_info')) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $Qrates->getPageSetLabel(OSCOM::getDef('text_display_number_of_tax_rates')); ?></td>
                    <td class="smallText" align="right"><?php echo $Qrates->getPageSetLinks(); ?></td>
                  </tr>
<?php
  if (empty($action)) {
?>
                  <tr>
                    <td class="smallText" colspan="5" align="right"><?php echo HTML::button(OSCOM::getDef('image_new_tax_rate'), 'fa fa-plus', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&action=new')); ?></td>
                  </tr>
<?php
  }
?>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'new':
      $heading[] = array('text' => '<strong>' . OSCOM::getDef('text_info_heading_new_tax_rate') . '</strong>');

      $contents = array('form' => HTML::form('rates', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&action=insert')));
      $contents[] = array('text' => OSCOM::getDef('text_info_insert_intro'));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_class_title') . '<br />' . tep_tax_classes_pull_down('name="tax_class_id" style="font-size:10px"'));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_zone_name') . '<br />' . tep_geo_zones_pull_down('name="tax_zone_id" style="font-size:10px"'));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_tax_rate') . '<br />' . HTML::inputField('tax_rate'));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_rate_description') . '<br />' . HTML::inputField('tax_description'));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_tax_rate_priority') . '<br />' . HTML::inputField('tax_priority'));
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::getDef('image_save'), 'fa fa-save') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'])));
      break;
    case 'edit':
      $heading[] = array('text' => '<strong>' . OSCOM::getDef('text_info_heading_edit_tax_rate') . '</strong>');

      $contents = array('form' => HTML::form('rates', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id  . '&action=save')));
      $contents[] = array('text' => OSCOM::getDef('text_info_edit_intro'));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_class_title') . '<br />' . tep_tax_classes_pull_down('name="tax_class_id" style="font-size:10px"', $trInfo->tax_class_id));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_zone_name') . '<br />' . tep_geo_zones_pull_down('name="tax_zone_id" style="font-size:10px"', $trInfo->geo_zone_id));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_tax_rate') . '<br />' . HTML::inputField('tax_rate', $trInfo->tax_rate));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_rate_description') . '<br />' . HTML::inputField('tax_description', $trInfo->tax_description));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_tax_rate_priority') . '<br />' . HTML::inputField('tax_priority', $trInfo->tax_priority));
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::getDef('image_save'), 'fa fa-save') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id)));
      break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . OSCOM::getDef('text_info_heading_delete_tax_rate') . '</strong>');

      $contents = array('form' => HTML::form('rates', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id  . '&action=deleteconfirm')));
      $contents[] = array('text' => OSCOM::getDef('text_info_delete_intro'));
      $contents[] = array('text' => '<br /><strong>' . $trInfo->tax_class_title . ' ' . number_format($trInfo->tax_rate, TAX_DECIMAL_PLACES) . '%</strong>');
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::getDef('image_delete'), 'fa fa-trash') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id)));
      break;
    default:
      if (is_object($trInfo)) {
        $heading[] = array('text' => '<strong>' . $trInfo->tax_class_title . '</strong>');
        $contents[] = array('align' => 'center', 'text' => HTML::button(OSCOM::getDef('image_edit'), 'fa fa-edit', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id . '&action=edit')) . HTML::button(OSCOM::getDef('image_delete'), 'fa fa-trash', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id . '&action=delete')));
        $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_date_added') . ' ' . DateTime::toShort($trInfo->date_added));
        $contents[] = array('text' => '' . OSCOM::getDef('text_info_last_modified') . ' ' . DateTime::toShort($trInfo->last_modified));
        $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_rate_description') . '<br />' . $trInfo->tax_description);
      }
      break;
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
