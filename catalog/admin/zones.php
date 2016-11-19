<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
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
        $zone_country_id = HTML::sanitize($_POST['zone_country_id']);
        $zone_code = HTML::sanitize($_POST['zone_code']);
        $zone_name = HTML::sanitize($_POST['zone_name']);

        $OSCOM_Db->save('zones', [
          'zone_country_id' => (int)$zone_country_id,
          'zone_code' => $zone_code,
          'zone_name' => $zone_name
        ]);

        OSCOM::redirect(FILENAME_ZONES);
        break;
      case 'save':
        $zone_id = HTML::sanitize($_GET['cID']);
        $zone_country_id = HTML::sanitize($_POST['zone_country_id']);
        $zone_code = HTML::sanitize($_POST['zone_code']);
        $zone_name = HTML::sanitize($_POST['zone_name']);

        $OSCOM_Db->save('zones', [
          'zone_country_id' => (int)$zone_country_id,
          'zone_code' => $zone_code,
          'zone_name' => $zone_name
        ], [
          'zone_id' => (int)$zone_id
        ]);

        OSCOM::redirect(FILENAME_ZONES, 'page=' . $_GET['page'] . '&cID=' . $zone_id);
        break;
      case 'deleteconfirm':
        $zone_id = HTML::sanitize($_GET['cID']);

        $OSCOM_Db->delete('zones', ['zone_id' => (int)$zone_id]);

        OSCOM::redirect(FILENAME_ZONES, 'page=' . $_GET['page']);
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
                <td class="dataTableHeadingContent"><?php echo OSCOM::getDef('table_heading_country_name'); ?></td>
                <td class="dataTableHeadingContent"><?php echo OSCOM::getDef('table_heading_zone_name'); ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo OSCOM::getDef('table_heading_zone_code'); ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo OSCOM::getDef('table_heading_action'); ?>&nbsp;</td>
              </tr>
<?php
  $Qzones = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS z.zone_id, c.countries_id, c.countries_name, z.zone_name, z.zone_code, z.zone_country_id from :table_zones z, :table_countries c where z.zone_country_id = c.countries_id order by c.countries_name, z.zone_name limit :page_set_offset, :page_set_max_results');
  $Qzones->setPageSet(MAX_DISPLAY_SEARCH_RESULTS);
  $Qzones->execute();

  while ($Qzones->fetch()) {
    if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ((int)$_GET['cID'] === $Qzones->valueInt('zone_id')))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
      $cInfo = new objectInfo($Qzones->toArray());
    }

    if (isset($cInfo) && is_object($cInfo) && ($Qzones->valueInt('zone_id') === (int)$cInfo->zone_id)) {
      echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_ZONES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->zone_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_ZONES, 'page=' . $_GET['page'] . '&cID=' . $Qzones->valueInt('zone_id')) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $Qzones->value('countries_name'); ?></td>
                <td class="dataTableContent"><?php echo $Qzones->value('zone_name'); ?></td>
                <td class="dataTableContent" align="center"><?php echo $Qzones->value('zone_code'); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($cInfo) && is_object($cInfo) && ($Qzones->valueInt('zone_id') === (int)$cInfo->zone_id) ) { echo HTML::image(OSCOM::linkImage('icon_arrow_right.gif'), ''); } else { echo '<a href="' . OSCOM::link(FILENAME_ZONES, 'page=' . $_GET['page'] . '&cID=' . $Qzones->valueInt('zone_id')) . '">' . HTML::image(OSCOM::linkImage('icon_info.gif'), OSCOM::getDef('image_icon_info')) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="4"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $Qzones->getPageSetLabel(OSCOM::getDef('text_display_number_of_zones')); ?></td>
                    <td class="smallText" align="right"><?php echo $Qzones->getPageSetLinks(); ?></td>
                  </tr>
<?php
  if (empty($action)) {
?>
                  <tr>
                    <td class="smallText" colspan="2" align="right"><?php echo HTML::button(OSCOM::getDef('image_new_zone'), 'fa fa-plus', OSCOM::link(FILENAME_ZONES, 'page=' . $_GET['page'] . '&action=new')); ?></td>
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
      $heading[] = array('text' => '<strong>' . OSCOM::getDef('text_info_heading_new_zone') . '</strong>');

      $contents = array('form' => HTML::form('zones', OSCOM::link(FILENAME_ZONES, 'page=' . $_GET['page'] . '&action=insert')));
      $contents[] = array('text' => OSCOM::getDef('text_info_insert_intro'));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_zones_name') . '<br />' . HTML::inputField('zone_name'));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_zones_code') . '<br />' . HTML::inputField('zone_code'));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_country_name') . '<br />' . HTML::selectField('zone_country_id', tep_get_countries()));
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::getDef('image_save'), 'fa fa-save') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_ZONES, 'page=' . $_GET['page'])));
      break;
    case 'edit':
      $heading[] = array('text' => '<strong>' . OSCOM::getDef('text_info_heading_edit_zone') . '</strong>');

      $contents = array('form' => HTML::form('zones', OSCOM::link(FILENAME_ZONES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->zone_id . '&action=save')));
      $contents[] = array('text' => OSCOM::getDef('text_info_edit_intro'));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_zones_name') . '<br />' . HTML::inputField('zone_name', $cInfo->zone_name));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_zones_code') . '<br />' . HTML::inputField('zone_code', $cInfo->zone_code));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_country_name') . '<br />' . HTML::selectField('zone_country_id', tep_get_countries(), $cInfo->countries_id));
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::getDef('image_save'), 'fa fa-save') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_ZONES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->zone_id)));
      break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . OSCOM::getDef('text_info_heading_delete_zone') . '</strong>');

      $contents = array('form' => HTML::form('zones', OSCOM::link(FILENAME_ZONES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->zone_id . '&action=deleteconfirm')));
      $contents[] = array('text' => OSCOM::getDef('text_info_delete_intro'));
      $contents[] = array('text' => '<br /><strong>' . $cInfo->zone_name . '</strong>');
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::getDef('image_delete'), 'fa fa-trash') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_ZONES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->zone_id)));
      break;
    default:
      if (isset($cInfo) && is_object($cInfo)) {
        $heading[] = array('text' => '<strong>' . $cInfo->zone_name . '</strong>');

        $contents[] = array('align' => 'center', 'text' => HTML::button(OSCOM::getDef('image_edit'), 'fa fa-edit', OSCOM::link(FILENAME_ZONES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->zone_id . '&action=edit')) . HTML::button(OSCOM::getDef('image_delete'), 'fa fa-trash', OSCOM::link(FILENAME_ZONES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->zone_id . '&action=delete')));
        $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_zones_name') . '<br />' . $cInfo->zone_name . ' (' . $cInfo->zone_code . ')');
        $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_country_name') . ' ' . $cInfo->countries_name);
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
