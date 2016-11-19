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
        $Qcategories = $OSCOM_Db->prepare('select c.categories_id as orig_category_id, cd.* from :table_categories c left join :table_categories_description cd on c.categories_id = cd.categories_id where cd.language_id = :language_id');
        $Qcategories->bindInt(':language_id', $OSCOM_Language->getId());
        $Qcategories->execute();

        while ($Qcategories->fetch()) {
          $cols = $Qcategories->toArray();

          $cols['categories_id'] = $cols['orig_category_id'];
          $cols['language_id'] = $insert_id;

          unset($cols['orig_category_id']);

          $OSCOM_Db->save('categories_description', $cols);
        }

// create additional products_description records
        $Qproducts = $OSCOM_Db->prepare('select p.products_id as orig_product_id, pd.* from :table_products p left join :table_products_description pd on p.products_id = pd.products_id where pd.language_id = :language_id');
        $Qproducts->bindInt(':language_id', $OSCOM_Language->getId());
        $Qproducts->execute();

        while ($Qproducts->fetch()) {
          $cols = $Qproducts->toArray();

          $cols['products_id'] = $cols['orig_product_id'];
          $cols['language_id'] = $insert_id;
          $cols['products_viewed'] = 0;

          unset($cols['orig_product_id']);

          $OSCOM_Db->save('products_description', $cols);
        }

// create additional products_options records
        $Qoptions = $OSCOM_Db->get('products_options', '*', [
          'language_id' => $OSCOM_Language->getId()
        ]);

        while ($Qoptions->fetch()) {
          $cols = $Qoptions->toArray();

          $cols['language_id'] = $insert_id;

          $OSCOM_Db->save('products_options', $cols);
        }

// create additional products_options_values records
        $Qvalues = $OSCOM_Db->get('products_options_values', '*', [
          'language_id' => $OSCOM_Language->getId()
        ]);

        while ($Qvalues->fetch()) {
          $cols = $Qvalues->toArray();

          $cols['language_id'] = $insert_id;

          $OSCOM_Db->save('products_options_values', $cols);
        }

// create additional manufacturers_info records
        $Qmanufacturers = $OSCOM_Db->prepare('select m.manufacturers_id as orig_manufacturer_id, mi.* from :table_manufacturers m left join :table_manufacturers_info mi on m.manufacturers_id = mi.manufacturers_id where mi.languages_id = :languages_id');
        $Qmanufacturers->bindInt(':languages_id', $OSCOM_Language->getId());
        $Qmanufacturers->execute();

        while ($Qmanufacturers->fetch()) {
          $cols = $Qmanufacturers->toArray();

          $cols['manufacturers_id'] = $cols['orig_manufacturer_id'];
          $cols['languages_id'] = $insert_id;

          unset($cols['orig_manufacturer_id']);
          unset($cols['url_clicks']);
          unset($cols['date_last_click']);

          $OSCOM_Db->save('manufacturers_info', $cols);
        }

// create additional orders_status records
        $Qstatus = $OSCOM_Db->get('orders_status', '*', [
          'language_id' => $OSCOM_Language->getId()
        ]);

        while ($Qstatus->fetch()) {
          $cols = $Qstatus->toArray();

          $cols['language_id'] = $insert_id;

          $OSCOM_Db->save('orders_status', $cols);
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
          $OSCOM_MessageStack->add(OSCOM::getDef('error_remove_default_language'), 'error');
        }
        break;
    }
  }

  $icons = [];

  foreach (glob(OSCOM::getConfig('dir_root', 'Shop') . 'public/third_party/flag-icon-css/flags/4x3/*.svg') as $file) {
    $code = basename($file, '.svg');

    $icons[] = [
      'id' => $code,
      'text' => $code
    ];
  }

  $directories = [];

  foreach (glob(OSCOM::getConfig('dir_root', 'Shop') . 'includes/languages/*', GLOB_ONLYDIR) as $dir) {
    $code = basename($dir);

    $directories[] = [
      'id' => $code,
      'text' => $code
    ];
  }

  foreach (glob(OSCOM::getConfig('dir_root', 'Admin') . 'includes/languages/*', GLOB_ONLYDIR) as $dir) {
    $code = basename($dir);

    if (array_search($code, array_column($directories, 'id')) === false) {
      $directories[] = [
        'id' => $code,
        'text' => $code
      ];
    }
  }

  uasort($directories, function ($a, $b) {
    if ($a['id'] == $b['id']) {
      return 0;
    }

    return ($a['id'] < $b['id']) ? -1 : 1;
  });

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
                <td class="dataTableHeadingContent"><?php echo OSCOM::getDef('table_heading_language_name'); ?></td>
                <td class="dataTableHeadingContent"><?php echo OSCOM::getDef('table_heading_language_code'); ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo OSCOM::getDef('table_heading_action'); ?>&nbsp;</td>
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
      echo '                <td class="dataTableContent"><strong>' . $Qlanguages->value('name') . ' (' . OSCOM::getDef('text_default') . ')</strong></td>' . "\n";
    } else {
      echo '                <td class="dataTableContent">' . $Qlanguages->value('name') . '</td>' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $Qlanguages->value('code'); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($lInfo) && is_object($lInfo) && ($Qlanguages->valueInt('languages_id') == (int)$lInfo->languages_id)) { echo HTML::image(OSCOM::linkImage('icon_arrow_right.gif')); } else { echo '<a href="' . OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $Qlanguages->valueInt('languages_id')) . '">' . HTML::image(OSCOM::linkImage('icon_info.gif'), OSCOM::getDef('image_icon_info')) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $Qlanguages->getPageSetLabel(OSCOM::getDef('text_display_number_of_languages')); ?></td>
                    <td class="smallText" align="right"><?php echo $Qlanguages->getPageSetLinks(); ?></td>
                  </tr>
<?php
  if (empty($action)) {
?>
                  <tr>
                    <td class="smallText" align="right" colspan="2"><?php echo HTML::button(OSCOM::getDef('image_new_language'), 'fa fa-plus', OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . (isset($lInfo) ? '&lID=' . $lInfo->languages_id : '') . '&action=new')); ?></td>
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
      $heading[] = array('text' => '<strong>' . OSCOM::getDef('text_info_heading_new_language') . '</strong>');

      $contents = array('form' => HTML::form('languages', OSCOM::link(FILENAME_LANGUAGES, 'action=insert')));
      $contents[] = array('text' => OSCOM::getDef('text_info_insert_intro'));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_language_name') . '<br />' . HTML::inputField('name'));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_language_code') . '<br />' . HTML::inputField('code'));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_language_image') . '<br />' . HTML::selectField('image', $icons));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_language_directory') . '<br />' . HTML::selectField('directory', $directories));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_language_sort_order') . '<br />' . HTML::inputField('sort_order'));
      $contents[] = array('text' => '<br />' . HTML::checkboxField('default') . ' ' . OSCOM::getDef('text_set_default'));
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::getDef('image_save'), 'fa fa-save') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $_GET['lID'])));
      break;
    case 'edit':
      $heading[] = array('text' => '<strong>' . OSCOM::getDef('text_info_heading_edit_language') . '</strong>');

      $contents = array('form' => HTML::form('languages', OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=save')));
      $contents[] = array('text' => OSCOM::getDef('text_info_edit_intro'));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_language_name') . '<br />' . HTML::inputField('name', $lInfo->name));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_language_code') . '<br />' . HTML::inputField('code', $lInfo->code));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_language_image') . '<br />' . HTML::selectField('image', $icons, $lInfo->image));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_language_directory') . '<br />' . HTML::selectField('directory', $directories, $lInfo->directory));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_language_sort_order') . '<br />' . HTML::inputField('sort_order', $lInfo->sort_order));
      if (DEFAULT_LANGUAGE != $lInfo->code) $contents[] = array('text' => '<br />' . HTML::checkboxField('default') . ' ' . OSCOM::getDef('text_set_default'));
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::getDef('image_save'), 'fa fa-save') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id)));
      break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . OSCOM::getDef('text_info_heading_delete_language') . '</strong>');

      $contents[] = array('text' => OSCOM::getDef('text_info_delete_intro'));
      $contents[] = array('text' => '<br /><strong>' . $lInfo->name . '</strong>');
      $contents[] = array('align' => 'center', 'text' => '<br />' . (($remove_language) ? HTML::button(OSCOM::getDef('image_delete'), 'fa fa-trash', OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=deleteconfirm')) : '') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id)));
      break;
    default:
      if (is_object($lInfo)) {
        $heading[] = array('text' => '<strong>' . $lInfo->name . '</strong>');

        $contents[] = array('align' => 'center', 'text' => HTML::button(OSCOM::getDef('image_edit'), 'fa fa-edit', OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=edit')) . HTML::button(OSCOM::getDef('image_delete'), 'fa fa-trash', OSCOM::link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=delete')) . HTML::button(OSCOM::getDef('image_details'), 'fa fa-info', OSCOM::link(FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $lInfo->directory)));
        $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_language_name') . ' ' . $lInfo->name);
        $contents[] = array('text' => OSCOM::getDef('text_info_language_code') . ' ' . $lInfo->code);
        $contents[] = array('text' => '<br />' . $OSCOM_Language->getImage($lInfo->code, 32, 24));
        $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_language_directory') . '<br />includes/languages/<strong>' . $lInfo->directory . '</strong>');
        $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_language_sort_order') . ' ' . $lInfo->sort_order);
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
