<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

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
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX_RATE_PRIORITY; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX_CLASS_TITLE; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ZONE; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX_RATE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
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
                <td class="dataTableContent" align="right"><?php if (isset($trInfo) && is_object($trInfo) && ($Qrates->valueInt('tax_rates_id') === (int)$trInfo->tax_rates_id)) { echo HTML::image(OSCOM::linkImage('icon_arrow_right.gif'), ''); } else { echo '<a href="' . OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $Qrates->valueInt('tax_rates_id')) . '">' . HTML::image(OSCOM::linkImage('icon_info.gif'), IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $Qrates->getPageSetLabel(TEXT_DISPLAY_NUMBER_OF_TAX_RATES); ?></td>
                    <td class="smallText" align="right"><?php echo $Qrates->getPageSetLinks(); ?></td>
                  </tr>
<?php
  if (empty($action)) {
?>
                  <tr>
                    <td class="smallText" colspan="5" align="right"><?php echo HTML::button(IMAGE_NEW_TAX_RATE, 'fa fa-plus', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&action=new')); ?></td>
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
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_NEW_TAX_RATE . '</strong>');

      $contents = array('form' => HTML::form('rates', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&action=insert')));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_CLASS_TITLE . '<br />' . tep_tax_classes_pull_down('name="tax_class_id" style="font-size:10px"'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_ZONE_NAME . '<br />' . tep_geo_zones_pull_down('name="tax_zone_id" style="font-size:10px"'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_TAX_RATE . '<br />' . HTML::inputField('tax_rate'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_RATE_DESCRIPTION . '<br />' . HTML::inputField('tax_description'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_TAX_RATE_PRIORITY . '<br />' . HTML::inputField('tax_priority'));
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(IMAGE_SAVE, 'fa fa-save', null, 'primary') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'])));
      break;
    case 'edit':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_EDIT_TAX_RATE . '</strong>');

      $contents = array('form' => HTML::form('rates', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id  . '&action=save')));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_CLASS_TITLE . '<br />' . tep_tax_classes_pull_down('name="tax_class_id" style="font-size:10px"', $trInfo->tax_class_id));
      $contents[] = array('text' => '<br />' . TEXT_INFO_ZONE_NAME . '<br />' . tep_geo_zones_pull_down('name="tax_zone_id" style="font-size:10px"', $trInfo->geo_zone_id));
      $contents[] = array('text' => '<br />' . TEXT_INFO_TAX_RATE . '<br />' . HTML::inputField('tax_rate', $trInfo->tax_rate));
      $contents[] = array('text' => '<br />' . TEXT_INFO_RATE_DESCRIPTION . '<br />' . HTML::inputField('tax_description', $trInfo->tax_description));
      $contents[] = array('text' => '<br />' . TEXT_INFO_TAX_RATE_PRIORITY . '<br />' . HTML::inputField('tax_priority', $trInfo->tax_priority));
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(IMAGE_SAVE, 'fa fa-save', null, 'primary') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id)));
      break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_TAX_RATE . '</strong>');

      $contents = array('form' => HTML::form('rates', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id  . '&action=deleteconfirm')));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br /><strong>' . $trInfo->tax_class_title . ' ' . number_format($trInfo->tax_rate, TAX_DECIMAL_PLACES) . '%</strong>');
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(IMAGE_DELETE, 'fa fa-trash', null, 'primary') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id)));
      break;
    default:
      if (is_object($trInfo)) {
        $heading[] = array('text' => '<strong>' . $trInfo->tax_class_title . '</strong>');
        $contents[] = array('align' => 'center', 'text' => HTML::button(IMAGE_EDIT, 'fa fa-edit', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id . '&action=edit')) . HTML::button(IMAGE_DELETE, 'fa fa-trash', OSCOM::link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id . '&action=delete')));
        $contents[] = array('text' => '<br />' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($trInfo->date_added));
        $contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($trInfo->last_modified));
        $contents[] = array('text' => '<br />' . TEXT_INFO_RATE_DESCRIPTION . '<br />' . $trInfo->tax_description);
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
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
