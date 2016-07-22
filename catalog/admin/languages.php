<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

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
        $name = HTML::sanitize($_POST['name']);
        $code = HTML::sanitize(substr($_POST['code'], 0, 2));
        $image = HTML::sanitize($_POST['image']);
        $directory = HTML::sanitize($_POST['directory']);
        $sort_order = (int)HTML::sanitize($_POST['sort_order']);

        $OSCOM_Db->save('languages', [
          'name' => $name,
          'code' => $code,
          'image' => $image,
          'directory' => $directory,
          'sort_order' => $sort_order
        ]);

        $insert_id = $OSCOM_Db->lastInsertId();

// create additional categories_description records
        $Qcategories = $OSCOM_Db->prepare('select c.categories_id, cd.categories_name from :table_categories c left join :table_categories_description cd on c.categories_id = cd.categories_id where cd.language_id = :language_id');
        $Qcategories->bindInt(':language_id', $_SESSION['languages_id']);
        $Qcategories->execute();

        while ($Qcategories->fetch()) {
          $OSCOM_Db->save('categories_description', [
            'categories_id' => $Qcategories->valueInt('categories_id'),
            'language_id' => $insert_id,
            'categories_name' => $Qcategories->value('categories_name')
          ]);
        }

// create additional products_description records
        $Qproducts = $OSCOM_Db->prepare('select p.products_id, pd.products_name, pd.products_description, pd.products_url from :table_products p left join :table_products_description pd on p.products_id = pd.products_id where pd.language_id = :language_id');
        $Qproducts->bindInt(':language_id', $_SESSION['languages_id']);
        $Qproducts->execute();

        while ($Qproducts->fetch()) {
          $OSCOM_Db->save('products_description', [
            'products_id' => $Qproducts->valueInt('products_id'),
            'language_id' => $insert_id,
            'products_name' => $Qproducts->value('products_name'),
            'products_description' => $Qproducts->value('products_description'),
            'products_url' => $Qproducts->value('products_url')
          ]);
        }

// create additional products_options records
        $Qoptions = $OSCOM_Db->get('products_options', [
          'products_options_id',
          'products_options_name'
        ], [
          'language_id' => $_SESSION['languages_id']
        ]);

        while ($Qoptions->fetch()) {
          $OSCOM_Db->save('products_options', [
            'products_options_id' => $Qoptions->valueInt('products_options_id'),
            'language_id' => $insert_id,
            'products_options_name' => $Qoptions->value('products_options_name')
          ]);
        }

// create additional products_options_values records
        $Qvalues = $OSCOM_Db->get('products_options_values', [
          'products_options_values_id',
          'products_options_values_name'
        ], [
          'language_id' => $_SESSION['languages_id']
        ]);

        while ($Qvalues->fetch()) {
          $OSCOM_Db->save('products_options_values', [
            'products_options_values_id' => $Qvalues->valueInt('products_options_values_id'),
            'language_id' => $insert_id,
            'products_options_values_name' => $Qvalues->value('products_options_values_name')
          ]);
        }

// create additional manufacturers_info records
        $Qmanufacturers = $OSCOM_Db->prepare('select m.manufacturers_id, mi.manufacturers_url from :table_manufacturers m left join :table_manufacturers_info mi on m.manufacturers_id = mi.manufacturers_id where mi.languages_id = :languages_id');
        $Qmanufacturers->bindInt(':languages_id', $_SESSION['languages_id']);
        $Qmanufacturers->execute();

        while ($Qmanufacturers->fetch()) {
          $OSCOM_Db->save('manufacturers_info', [
            'manufacturers_id' => $Qmanufacturers->valueInt('manufacturers_id'),
            'languages_id' => $insert_id,
            'manufacturers_url' => $Qmanufacturers->value('manufacturers_url')
          ]);
        }

// create additional orders_status records
        $Qstatus = $OSCOM_Db->get('orders_status', [
          'orders_status_id',
          'orders_status_name'
        ], [
          'language_id' => $_SESSION['languages_id']
        ]);

        while ($Qstatus->fetch()) {
          $OSCOM_Db->save('orders_status', [
            'orders_status_id' => $Qstatus->valueInt('orders_status_id'),
            'language_id' => $insert_id,
            'orders_status_name' => $Qstatus->value('orders_status_name')
          ]);
        }

        if (isset($_POST['default']) && ($_POST['default'] == 'on')) {
          $OSCOM_Db->save('configuration', [
            'configuration_value' => $code
          ], [
            'configuration_key' => 'DEFAULT_LANGUAGE'
          ]);
        }

        OSCOM::redirect(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $insert_id);
        break;
      case 'save':
        $lID = HTML::sanitize($_GET['lID']);
        $name = HTML::sanitize($_POST['name']);
        $code = HTML::sanitize(substr($_POST['code'], 0, 2));
        $image = HTML::sanitize($_POST['image']);
        $directory = HTML::sanitize($_POST['directory']);
        $sort_order = (int)HTML::sanitize($_POST['sort_order']);

        $OSCOM_Db->save('languages', [
          'name' => $name,
          'code' => $code,
          'image' => $image,
          'directory' => $directory,
          'sort_order' => $sort_order
        ], [
          'languages_id' => (int)$lID
        ]);

        if (isset($_POST['default']) && ($_POST['default'] == 'on')) {
          $OSCOM_Db->save('configuration', [
            'configuration_value' => $code
          ], [
            'configuration_key' => 'DEFAULT_LANGUAGE'
          ]);
        }

        OSCOM::redirect(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $_GET['lID']);
        break;
      case 'deleteconfirm':
        $lID = HTML::sanitize($_GET['lID']);

        $Qlanguage = $OSCOM_Db->get('languages', 'languages_id', ['code' => DEFAULT_LANGUAGE]);

        if ($Qlanguage->valueInt('languages_id') === (int)$lID) {
          $OSCOM_Db->save('configuration', [
            'configuration_value' => ''
          ], [
            'configuration_key' => 'DEFAULT_CURRENCY'
          ]);
        }

        $OSCOM_Db->delete('categories_description', ['language_id' => $lID]);
        $OSCOM_Db->delete('products_description', ['language_id' => $lID]);
        $OSCOM_Db->delete('products_options', ['language_id' => $lID]);
        $OSCOM_Db->delete('products_options_values', ['language_id' => $lID]);
        $OSCOM_Db->delete('manufacturers_info', ['languages_id' => $lID]);
        $OSCOM_Db->delete('orders_status', ['language_id' => $lID]);
        $OSCOM_Db->delete('languages', ['languages_id' => $lID]);

        OSCOM::redirect(FILENAME_LANGUAGES, 'page=' . $_GET['page']);
        break;
      case 'delete':
        $lID = HTML::sanitize($_GET['lID']);

        $Qlanguage = $OSCOM_Db->get('languages', 'code', ['languages_id' => $lID]);

        $remove_language = true;
        if ($Qlanguage->value('code') == DEFAULT_LANGUAGE) {
          $remove_language = false;
          $messageStack->add(ERROR_REMOVE_DEFAULT_LANGUAGE, 'error');
        }
        break;
    }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LANGUAGE_NAME; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LANGUAGE_CODE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $Qlanguages = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS languages_id, name, code, image, directory, sort_order from :table_languages order by sort_order limit :page_set_offset, :page_set_max_results');
  $Qlanguages->setPageSet(MAX_DISPLAY_SEARCH_RESULTS);
  $Qlanguages->execute();

  while ($Qlanguages->fetch()) {
    if ((!isset($_GET['lID']) || (isset($_GET['lID']) && ((int)$_GET['lID'] === $Qlanguages->valueInt('languages_id')))) && !isset($lInfo) && (substr($action, 0, 3) != 'new')) {
      $lInfo = new objectInfo($Qlanguages->toArray());
    }

    if (isset($lInfo) && is_object($lInfo) && ($Qlanguages->valueInt('languages_id') === (int)$lInfo->languages_id) ) {
      echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $Qlanguages->valueInt('languages_id')) . '\'">' . "\n";
    }

    if (DEFAULT_LANGUAGE == $Qlanguages->value('code')) {
      echo '                <td class="dataTableContent"><strong>' . $Qlanguages->value('name') . ' (' . TEXT_DEFAULT . ')</strong></td>' . "\n";
    } else {
      echo '                <td class="dataTableContent">' . $Qlanguages->value('name') . '</td>' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $Qlanguages->value('code'); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($lInfo) && is_object($lInfo) && ($Qlanguages->valueInt('languages_id') == (int)$lInfo->languages_id)) { echo HTML::image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $Qlanguages->valueInt('languages_id')) . '">' . HTML::image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $Qlanguages->getPageSetLabel(TEXT_DISPLAY_NUMBER_OF_LANGUAGES); ?></td>
                    <td class="smallText" align="right"><?php echo $Qlanguages->getPageSetLinks(); ?></td>
                  </tr>
<?php
  if (empty($action)) {
?>
                  <tr>
                    <td class="smallText" align="right" colspan="2"><?php echo HTML::button(IMAGE_NEW_LANGUAGE, 'fa fa-plus', OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . (isset($lInfo) ? '&lID=' . $lInfo->languages_id : '') . '&action=new')); ?></td>
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
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_NEW_LANGUAGE . '</strong>');

      $contents = array('form' => HTML::form('languages', OSCOM::link(FILENAME_LANGUAGES, 'action=insert')));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_NAME . '<br />' . HTML::inputField('name'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_CODE . '<br />' . HTML::inputField('code'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_IMAGE . '<br />' . HTML::inputField('image', 'icon.gif'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_DIRECTORY . '<br />' . HTML::inputField('directory'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_SORT_ORDER . '<br />' . HTML::inputField('sort_order'));
      $contents[] = array('text' => '<br />' . HTML::checkboxField('default') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(IMAGE_SAVE, 'fa fa-save', null, 'primary') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $_GET['lID'])));
      break;
    case 'edit':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_EDIT_LANGUAGE . '</strong>');

      $contents = array('form' => HTML::form('languages', OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=save')));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_NAME . '<br />' . HTML::inputField('name', $lInfo->name));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_CODE . '<br />' . HTML::inputField('code', $lInfo->code));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_IMAGE . '<br />' . HTML::inputField('image', $lInfo->image));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_DIRECTORY . '<br />' . HTML::inputField('directory', $lInfo->directory));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_SORT_ORDER . '<br />' . HTML::inputField('sort_order', $lInfo->sort_order));
      if (DEFAULT_LANGUAGE != $lInfo->code) $contents[] = array('text' => '<br />' . HTML::checkboxField('default') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(IMAGE_SAVE, 'fa fa-save', null, 'primary') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id)));
      break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_LANGUAGE . '</strong>');

      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br /><strong>' . $lInfo->name . '</strong>');
      $contents[] = array('align' => 'center', 'text' => '<br />' . (($remove_language) ? HTML::button(IMAGE_DELETE, 'fa fa-trash', OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=deleteconfirm'), 'primary') : '') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id)));
      break;
    default:
      if (is_object($lInfo)) {
        $heading[] = array('text' => '<strong>' . $lInfo->name . '</strong>');

        $contents[] = array('align' => 'center', 'text' => HTML::button(IMAGE_EDIT, 'fa fa-edit', OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=edit')) . HTML::button(IMAGE_DELETE, 'fa fa-trash', OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=delete')) . HTML::button(IMAGE_DETAILS, 'fa fa-info', OSCOM::link(FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $lInfo->directory)));
        $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_NAME . ' ' . $lInfo->name);
        $contents[] = array('text' => TEXT_INFO_LANGUAGE_CODE . ' ' . $lInfo->code);
        $contents[] = array('text' => '<br />' . HTML::image(OSCOM::link('Shop/' . DIR_WS_LANGUAGES . $lInfo->directory . '/images/' . $lInfo->image, '', 'SSL'), $lInfo->name));
        $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_DIRECTORY . '<br />' . DIR_WS_CATALOG_LANGUAGES . '<strong>' . $lInfo->directory . '</strong>');
        $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_SORT_ORDER . ' ' . $lInfo->sort_order);
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
