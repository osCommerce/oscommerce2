<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\Cache;
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
      case 'save':
        if (isset($_GET['mID'])) $manufacturers_id = HTML::sanitize($_GET['mID']);
        $manufacturers_name = HTML::sanitize($_POST['manufacturers_name']);

        $sql_data_array = array('manufacturers_name' => $manufacturers_name);

        if ($action == 'insert') {
          $insert_sql_data = array('date_added' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

          $OSCOM_Db->save('manufacturers', $sql_data_array);
          $manufacturers_id = $OSCOM_Db->lastInsertId();
        } elseif ($action == 'save') {
          $update_sql_data = array('last_modified' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $update_sql_data);

          $OSCOM_Db->save('manufacturers', $sql_data_array, [
            'manufacturers_id' => (int)$manufacturers_id
          ]);
        }

        $manufacturers_image = new upload('manufacturers_image');
        $manufacturers_image->set_destination(OSCOM::getConfig('dir_root', 'Shop') . 'images/');

        if ($manufacturers_image->parse() && $manufacturers_image->save()) {
          $OSCOM_Db->save('manufacturers', [
            'manufacturers_image' => $manufacturers_image->filename
          ], [
            'manufacturers_id' => (int)$manufacturers_id
          ]);
        }

        $languages = tep_get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
          $manufacturers_url_array = $_POST['manufacturers_url'];
          $language_id = $languages[$i]['id'];

          $sql_data_array = array('manufacturers_url' => HTML::sanitize($manufacturers_url_array[$language_id]));

          if ($action == 'insert') {
            $insert_sql_data = array('manufacturers_id' => $manufacturers_id,
                                     'languages_id' => $language_id);

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            $OSCOM_Db->save('manufacturers_info', $sql_data_array);
          } elseif ($action == 'save') {
            $OSCOM_Db->save('manufacturers_info', $sql_data_array, [
              'manufacturers_id' => (int)$manufacturers_id,
              'languages_id' => (int)$_SESSION['languages_id']
            ]);
          }
        }

        Cache::clear('manufacturers');

        OSCOM::redirect(FILENAME_MANUFACTURERS, 'page=' . $_GET['page'] . '&mID=' . $manufacturers_id);
        break;
      case 'deleteconfirm':
        $manufacturers_id = HTML::sanitize($_GET['mID']);

        if (isset($_POST['delete_image']) && ($_POST['delete_image'] == 'on')) {
          $Qmanufacturer = $OSCOM_Db->get('manufacturers', 'manufacturers_image', ['manufacturers_id' => (int)$manufacturers_id]);

          if (tep_not_null($Qmanufacturer->value('manufacturers_image'))) {
            $image_location = OSCOM::getConfig('dir_root', 'Shop') . 'images/' . $Qmanufacturer->value('manufacturers_image');

            if (is_file($image_location)) unlink($image_location);
          }
        }

        $OSCOM_Db->delete('manufacturers', ['manufacturers_id' => (int)$manufacturers_id]);
        $OSCOM_Db->delete('manufacturers_info', ['manufacturers_id' => (int)$manufacturers_id]);

        if (isset($_POST['delete_products']) && ($_POST['delete_products'] == 'on')) {
          $Qproducts = $OSCOM_Db->get('products', 'products_id', ['manufacturers_id' => (int)$manufacturers_id]);
          while ($Qproducts->fetch()) {
            tep_remove_product($Qproducts->value('products_id'));
          }
        } else {
          $OSCOM_Db->save('products', [
            'manufacturers_id' => ''
          ], [
            'manufacturers_id' => (int)$manufacturers_id
          ]);
        }

        Cache::clear('manufacturers');

        OSCOM::redirect(FILENAME_MANUFACTURERS, 'page=' . $_GET['page']);
        break;
    }
  }

  require($oscTemplate->getFile('template_top.php'));
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MANUFACTURERS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $Qmanufacturers = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS manufacturers_id, manufacturers_name, manufacturers_image, date_added, last_modified from :table_manufacturers order by manufacturers_name limit :page_set_offset, :page_set_max_results');
  $Qmanufacturers->setPageSet(MAX_DISPLAY_SEARCH_RESULTS);
  $Qmanufacturers->execute();

  while ($Qmanufacturers->fetch()) {
    if ((!isset($_GET['mID']) || (isset($_GET['mID']) && ((int)$_GET['mID'] === $Qmanufacturers->valueInt('manufacturers_id')))) && !isset($mInfo) && (substr($action, 0, 3) != 'new')) {
      $Qproducts = $OSCOM_Db->get('products', 'count(*) as products_count', ['manufacturers_id' => $Qmanufacturers->valueInt('manufacturers_id')]);

      $mInfo_array = array_merge($Qmanufacturers->toArray(), $Qproducts->toArray());
      $mInfo = new objectInfo($mInfo_array);
    }

    if (isset($mInfo) && is_object($mInfo) && ($Qmanufacturers->valueInt('manufacturers_id') === (int)$mInfo->manufacturers_id)) {
      echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_MANUFACTURERS, 'page=' . $_GET['page'] . '&mID=' . $Qmanufacturers->valueInt('manufacturers_id') . '&action=edit') . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_MANUFACTURERS, 'page=' . $_GET['page'] . '&mID=' . $Qmanufacturers->valueInt('manufacturers_id')) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $Qmanufacturers->value('manufacturers_name'); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($mInfo) && is_object($mInfo) && ($Qmanufacturers->valueInt('manufacturers_id') === (int)$mInfo->manufacturers_id)) { echo HTML::image(OSCOM::linkImage('icon_arrow_right.gif')); } else { echo '<a href="' . OSCOM::link(FILENAME_MANUFACTURERS, 'page=' . $_GET['page'] . '&mID=' . $Qmanufacturers->valueInt('manufacturers_id')) . '">' . HTML::image(OSCOM::linkImage('icon_info.gif'), IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $Qmanufacturers->getPageSetLabel(TEXT_DISPLAY_NUMBER_OF_MANUFACTURERS); ?></td>
                    <td class="smallText" align="right"><?php echo $Qmanufacturers->getPageSetLinks(); ?></td>
                  </tr>
                </table></td>
              </tr>
<?php
  if (empty($action)) {
?>
              <tr>
                <td align="right" colspan="2" class="smallText"><?php echo HTML::button(IMAGE_INSERT, 'fa fa-plus', OSCOM::link(FILENAME_MANUFACTURERS, 'page=' . $_GET['page'] . (isset($mInfo) ? '&mID=' . $mInfo->manufacturers_id : '') . '&action=new')); ?></td>
              </tr>
<?php
  }
?>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'new':
      $heading[] = array('text' => '<strong>' . TEXT_HEADING_NEW_MANUFACTURER . '</strong>');

      $contents = array('form' => HTML::form('manufacturers', OSCOM::link(FILENAME_MANUFACTURERS, 'action=insert', 'post', 'enctype="multipart/form-data"')));
      $contents[] = array('text' => TEXT_NEW_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_MANUFACTURERS_NAME . '<br />' . HTML::inputField('manufacturers_name'));
      $contents[] = array('text' => '<br />' . TEXT_MANUFACTURERS_IMAGE . '<br />' . HTML::fileField('manufacturers_image'));

      $manufacturer_inputs_string = '';
      $languages = tep_get_languages();
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        $manufacturer_inputs_string .= '<br />' . HTML::image(OSCOM::link('Shop/includes/languages/' . $languages[$i]['directory'] . '/images/' . $languages[$i]['image']), $languages[$i]['name']) . '&nbsp;' . HTML::inputField('manufacturers_url[' . $languages[$i]['id'] . ']');
      }

      $contents[] = array('text' => '<br />' . TEXT_MANUFACTURERS_URL . $manufacturer_inputs_string);
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(IMAGE_SAVE, 'fa fa-save') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_MANUFACTURERS, 'page=' . $_GET['page'] . '&mID=' . $_GET['mID'])));
      break;
    case 'edit':
      $heading[] = array('text' => '<strong>' . TEXT_HEADING_EDIT_MANUFACTURER . '</strong>');

      $contents = array('form' => HTML::form('manufacturers', OSCOM::link(FILENAME_MANUFACTURERS, 'page=' . $_GET['page'] . '&mID=' . $mInfo->manufacturers_id . '&action=save', 'post', 'enctype="multipart/form-data"')));
      $contents[] = array('text' => TEXT_EDIT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_MANUFACTURERS_NAME . '<br />' . HTML::inputField('manufacturers_name', $mInfo->manufacturers_name));
      $contents[] = array('text' => '<br />' . TEXT_MANUFACTURERS_IMAGE . '<br />' . HTML::fileField('manufacturers_image') . '<br />' . $mInfo->manufacturers_image);

      $manufacturer_inputs_string = '';
      $languages = tep_get_languages();
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        $manufacturer_inputs_string .= '<br />' . HTML::image(OSCOM::link('Shop/includes/languages/' . $languages[$i]['directory'] . '/images/' . $languages[$i]['image']), $languages[$i]['name']) . '&nbsp;' . HTML::inputField('manufacturers_url[' . $languages[$i]['id'] . ']', tep_get_manufacturer_url($mInfo->manufacturers_id, $languages[$i]['id']));
      }

      $contents[] = array('text' => '<br />' . TEXT_MANUFACTURERS_URL . $manufacturer_inputs_string);
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(IMAGE_SAVE, 'fa fa-save') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_MANUFACTURERS, 'page=' . $_GET['page'] . '&mID=' . $mInfo->manufacturers_id)));
      break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . TEXT_HEADING_DELETE_MANUFACTURER . '</strong>');

      $contents = array('form' => HTML::form('manufacturers', OSCOM::link(FILENAME_MANUFACTURERS, 'page=' . $_GET['page'] . '&mID=' . $mInfo->manufacturers_id . '&action=deleteconfirm')));
      $contents[] = array('text' => TEXT_DELETE_INTRO);
      $contents[] = array('text' => '<br /><strong>' . $mInfo->manufacturers_name . '</strong>');
      $contents[] = array('text' => '<br />' . HTML::checkboxField('delete_image', '', true) . ' ' . TEXT_DELETE_IMAGE);

      if ($mInfo->products_count > 0) {
        $contents[] = array('text' => '<br />' . HTML::checkboxField('delete_products') . ' ' . TEXT_DELETE_PRODUCTS);
        $contents[] = array('text' => '<br />' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $mInfo->products_count));
      }

      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(IMAGE_DELETE, 'fa fa-trash') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_MANUFACTURERS, 'page=' . $_GET['page'] . '&mID=' . $mInfo->manufacturers_id)));
      break;
    default:
      if (isset($mInfo) && is_object($mInfo)) {
        $heading[] = array('text' => '<strong>' . $mInfo->manufacturers_name . '</strong>');

        $contents[] = array('align' => 'center', 'text' => HTML::button(IMAGE_EDIT, 'fa fa-edit', OSCOM::link(FILENAME_MANUFACTURERS, 'page=' . $_GET['page'] . '&mID=' . $mInfo->manufacturers_id . '&action=edit')) . HTML::button(IMAGE_DELETE, 'fa fa-trash', OSCOM::link(FILENAME_MANUFACTURERS, 'page=' . $_GET['page'] . '&mID=' . $mInfo->manufacturers_id . '&action=delete')));
        $contents[] = array('text' => '<br />' . TEXT_DATE_ADDED . ' ' . tep_date_short($mInfo->date_added));
        if (tep_not_null($mInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . tep_date_short($mInfo->last_modified));
        $contents[] = array('text' => '<br />' . tep_info_image($mInfo->manufacturers_image, $mInfo->manufacturers_name));
        $contents[] = array('text' => '<br />' . TEXT_PRODUCTS . ' ' . $mInfo->products_count);
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
