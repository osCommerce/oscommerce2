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
                <td class="dataTableHeadingContent" align="center" colspan="2"><?php echo OSCOM::getDef('table_heading_country_codes'); ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo OSCOM::getDef('table_heading_action'); ?>&nbsp;</td>
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
                <td class="dataTableContent" align="right"><?php if (isset($cInfo) && is_object($cInfo) && ($Qcountries->valueInt('countries_id') === (int)$cInfo->countries_id) ) { echo HTML::image(OSCOM::linkImage('icon_arrow_right.gif'), ''); } else { echo '<a href="' . OSCOM::link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $Qcountries->valueInt('countries_id')) . '">' . HTML::image(OSCOM::linkImage('icon_info.gif'), OSCOM::getDef('image_icon_info')) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="4"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $Qcountries->getPageSetLabel(OSCOM::getDef('text_display_number_of_countries')); ?></td>
                    <td class="smallText" align="right"><?php echo $Qcountries->getPageSetLinks(); ?></td>
                  </tr>
<?php
  if (empty($action)) {
?>
                  <tr>
                    <td class="smallText" colspan="2" align="right"><?php echo HTML::button(OSCOM::getDef('image_new_country'), 'fa fa-plus', OSCOM::link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&action=new')); ?></td>
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
      $heading[] = array('text' => '<strong>' . OSCOM::getDef('text_info_heading_new_country') . '</strong>');

      $contents = array('form' => HTML::form('countries', OSCOM::link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&action=insert')));
      $contents[] = array('text' => OSCOM::getDef('text_info_insert_intro'));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_country_name') . '<br />' . HTML::inputField('countries_name'));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_country_code_2') . '<br />' . HTML::inputField('countries_iso_code_2'));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_country_code_3') . '<br />' . HTML::inputField('countries_iso_code_3'));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_address_format') . '<br />' . HTML::selectField('address_format_id', tep_get_address_formats()));
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::getDef('image_save'), 'fa fa-save') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_COUNTRIES, 'page=' . $_GET['page'])));
      break;
    case 'edit':
      $heading[] = array('text' => '<strong>' . OSCOM::getDef('text_info_heading_edit_country') . '</strong>');

      $contents = array('form' => HTML::form('countries', OSCOM::link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->countries_id . '&action=save')));
      $contents[] = array('text' => OSCOM::getDef('text_info_edit_intro'));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_country_name') . '<br />' . HTML::inputField('countries_name', $cInfo->countries_name));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_country_code_2') . '<br />' . HTML::inputField('countries_iso_code_2', $cInfo->countries_iso_code_2));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_country_code_3') . '<br />' . HTML::inputField('countries_iso_code_3', $cInfo->countries_iso_code_3));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_address_format') . '<br />' . HTML::selectField('address_format_id', tep_get_address_formats(), $cInfo->address_format_id));
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::getDef('image_save'), 'fa fa-save') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->countries_id)));
      break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . OSCOM::getDef('text_info_heading_delete_country') . '</strong>');

      $contents = array('form' => HTML::form('countries', OSCOM::link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->countries_id . '&action=deleteconfirm')));
      $contents[] = array('text' => OSCOM::getDef('text_info_delete_intro'));
      $contents[] = array('text' => '<br /><strong>' . $cInfo->countries_name . '</strong>');
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::getDef('image_delete'), 'fa fa-trash') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->countries_id)));
      break;
    default:
      if (is_object($cInfo)) {
        $heading[] = array('text' => '<strong>' . $cInfo->countries_name . '</strong>');

        $contents[] = array('align' => 'center', 'text' => HTML::button(OSCOM::getDef('image_edit'), 'fa fa-edit', OSCOM::link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->countries_id . '&action=edit')) . HTML::button(OSCOM::getDef('image_delete'), 'fa fa-trash', OSCOM::link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->countries_id . '&action=delete')));
        $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_country_name') . '<br />' . $cInfo->countries_name);
        $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_country_code_2') . ' ' . $cInfo->countries_iso_code_2);
        $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_country_code_3') . ' ' . $cInfo->countries_iso_code_3);
        $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_address_format') . ' ' . $cInfo->address_format_id);
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
