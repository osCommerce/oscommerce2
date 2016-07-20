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
        $countries_name = HTML::sanitize($_POST['countries_name']);
        $countries_iso_code_2 = HTML::sanitize($_POST['countries_iso_code_2']);
        $countries_iso_code_3 = HTML::sanitize($_POST['countries_iso_code_3']);
        $address_format_id = HTML::sanitize($_POST['address_format_id']);

        $OSCOM_Db->save('countries', [
          'countries_name' => $countries_name,
          'countries_iso_code_2' => $countries_iso_code_2,
          'countries_iso_code_3' => $countries_iso_code_3,
          'address_format_id' => (int)$address_format_id
        ]);

        OSCOM::redirect(FILENAME_COUNTRIES);
        break;
      case 'save':
        $countries_id = HTML::sanitize($_GET['cID']);
        $countries_name = HTML::sanitize($_POST['countries_name']);
        $countries_iso_code_2 = HTML::sanitize($_POST['countries_iso_code_2']);
        $countries_iso_code_3 = HTML::sanitize($_POST['countries_iso_code_3']);
        $address_format_id = HTML::sanitize($_POST['address_format_id']);

        $OSCOM_Db->save('countries', [
          'countries_name' => $countries_name,
          'countries_iso_code_2' => $countries_iso_code_2,
          'countries_iso_code_3' => $countries_iso_code_3,
          'address_format_id' => (int)$address_format_id
        ], [
          'countries_id' => (int)$countries_id
        ]);

        OSCOM::redirect(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $countries_id);
        break;
      case 'deleteconfirm':
        $countries_id = HTML::sanitize($_GET['cID']);

        $OSCOM_Db->delete('countries', [
          'countries_id' => (int)$countries_id
        ]);

        OSCOM::redirect(FILENAME_COUNTRIES, 'page=' . $_GET['page']);
        break;
    }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_COUNTRY_NAME; ?></td>
                <td class="dataTableHeadingContent" align="center" colspan="2"><?php echo TABLE_HEADING_COUNTRY_CODES; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $Qcountries = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id from :table_countries order by countries_name limit :page_set_offset, :page_set_max_results');
  $Qcountries->setPageSet(MAX_DISPLAY_SEARCH_RESULTS);
  $Qcountries->execute();

  while ($Qcountries->fetch()) {
    if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ((int)$_GET['cID'] == $Qcountries->valueInt('countries_id')))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
      $cInfo = new objectInfo($Qcountries->toArray());
    }

    if (isset($cInfo) && is_object($cInfo) && ($Qcountries->valueInt('countries_id') === (int)$cInfo->countries_id)) {
      echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->countries_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $Qcountries->valueInt('countries_id')) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $Qcountries->value('countries_name'); ?></td>
                <td class="dataTableContent" align="center" width="40"><?php echo $Qcountries->value('countries_iso_code_2'); ?></td>
                <td class="dataTableContent" align="center" width="40"><?php echo $Qcountries->value('countries_iso_code_3'); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($cInfo) && is_object($cInfo) && ($Qcountries->valueInt('countries_id') === (int)$cInfo->countries_id) ) { echo HTML::image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . OSCOM::link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $Qcountries->valueInt('countries_id')) . '">' . HTML::image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="4"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $Qcountries->getPageSetLabel(TEXT_DISPLAY_NUMBER_OF_COUNTRIES); ?></td>
                    <td class="smallText" align="right"><?php echo $Qcountries->getPageSetLinks(); ?></td>
                  </tr>
<?php
  if (empty($action)) {
?>
                  <tr>
                    <td class="smallText" colspan="2" align="right"><?php echo HTML::button(IMAGE_NEW_COUNTRY, 'fa fa-plus', OSCOM::link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&action=new')); ?></td>
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
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_NEW_COUNTRY . '</strong>');

      $contents = array('form' => HTML::form('countries', OSCOM::link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&action=insert')));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_NAME . '<br />' . HTML::inputField('countries_name'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_CODE_2 . '<br />' . HTML::inputField('countries_iso_code_2'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_CODE_3 . '<br />' . HTML::inputField('countries_iso_code_3'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_ADDRESS_FORMAT . '<br />' . HTML::selectField('address_format_id', tep_get_address_formats()));
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(IMAGE_SAVE, 'fa fa-save', null, 'primary') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_COUNTRIES, 'page=' . $_GET['page'])));
      break;
    case 'edit':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_EDIT_COUNTRY . '</strong>');

      $contents = array('form' => HTML::form('countries', OSCOM::link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->countries_id . '&action=save')));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_NAME . '<br />' . HTML::inputField('countries_name', $cInfo->countries_name));
      $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_CODE_2 . '<br />' . HTML::inputField('countries_iso_code_2', $cInfo->countries_iso_code_2));
      $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_CODE_3 . '<br />' . HTML::inputField('countries_iso_code_3', $cInfo->countries_iso_code_3));
      $contents[] = array('text' => '<br />' . TEXT_INFO_ADDRESS_FORMAT . '<br />' . HTML::selectField('address_format_id', tep_get_address_formats(), $cInfo->address_format_id));
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(IMAGE_SAVE, 'fa fa-save', null, 'primary') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->countries_id)));
      break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_COUNTRY . '</strong>');

      $contents = array('form' => HTML::form('countries', OSCOM::link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->countries_id . '&action=deleteconfirm')));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br /><strong>' . $cInfo->countries_name . '</strong>');
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(IMAGE_DELETE, 'fa fa-trash', null, 'primary') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->countries_id)));
      break;
    default:
      if (is_object($cInfo)) {
        $heading[] = array('text' => '<strong>' . $cInfo->countries_name . '</strong>');

        $contents[] = array('align' => 'center', 'text' => HTML::button(IMAGE_EDIT, 'fa fa-edit', OSCOM::link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->countries_id . '&action=edit')) . HTML::button(IMAGE_DELETE, 'fa fa-trash', OSCOM::link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->countries_id . '&action=delete')));
        $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_NAME . '<br />' . $cInfo->countries_name);
        $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_CODE_2 . ' ' . $cInfo->countries_iso_code_2);
        $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_CODE_3 . ' ' . $cInfo->countries_iso_code_3);
        $contents[] = array('text' => '<br />' . TEXT_INFO_ADDRESS_FORMAT . ' ' . $cInfo->address_format_id);
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
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
