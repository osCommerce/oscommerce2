<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\Cache;
  use OSC\OM\DateTime;
  use OSC\OM\FileSystem;
  use OSC\OM\HTML;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  require('includes/application_top.php');

  $OSCOM_Hooks = Registry::get('Hooks');

  require('includes/classes/currencies.php');
  $currencies = new currencies();

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  $OSCOM_Hooks->call('Products', 'PreAction');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'setflag':
        if ( ($_GET['flag'] == '0') || ($_GET['flag'] == '1') ) {
          if (isset($_GET['pID'])) {
            tep_set_product_status($_GET['pID'], $_GET['flag']);
          }

          Cache::clear('categories');
          Cache::clear('products-also_purchased');
        }

        OSCOM::redirect(FILENAME_CATEGORIES, 'cPath=' . $_GET['cPath'] . '&pID=' . $_GET['pID']);
        break;
      case 'insert_category':
      case 'update_category':
        if (isset($_POST['categories_id'])) $categories_id = HTML::sanitize($_POST['categories_id']);
        $sort_order = HTML::sanitize($_POST['sort_order']);

        $sql_data_array = array('sort_order' => (int)$sort_order);

        if ($action == 'insert_category') {
          $insert_sql_data = array('parent_id' => $current_category_id,
                                   'date_added' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

          $OSCOM_Db->save('categories', $sql_data_array);

          $categories_id = $OSCOM_Db->lastInsertId();
        } elseif ($action == 'update_category') {
          $update_sql_data = array('last_modified' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $update_sql_data);

          $OSCOM_Db->save('categories', $sql_data_array, ['categories_id' => (int)$categories_id]);
        }

        $languages = tep_get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
          $categories_name_array = $_POST['categories_name'];

          $language_id = $languages[$i]['id'];

          $sql_data_array = array('categories_name' => HTML::sanitize($categories_name_array[$language_id]));

          if ($action == 'insert_category') {
            $insert_sql_data = array('categories_id' => $categories_id,
                                     'language_id' => $languages[$i]['id']);

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            $OSCOM_Db->save('categories_description', $sql_data_array);
          } elseif ($action == 'update_category') {
            $OSCOM_Db->save('categories_description', $sql_data_array, [
              'categories_id' => (int)$categories_id,
              'language_id' => (int)$languages[$i]['id']
            ]);
          }
        }

        $categories_image = new upload('categories_image');
        $categories_image->set_destination(OSCOM::getConfig('dir_root', 'Shop') . 'images/');

        if ($categories_image->parse() && $categories_image->save()) {
          $OSCOM_Db->save('categories', [
            'categories_image' => $categories_image->filename
          ], [
            'categories_id' => (int)$categories_id
          ]);
        }

        Cache::clear('categories');
        Cache::clear('products-also_purchased');

        OSCOM::redirect(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $categories_id);
        break;
      case 'delete_category_confirm':
        if (isset($_POST['categories_id'])) {
          $categories_id = HTML::sanitize($_POST['categories_id']);

          $categories = tep_get_category_tree($categories_id, '', '0', '', true);
          $products = array();
          $products_delete = array();

          for ($i=0, $n=sizeof($categories); $i<$n; $i++) {
            $Qproducts = $OSCOM_Db->get('products_to_categories', 'products_id', ['categories_id' => (int)$categories[$i]['id']]);

            while ($Qproducts->fetch()) {
              $products[$Qproducts->valueInt('products_id')]['categories'][] = $categories[$i]['id'];
            }
          }

          foreach ( $products as $key => $value ) {
            $category_ids = '';

            for ($i=0, $n=sizeof($value['categories']); $i<$n; $i++) {
              $category_ids .= "'" . (int)$value['categories'][$i] . "', ";
            }
            $category_ids = substr($category_ids, 0, -2);

            $Qcheck = $OSCOM_Db->prepare('select products_id from :table_products_to_categories where products_id = :products_id and categories_id not in (' . $category_ids . ') limit 1');
            $Qcheck->bindInt(':products_id', $key);
            $Qcheck->execute();

            if ($Qcheck->check() === false) {
              $products_delete[$key] = $key;
            }
          }

// removing categories can be a lengthy process
          tep_set_time_limit(0);
          for ($i=0, $n=sizeof($categories); $i<$n; $i++) {
            tep_remove_category($categories[$i]['id']);
          }

          foreach  ( array_keys ($products_delete) as $key ) {
            tep_remove_product($key);
          }
        }

        Cache::clear('categories');
        Cache::clear('products-also_purchased');

        OSCOM::redirect(FILENAME_CATEGORIES, 'cPath=' . $cPath);
        break;
      case 'delete_product_confirm':
        if (isset($_POST['products_id']) && isset($_POST['product_categories']) && is_array($_POST['product_categories'])) {
          $product_id = HTML::sanitize($_POST['products_id']);
          $product_categories = $_POST['product_categories'];

          for ($i=0, $n=sizeof($product_categories); $i<$n; $i++) {
            $OSCOM_Db->delete('products_to_categories', [
              'products_id' => (int)$product_id,
              'categories_id' => (int)$product_categories[$i]
            ]);
          }

          $Qcheck = $OSCOM_Db->get('products_to_categories', 'products_id', ['products_id' => (int)$product_id], null, 1);

          if ($Qcheck->fetch() === false) {
            tep_remove_product($product_id);
          }
        }

        Cache::clear('categories');
        Cache::clear('products-also_purchased');

        $OSCOM_Hooks->call('Products', 'ActionDelete');

        OSCOM::redirect(FILENAME_CATEGORIES, 'cPath=' . $cPath);
        break;
      case 'move_category_confirm':
        if (isset($_POST['categories_id']) && ($_POST['categories_id'] != $_POST['move_to_category_id'])) {
          $categories_id = HTML::sanitize($_POST['categories_id']);
          $new_parent_id = HTML::sanitize($_POST['move_to_category_id']);

          $path = explode('_', tep_get_generated_category_path_ids($new_parent_id));

          if (in_array($categories_id, $path)) {
            $OSCOM_MessageStack->add(OSCOM::getDef('error_cannot_move_category_to_parent'), 'error');

            OSCOM::redirect(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $categories_id);
          } else {
            $OSCOM_Db->save('categories', [
              'parent_id' => (int)$new_parent_id,
              'last_modified' => 'now()'
            ], [
              'categories_id' => (int)$categories_id
            ]);

            Cache::clear('categories');
            Cache::clear('products-also_purchased');

            OSCOM::redirect(FILENAME_CATEGORIES, 'cPath=' . $new_parent_id . '&cID=' . $categories_id);
          }
        }

        break;
      case 'move_product_confirm':
        $products_id = HTML::sanitize($_POST['products_id']);
        $new_parent_id = HTML::sanitize($_POST['move_to_category_id']);

        $Qcheck = $OSCOM_Db->get('products_to_categories', 'products_id', [
          'products_id' => (int)$products_id,
          'categories_id' => (int)$new_parent_id
        ], null, 1);

        if ($Qcheck->fetch() === false) {
          $OSCOM_Db->save('products_to_categories', [
            'categories_id' => (int)$new_parent_id
          ], [
            'products_id' => (int)$products_id,
            'categories_id' => (int)$current_category_id
          ]);
        }

        Cache::clear('categories');
        Cache::clear('products-also_purchased');

        $OSCOM_Hooks->call('Products', 'ActionMove');

        OSCOM::redirect(FILENAME_CATEGORIES, 'cPath=' . $new_parent_id . '&pID=' . $products_id);
        break;
      case 'insert_product':
      case 'update_product':
        if (isset($_GET['pID'])) $products_id = HTML::sanitize($_GET['pID']);
        $products_date_available = HTML::sanitize($_POST['products_date_available']);

        $products_date_available = (date('Y-m-d') < $products_date_available) ? $products_date_available : 'null';

        $sql_data_array = array('products_quantity' => (int)HTML::sanitize($_POST['products_quantity']),
                                'products_model' => HTML::sanitize($_POST['products_model']),
                                'products_price' => (float)HTML::sanitize($_POST['products_price']),
                                'products_date_available' => $products_date_available,
                                'products_weight' => (float)HTML::sanitize($_POST['products_weight']),
                                'products_status' => HTML::sanitize($_POST['products_status']),
                                'products_tax_class_id' => HTML::sanitize($_POST['products_tax_class_id']),
                                'manufacturers_id' => (int)HTML::sanitize($_POST['manufacturers_id']));

        $products_image = new upload('products_image');
        $products_image->set_destination(OSCOM::getConfig('dir_root', 'Shop') . 'images/');
        if ($products_image->parse() && $products_image->save()) {
          $sql_data_array['products_image'] = HTML::sanitize($products_image->filename);
        }

        if ($action == 'insert_product') {
          $insert_sql_data = array('products_date_added' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

          $OSCOM_Db->save('products', $sql_data_array);
          $products_id = $OSCOM_Db->lastInsertId();

          $OSCOM_Db->save('products_to_categories', [
            'products_id' => (int)$products_id,
            'categories_id' => (int)$current_category_id
          ]);
        } elseif ($action == 'update_product') {
          $update_sql_data = array('products_last_modified' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $update_sql_data);

          $OSCOM_Db->save('products', $sql_data_array, ['products_id' => (int)$products_id]);
        }

        $languages = tep_get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
          $language_id = $languages[$i]['id'];

          $sql_data_array = array('products_name' => HTML::sanitize($_POST['products_name'][$language_id]),
                                  'products_description' => $_POST['products_description'][$language_id],
                                  'products_url' => HTML::sanitize($_POST['products_url'][$language_id]));

          if ($action == 'insert_product') {
            $insert_sql_data = array('products_id' => $products_id,
                                     'language_id' => $language_id);

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            $OSCOM_Db->save('products_description', $sql_data_array);
          } elseif ($action == 'update_product') {
            $OSCOM_Db->save('products_description', $sql_data_array, [
              'products_id' => (int)$products_id,
              'language_id' => (int)$language_id
            ]);
          }
        }

        $pi_sort_order = 0;
        $piArray = array(0);

        foreach ($_FILES as $key => $value) {
// Update existing large product images
          if (preg_match('/^products_image_large_([0-9]+)$/', $key, $matches)) {
            $pi_sort_order++;

            $sql_data_array = array('htmlcontent' => $_POST['products_image_htmlcontent_' . $matches[1]],
                                    'sort_order' => $pi_sort_order);

            $t = new upload($key);
            $t->set_destination(OSCOM::getConfig('dir_root', 'Shop') . 'images/');
            if ($t->parse() && $t->save()) {
              $sql_data_array['image'] = HTML::sanitize($t->filename);
            }

            $OSCOM_Db->save('products_images', $sql_data_array, [
              'products_id' => (int)$products_id,
              'id' => (int)$matches[1]
            ]);

            $piArray[] = (int)$matches[1];
          } elseif (preg_match('/^products_image_large_new_([0-9]+)$/', $key, $matches)) {
// Insert new large product images
            $sql_data_array = array('products_id' => (int)$products_id,
                                    'htmlcontent' => $_POST['products_image_htmlcontent_new_' . $matches[1]]);

            $t = new upload($key);
            $t->set_destination(OSCOM::getConfig('dir_root', 'Shop') . 'images/');
            if ($t->parse() && $t->save()) {
              $pi_sort_order++;

              $sql_data_array['image'] = HTML::sanitize($t->filename);
              $sql_data_array['sort_order'] = $pi_sort_order;

              $OSCOM_Db->save('products_images', $sql_data_array);

              $piArray[] = $OSCOM_Db->lastInsertId();
            }
          }
        }

        $Qimages = $OSCOM_Db->prepare('select image from :table_products_images where products_id = :products_id and id not in (' . implode(', ', $piArray) . ')');
        $Qimages->bindInt(':products_id', $products_id);
        $Qimages->execute();

        if ($Qimages->fetch() !== false) {
          do {
            $Qcheck = $OSCOM_Db->get('products_images', 'count(*) as total', ['image' => $Qimages->value('image')]);

            if ($Qcheck->valueInt('total') < 2) {
              if (is_file(OSCOM::getConfig('dir_root', 'Shop') . 'images/' . $Qimage->value('image'))) {
                unlink(OSCOM::getConfig('dir_root', 'Shop') . 'images/' . $Qimage->value('image'));
              }
            }
          } while ($Qimages->fetch());

          $Qdel = $OSCOM_Db->prepare('delete from :table_products_images where products_id = :products_id and id not in (' . implode(', ', $piArray) . ')');
          $Qdel->bindInt(':products_id', $products_id);
          $Qdel->execute();
        }

        Cache::clear('categories');
        Cache::clear('products-also_purchased');

        $OSCOM_Hooks->call('Products', 'ActionSave');

        OSCOM::redirect(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $products_id);
        break;
      case 'copy_to_confirm':
        if (isset($_POST['products_id']) && isset($_POST['categories_id'])) {
          $products_id = HTML::sanitize($_POST['products_id']);
          $categories_id = HTML::sanitize($_POST['categories_id']);

          if ($_POST['copy_as'] == 'link') {
            if ($categories_id != $current_category_id) {
              $Qcheck = $OSCOM_Db->get('products_to_categories', 'products_id', [
                'products_id' => (int)$products_id,
                'categories_id' => (int)$categories_id
              ], null, 1);

              if ($Qcheck->fetch() === false) {
                $OSCOM_Db->save('products_to_categories', [
                  'products_id' => (int)$products_id,
                  'categories_id' => (int)$categories_id
                ]);
              }
            } else {
              $OSCOM_MessageStack->add(OSCOM::getDef('error_cannot_link_to_same_category'), 'error');
            }
          } elseif ($_POST['copy_as'] == 'duplicate') {
            $Qproduct = $OSCOM_Db->get('products', '*', ['products_id' => (int)$products_id]);

            $OSCOM_Db->save('products', [
              'products_quantity' => $Qproduct->valueInt('products_quantity'),
              'products_model' => $Qproduct->value('products_model'),
              'products_image' => $Qproduct->value('products_image'),
              'products_price' => $Qproduct->value('products_price'),
              'products_date_added' => 'now()',
              'products_date_available' => $Qproduct->hasValue('products_date_available') ? $Qproduct->value('products_date_available') : null,
              'products_weight' => $Qproduct->value('products_weight'),
              'products_status' => 0,
              'products_tax_class_id' => $Qproduct->valueInt('products_tax_class_id'),
              'manufacturers_id' => $Qproduct->valueInt('manufacturers_id')
            ]);
            $dup_products_id = $OSCOM_Db->lastInsertId();

            $Qdesc = $OSCOM_Db->get('products_description', '*', ['products_id' => (int)$products_id]);

            while ($Qdesc->fetch()) {
              $OSCOM_Db->save('products_description', [
                'products_id' => (int)$dup_products_id,
                'language_id' => $Qdesc->valueInt('language_id'),
                'products_name' => $Qdesc->value('products_name'),
                'products_description' => $Qdesc->value('products_description'),
                'products_url' => $Qdesc->value('products_url'),
                'products_viewed' => 0
              ]);
            }

            $Qimages = $OSCOM_Db->get('products_images', '*', ['products_id' => (int)$products_id]);

            while ($Qimages->fetch()) {
              $OSCOM_Db->save('products_images', [
                'products_id' => (int)$dup_products_id,
                'image' => $Qimages->value('image'),
                'htmlcontent' => $Qimages->value('htmlcontent'),
                'sort_order' => $Qimages->valueInt('sort_order')
              ]);
            }

            $OSCOM_Db->save('products_to_categories', [
              'products_id' => (int)$dup_products_id,
              'categories_id' => (int)$categories_id
            ]);

            $products_id = $dup_products_id;
          }

          Cache::clear('categories');
          Cache::clear('products-also_purchased');
        }

        $OSCOM_Hooks->call('Products', 'ActionCopy');

        OSCOM::redirect(FILENAME_CATEGORIES, 'cPath=' . $categories_id . '&pID=' . $products_id);
        break;
    }
  }

// check if the catalog image directory exists
  if (is_dir(OSCOM::getConfig('dir_root', 'Shop') . 'images/')) {
    if (!FileSystem::isWritable(OSCOM::getConfig('dir_root', 'Shop') . 'images/')) {
      $OSCOM_MessageStack->add(OSCOM::getDef('error_catalog_image_directory_not_writeable', [
        'images_path' => OSCOM::getConfig('dir_root', 'Shop') . 'images/'
      ]), 'error');
    }
  } else {
    $OSCOM_MessageStack->add(OSCOM::getDef('error_catalog_image_directory_does_not_exist', [
      'images_path' => OSCOM::getConfig('dir_root', 'Shop') . 'images/'
    ]), 'error');
  }

  $cPath_back = '';
  if (sizeof($cPath_array) > 0) {
    for ($i=0, $n=sizeof($cPath_array)-1; $i<$n; $i++) {
      if (empty($cPath_back)) {
        $cPath_back .= $cPath_array[$i];
      } else {
        $cPath_back .= '_' . $cPath_array[$i];
      }
    }
  }

  $cPath_back = (tep_not_null($cPath_back)) ? 'cPath=' . $cPath_back . '&' : '';

  $show_listing = true;

  require($oscTemplate->getFile('template_top.php'));

  if (empty($action)) {
?>

<div class="pull-right">
  <?= ((sizeof($cPath_array) > 0) ? HTML::button(OSCOM::getDef('image_back'), 'fa fa-chevron-left', OSCOM::link(FILENAME_CATEGORIES, $cPath_back . 'cID=' . $current_category_id), null, 'btn-link') : '') .
      HTML::button(OSCOM::getDef('image_new_category'), 'fa fa-plus', OSCOM::link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&action=new_category'), null, 'btn-info') .
      HTML::button(OSCOM::getDef('image_new_product'), 'fa fa-plus', OSCOM::link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&action=new_product'), null, 'btn-info'); ?>
</div>

<?php
  }
?>

<h2><i class="fa fa-th"></i> <a href="<?= OSCOM::link('categories.php'); ?>"><?= OSCOM::getDef('heading_title'); ?></a></h2>

<?php
  if (empty($action)) {
    echo HTML::form('search', OSCOM::link(FILENAME_CATEGORIES), 'get', 'class="form-inline"', ['session_id' => true]) .
         HTML::inputField('search', null, 'placeholder="' . OSCOM::getDef('heading_title_search') . '"') .
         HTML::selectField('cPath', tep_get_category_tree(), $current_category_id, 'onchange="this.form.submit();"') .
         '</form>';
  }

  if (!empty($action)) {
    if ($action == 'new_product') {
      $show_listing = false;

      $parameters = array('products_name' => '',
                          'products_description' => '',
                          'products_url' => '',
                          'products_id' => '',
                          'products_quantity' => '',
                          'products_model' => '',
                          'products_image' => '',
                          'products_larger_images' => array(),
                          'products_price' => '',
                          'products_weight' => '',
                          'products_date_added' => '',
                          'products_last_modified' => '',
                          'products_date_available' => '',
                          'products_status' => '',
                          'products_tax_class_id' => '',
                          'manufacturers_id' => '');

      $pInfo = new objectInfo($parameters);

      if (isset($_GET['pID']) && empty($_POST)) {
        $Qproduct = $OSCOM_Db->prepare('select pd.products_name, pd.products_description, pd.products_url, p.products_id, p.products_quantity, p.products_model, p.products_image, p.products_price, p.products_weight, p.products_date_added, p.products_last_modified, date_format(p.products_date_available, "%Y-%m-%d") as products_date_available, p.products_status, p.products_tax_class_id, p.manufacturers_id from :table_products p, :table_products_description pd where p.products_id = :products_id and p.products_id = pd.products_id and pd.language_id = :language_id');
        $Qproduct->bindInt(':products_id', $_GET['pID']);
        $Qproduct->bindInt(':language_id', $OSCOM_Language->getId());
        $Qproduct->execute();

        $pInfo->objectInfo($Qproduct->toArray());

        $Qimages = $OSCOM_Db->get('products_images', [
          'id',
          'image',
          'htmlcontent',
          'sort_order'
        ], [
          'products_id' => $Qproduct->valueInt('products_id')
        ],
        'sort_order');

        while ($Qimages->fetch()) {
          $pInfo->products_larger_images[] = [
            'id' => $Qimages->valueInt('id'),
            'image' => $Qimages->value('image'),
            'htmlcontent' => $Qimages->value('htmlcontent'),
            'sort_order' => $Qimages->valueInt('sort_order')
          ];
        }
      }

      $manufacturers_array = array(array('id' => '', 'text' => OSCOM::getDef('text_none')));

      $Qmanufacturers = $OSCOM_Db->get('manufacturers', [
        'manufacturers_id',
        'manufacturers_name'
      ], null, 'manufacturers_name');

      while ($Qmanufacturers->fetch()) {
        $manufacturers_array[] = [
          'id' => $Qmanufacturers->valueInt('manufacturers_id'),
          'text' => $Qmanufacturers->value('manufacturers_name')
        ];
      }

      $tax_class_array = array(array('id' => '0', 'text' => OSCOM::getDef('text_none')));

      $Qtax = $OSCOM_Db->get('tax_class', [
        'tax_class_id',
        'tax_class_title'
      ], null, 'tax_class_title');

      while ($Qtax->fetch()) {
        $tax_class_array[] = [
          'id' => $Qtax->valueInt('tax_class_id'),
          'text' => $Qtax->value('tax_class_title')
        ];
      }

      $languages = tep_get_languages();

      if (!isset($pInfo->products_status)) $pInfo->products_status = '1';
      switch ($pInfo->products_status) {
        case '0': $in_status = false; $out_status = true; break;
        case '1':
        default: $in_status = true; $out_status = false;
      }

      $form_action = (isset($_GET['pID'])) ? 'update_product' : 'insert_product';
?>
<script type="text/javascript"><!--
var tax_rates = new Array();
<?php
    for ($i=0, $n=sizeof($tax_class_array); $i<$n; $i++) {
      if ($tax_class_array[$i]['id'] > 0) {
        echo 'tax_rates["' . $tax_class_array[$i]['id'] . '"] = ' . tep_get_tax_rate_value($tax_class_array[$i]['id']) . ';' . "\n";
      }
    }
?>

function doRound(x, places) {
  return Math.round(x * Math.pow(10, places)) / Math.pow(10, places);
}

function getTaxRate() {
  var selected_value = document.forms["new_product"].products_tax_class_id.selectedIndex;
  var parameterVal = document.forms["new_product"].products_tax_class_id[selected_value].value;

  if ( (parameterVal > 0) && (tax_rates[parameterVal] > 0) ) {
    return tax_rates[parameterVal];
  } else {
    return 0;
  }
}

function updateGross() {
  var taxRate = getTaxRate();
  var grossValue = document.forms["new_product"].products_price.value;

  if (taxRate > 0) {
    grossValue = grossValue * ((taxRate / 100) + 1);
  }

  document.forms["new_product"].products_price_gross.value = doRound(grossValue, 4);
}

function updateNet() {
  var taxRate = getTaxRate();
  var netValue = document.forms["new_product"].products_price_gross.value;

  if (taxRate > 0) {
    netValue = netValue / ((taxRate / 100) + 1);
  }

  document.forms["new_product"].products_price.value = doRound(netValue, 4);
}
//--></script>

<?php echo HTML::form('new_product', OSCOM::link(FILENAME_CATEGORIES, 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . '&action=' . $form_action), 'post', 'enctype="multipart/form-data"'); ?>

<h1 class="pageHeading"><?php echo OSCOM::getDef('text_new_product', ['generated_category_path' => tep_output_generated_category_path($current_category_id)]); ?></h1>

<div id="productTabs">
  <ul id="productTabsMain" class="nav nav-tabs">
    <li class="active"><a data-target="#section_general_content" data-toggle="tab"><?= OSCOM::getDef('section_heading_general'); ?></a></li>
    <li><a data-target="#section_data_content" data-toggle="tab"><?= OSCOM::getDef('section_heading_data'); ?></a></li>
    <li><a data-target="#section_images_content" data-toggle="tab"><?= OSCOM::getDef('section_heading_images'); ?></a></li>
  </ul>

  <div class="tab-content">
    <div id="section_general_content" class="tab-pane active">
      <div class="panel panel-primary oscom-panel">
        <div class="panel-body">
          <div class="container-fluid">
            <div class="row">
              <div id="productLanguageTabs">
                <ul class="nav nav-tabs">

<?php
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        echo '<li ' . ($i === 0 ? 'class="active"' : '') . '><a data-target="#section_general_content_' . $languages[$i]['directory'] . '" data-toggle="tab">' . $OSCOM_Language->getImage($languages[$i]['code']) . '&nbsp;' . $languages[$i]['name'] . '</a></li>';
      }
?>

                </ul>

                <div class="tab-content">

<?php
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>

                  <div id="section_general_content_<?php echo $languages[$i]['directory']; ?>" class="tab-pane <?= ($i === 0 ? 'active' : ''); ?>">
                    <div class="panel panel-info oscom-panel">
                      <div class="panel-body">
                        <div class="container-fluid">
                          <div class="row">
                            <?= OSCOM::getDef('text_products_name') . '<br />' . HTML::inputField('products_name[' . $languages[$i]['id'] . ']', (empty($pInfo->products_id) ? '' : tep_get_products_name($pInfo->products_id, $languages[$i]['id']))); ?>
                          </div>

                          <div class="row">
                            <?= OSCOM::getDef('text_products_description') . '<br />' . HTML::textareaField('products_description[' . $languages[$i]['id'] . ']', '70', '15', (empty($pInfo->products_id) ? '' : tep_get_products_description($pInfo->products_id, $languages[$i]['id']))); ?>
                          </div>

                          <div class="row">
                            <?= OSCOM::getDef('text_products_url') . ' <small>' . OSCOM::getDef('text_products_url_without_http') . '</small><br />' . HTML::inputField('products_url[' . $languages[$i]['id'] . ']', (isset($products_url[$languages[$i]['id']]) ? stripslashes($products_url[$languages[$i]['id']]) : tep_get_products_url($pInfo->products_id, $languages[$i]['id']))); ?>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

<?php
      }
?>

                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div id="section_data_content" class="tab-pane">
      <div class="panel panel-primary oscom-panel">
        <div class="panel-body">
          <div class="container-fluid">
            <div class="row">
              <?= OSCOM::getDef('text_products_status') . '<br />' . HTML::radioField('products_status', '1', $in_status) . '&nbsp;' . OSCOM::getDef('text_product_available') . '&nbsp;' . HTML::radioField('products_status', '0', $out_status) . '&nbsp;' . OSCOM::getDef('text_product_not_available'); ?>
            </div>

            <div class="row">
              <?= OSCOM::getDef('text_products_date_available') . '<br />' . HTML::inputField('products_date_available', $pInfo->products_date_available, 'id="products_date_available"', 'date'); ?>
            </div>

            <div class="row">
              <?= OSCOM::getDef('text_products_manufacturer') . '<br />' . HTML::selectField('manufacturers_id', $manufacturers_array, $pInfo->manufacturers_id); ?>
            </div>

            <div class="row">
              <?= OSCOM::getDef('text_products_tax_class') . '<br />' . HTML::selectField('products_tax_class_id', $tax_class_array, $pInfo->products_tax_class_id, 'onchange="updateGross()"'); ?>
            </div>

            <div class="row">
              <?= OSCOM::getDef('text_products_price_net') . '<br />' . HTML::inputField('products_price', $pInfo->products_price, 'onkeyup="updateGross()"'); ?>
            </div>

            <div class="row">
              <?= OSCOM::getDef('text_products_price_gross') . '<br />' . HTML::inputField('products_price_gross', $pInfo->products_price, 'onkeyup="updateNet()"'); ?>
            </div>

            <div class="row">
              <?= OSCOM::getDef('text_products_quantity') . '<br />' . HTML::inputField('products_quantity', $pInfo->products_quantity); ?>
            </div>

            <div class="row">
              <?= OSCOM::getDef('text_products_model') . '<br />' . HTML::inputField('products_model', $pInfo->products_model); ?>
            </div>

            <div class="row">
              <?= OSCOM::getDef('text_products_weight') . HTML::inputField('products_weight', $pInfo->products_weight); ?>
            </div>
          </div>
        </div>
      </div>

<script type="text/javascript"><!--
updateGross();
//--></script>

    </div>

    <div id="section_images_content" class="tab-pane">
      <div class="panel panel-primary oscom-panel">
        <div class="panel-body">
          <div class="container-fluid">
            <div class="row bg-info" style="padding: 10px;">
              <?=
                OSCOM::getDef('text_products_main_image') . ' <small>(' . SMALL_IMAGE_WIDTH . ' x ' . SMALL_IMAGE_HEIGHT . 'px)</small><br />' .
                HTML::fileField('products_image') .
                (tep_not_null($pInfo->products_image) ? '<br /><a href="' . OSCOM::linkImage('Shop/' . $pInfo->products_image) . '" target="_blank">' . $pInfo->products_image . '</a>' : '');
              ?>
            </div>

            <div class="row">
              <ul id="piList"></ul>

              <a class="linkHandle" data-action="addNewPiForm"><i class="fa fa-plus"></i>&nbsp;<?= OSCOM::getDef('text_products_add_large_image'); ?></a>
            </div>
          </div>
        </div>
      </div>

<script id="templateLargeImage" type="x-tmpl-mustache">
<li id="piId{{counter}}" class="bg-warning">
  <div class="piActions pull-right">
    <a class="linkHandle" data-piid="{{counter}}" data-action="showPiDelConfirm" data-state="active"><i class="fa fa-trash" title="<?= OSCOM::getDef('image_delete'); ?>"></i></a>
    <a class="sortHandle" data-state="active"><i class="fa fa-arrows-v" title="<?= OSCOM::getDef('image_move'); ?>"></i></a>
    <a class="linkHandle" data-piid="{{counter}}" data-action="undoDelete" data-state="inactive"><i class="fa fa-undo" title="<?= OSCOM::getDef('image_undo'); ?>"></i></a>
  </div>
  <strong><?= OSCOM::getDef('text_products_large_image'); ?></strong><br />
  <?= HTML::fileField('{{input_file_name}}'); ?><br />
  {{#image}}<a href="<?= OSCOM::linkImage('Shop/'); ?>{{image}}" target="_blank">{{image}}</a><br /><br />{{/image}}
  <?= OSCOM::getDef('text_products_large_image_html_content'); ?><br />
  <?= HTML::textareaField('{{input_html_content_name}}', '70', '3', '{{html_content}}', null, false); ?>
</li>
</script>

<div class="modal" tabindex="-1" role="dialog" id="piDelConfirm">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

        <h4 class="modal-title"><?= OSCOM::getDef('text_products_large_image_delete_title'); ?></h4>
      </div>

      <div class="modal-body">
        <p><?= OSCOM::getDef('text_products_large_image_confirm_delete'); ?></p>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-danger" id="piDelConfirmButtonDelete"><?= OSCOM::getDef('image_delete'); ?></button>
        <button type="button" class="btn btn-link" data-dismiss="modal"><?= OSCOM::getDef('image_cancel'); ?></button>
      </div>
    </div>
  </div>
</div>

<style type="text/css">
#piList { list-style-type: none; margin: 0; padding: 0; }
#piList li { margin: 15px 0; padding: 10px; }
</style>

<script>
$(function() {
  var templateLargeImage = $('#templateLargeImage').html();
  Mustache.parse(templateLargeImage);

<?php
    $pi_array = [];

    foreach ($pInfo->products_larger_images as $pi) {
      $pi_array[] = [
        'counter' => count($pi_array) + 1,
        'input_file_name' => 'products_image_large_' . $pi['id'],
        'input_html_content_name' => 'products_image_htmlcontent_' . $pi['id'],
        'image' => $pi['image'],
        'html_content' => $pi['htmlcontent']
      ];
    }

    echo '  var piArray = ' . json_encode($pi_array) . ';';
?>

  $.each(piArray, function(k, v) {
    $('#piList').append(Mustache.render(templateLargeImage, v));
  });

  $('#piList .piActions a[data-state="inactive"]').hide();

  Sortable.create($('#piList')[0], {
    handle: '.sortHandle'
  });

  $('#section_images_content a[data-action="addNewPiForm"]').on('click', function() {
    var piSize = $('#piList li').length + 1;

    var data = {
      counter: piSize,
      input_file_name: 'products_image_large_new_' + piSize,
      input_html_content_name: 'products_image_htmlcontent_new_' + piSize
    };

    $('#piList').append(Mustache.render(templateLargeImage, data));

    $('#piId' + piSize + ' .piActions a[data-state="inactive"]').hide();
  });

  $('#section_images_content').on('click', '#piList li a[data-action="showPiDelConfirm"]', function() {
    $('#piDelConfirm').data('piid', $(this).data('piid'));

    $('#piDelConfirm').modal('show');
  });

  $('#section_images_content').on('click', '#piList li a[data-action="undoDelete"]', function() {
    $('#piId' + $(this).data('piid') + ' .piActions a[data-state="inactive"]').hide();
    $('#piId' + $(this).data('piid') + ' .piActions a[data-state="active"]').show();
    $('#piId' + $(this).data('piid') + ' :input').prop('disabled', false);
    $('#piId' + $(this).data('piid')).removeClass('bg-danger').addClass('bg-warning');
  });

  $('#piDelConfirmButtonDelete').on('click', function() {
    $('#piId' + $('#piDelConfirm').data('piid')).removeClass('bg-warning').addClass('bg-danger');
    $('#piId' + $('#piDelConfirm').data('piid') + ' :input').prop('disabled', true);
    $('#piId' + $('#piDelConfirm').data('piid') + ' .piActions a[data-state="active"]').hide();
    $('#piId' + $('#piDelConfirm').data('piid') + ' .piActions a[data-state="inactive"]').show();

    $('#piDelConfirm').modal('hide');
  });
});
</script>

    </div>
  </div>
</div>

<div style="padding-top: 15px;">
  <?=
    HTML::hiddenField('products_date_added', (tep_not_null($pInfo->products_date_added) ? $pInfo->products_date_added : date('Y-m-d'))) .
    HTML::button(OSCOM::getDef('image_save'), 'fa fa-save', null, null, 'btn-success') .
    HTML::button(OSCOM::getDef('image_cancel'), null, OSCOM::link(FILENAME_CATEGORIES, 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '')), null, 'btn-link');
  ?>
</div>

<?= $OSCOM_Hooks->output('Products', 'Page', null, 'display'); ?>

</form>

<?php
    } elseif ($action == 'new_product_preview') {
      $show_listing = false;

      $Qproduct = $OSCOM_Db->get([
        'products p',
        'products_description pd'
      ], [
        'p.products_id',
        'pd.language_id',
        'pd.products_name',
        'pd.products_description',
        'pd.products_url',
        'p.products_quantity',
        'p.products_model',
        'p.products_image',
        'p.products_price',
        'p.products_weight',
        'p.products_date_added',
        'p.products_last_modified',
        'p.products_date_available',
        'p.products_status',
        'p.manufacturers_id'
      ], [
        'p.products_id' => [
          'val' => (int)$_GET['pID'],
          'rel' => 'pd.products_id'
        ],
      ]);

      $pInfo = new objectInfo($Qproduct->toArray());
      $products_image_name = $pInfo->products_image;

      $languages = tep_get_languages();
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        $pInfo->products_name = tep_get_products_name($pInfo->products_id, $languages[$i]['id']);
        $pInfo->products_description = tep_get_products_description($pInfo->products_id, $languages[$i]['id']);
        $pInfo->products_url = tep_get_products_url($pInfo->products_id, $languages[$i]['id']);
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo $OSCOM_Language->getImage($languages[$i]['code']) . '&nbsp;' . $pInfo->products_name; ?></td>
            <td class="pageHeading" align="right"><?php echo $currencies->format($pInfo->products_price); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td class="main"><?php echo HTML::image(OSCOM::linkImage('Shop/' . $products_image_name), $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="right" hspace="5" vspace="5"') . $pInfo->products_description; ?></td>
      </tr>
<?php
        if ($pInfo->products_url) {
?>
      <tr>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td class="main"><?php echo OSCOM::getDef('text_product_more_information', ['products_url' => $pInfo->products_url]); ?></td>
      </tr>
<?php
        }
?>
      <tr>
        <td>&nbsp;</td>
      </tr>
<?php
        if ($pInfo->products_date_available > date('Y-m-d')) {
?>
      <tr>
        <td align="center" class="smallText"><?php echo OSCOM::getDef('text_product_date_available', ['products_date_available' => DateTime::toLong($pInfo->products_date_available)]); ?></td>
      </tr>
<?php
        } else {
?>
      <tr>
        <td align="center" class="smallText"><?php echo OSCOM::getDef('text_product_date_added', ['products_date_added' => DateTime::toLong($pInfo->products_date_added)]); ?></td>
      </tr>
<?php
        }
?>
      <tr>
        <td>&nbsp;</td>
      </tr>
<?php
      }

      if (isset($_GET['origin'])) {
        $pos_params = strpos($_GET['origin'], '?', 0);
        if ($pos_params != false) {
          $back_url = substr($_GET['origin'], 0, $pos_params);
          $back_url_params = substr($_GET['origin'], $pos_params + 1);
        } else {
          $back_url = $_GET['origin'];
          $back_url_params = '';
        }
      } else {
        $back_url = FILENAME_CATEGORIES;
        $back_url_params = 'cPath=' . $cPath . '&pID=' . $pInfo->products_id;
      }
?>
      <tr>
        <td align="right" class="smallText"><?php echo HTML::button(OSCOM::getDef('image_back'), 'fa fa-chevron-left', OSCOM::link($back_url, $back_url_params)); ?></td>
      </tr>
    </table>

<?php
    } else {
      $heading = $contents = [];

      if (isset($_GET['cID']) && is_numeric($_GET['cID']) && ($_GET['cID'] > 0)) {
        $Qcategory = $OSCOM_Db->get([
          'categories c',
          'categories_description cd'
        ], [
          'c.categories_id',
          'cd.categories_name',
          'c.categories_image',
          'c.parent_id',
          'c.sort_order',
          'c.date_added',
          'c.last_modified'
        ], [
          'c.categories_id' => [
            'val' => (int)$_GET['cID'],
            'rel' => 'cd.categories_id'
          ],
          'cd.language_id' => $OSCOM_Language->getId()
        ]);

        if ($Qcategory->fetch() !== false) {
          $category_childs = array('childs_count' => tep_childs_in_category_count($Qcategory->valueInt('categories_id')));
          $category_products = array('products_count' => tep_products_in_category_count($Qcategory->valueInt('categories_id')));

          $cInfo_array = array_merge($Qcategory->toArray(), $category_childs, $category_products);
          $cInfo = new objectInfo($cInfo_array);
        }
      } elseif (isset($_GET['pID']) && is_numeric($_GET['pID']) && ($_GET['pID'] > 0)) {
        $Qproduct = $OSCOM_Db->get([
          'products p',
          'products_description pd'
        ], [
          'p.products_id',
          'pd.products_name',
          'p.products_quantity',
          'p.products_image',
          'p.products_price',
          'p.products_date_added',
          'p.products_last_modified',
          'p.products_date_available',
          'p.products_status'
        ], [
          'p.products_id' => [
            'val' => (int)$_GET['pID'],
            'rel' => [
              'pd.products_id'
            ]
          ],
          'pd.language_id' => $OSCOM_Language->getId()
        ]);

        if ($Qproduct->fetch() !== false) {
          $Qreviews = $OSCOM_Db->get('reviews', '(avg(reviews_rating) / 5 * 100) as average_rating', ['products_id' => $Qproduct->valueInt('products_id')]);

          $pInfo_array = array_merge($Qproduct->toArray(), $Qreviews->toArray());
          $pInfo = new objectInfo($pInfo_array);
        }
      }

      switch ($action) {
        case 'new_category':
          $heading[] = array('text' => OSCOM::getDef('text_info_heading_new_category'));

          $contents = array('form' => HTML::form('newcategory', OSCOM::link(FILENAME_CATEGORIES, 'action=insert_category&cPath=' . $cPath), 'post', 'enctype="multipart/form-data"'));
          $contents[] = array('text' => OSCOM::getDef('text_new_category_intro'));

          $category_inputs_string = '';
          $languages = tep_get_languages();
          for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $category_inputs_string .= '<br />' . $OSCOM_Language->getImage($languages[$i]['code']) . '&nbsp;' . HTML::inputField('categories_name[' . $languages[$i]['id'] . ']');
          }

          $contents[] = array('text' => OSCOM::getDef('text_categories_name') . $category_inputs_string);
          $contents[] = array('text' => OSCOM::getDef('text_categories_image') . '<br />' . HTML::fileField('categories_image'));
          $contents[] = array('text' => OSCOM::getDef('text_sort_order') . '<br />' . HTML::inputField('sort_order', '', 'size="2"'));
          $contents[] = array('text' => HTML::button(OSCOM::getDef('image_save'), 'fa fa-save', null, null, 'btn-success') . HTML::button(OSCOM::getDef('image_cancel'), null, OSCOM::link(FILENAME_CATEGORIES, 'cPath=' . $cPath), null, 'btn-link'));
          break;

        case 'edit_category':
          if (isset($cInfo)) {
            $heading[] = array('text' => OSCOM::getDef('text_info_heading_edit_category'));

            $contents = array('form' => HTML::form('categories', OSCOM::link(FILENAME_CATEGORIES, 'action=update_category&cPath=' . $cPath), 'post', 'enctype="multipart/form-data"') . HTML::hiddenField('categories_id', $cInfo->categories_id));
            $contents[] = array('text' => OSCOM::getDef('text_edit_intro'));

            $category_inputs_string = '';
            $languages = tep_get_languages();
            for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
              $category_inputs_string .= '<br />' . $OSCOM_Language->getImage($languages[$i]['code']) . '&nbsp;' . HTML::inputField('categories_name[' . $languages[$i]['id'] . ']', tep_get_category_name($cInfo->categories_id, $languages[$i]['id']));
            }

            $contents[] = array('text' => OSCOM::getDef('text_edit_categories_name') . $category_inputs_string);
            $contents[] = array('text' => HTML::image(OSCOM::linkImage('Shop/' . $cInfo->categories_image), $cInfo->categories_name) . '<br />' . OSCOM::getConfig('http_path', 'Shop') . OSCOM::getConfig('http_images_path', 'Shop') . '<br /><strong>' . $cInfo->categories_image . '</strong>');
            $contents[] = array('text' => OSCOM::getDef('text_edit_categories_image') . '<br />' . HTML::fileField('categories_image'));
            $contents[] = array('text' => OSCOM::getDef('text_edit_sort_order') . '<br />' . HTML::inputField('sort_order', $cInfo->sort_order, 'size="2"'));
            $contents[] = array('text' => HTML::button(OSCOM::getDef('image_save'), 'fa fa-save', null, null, 'btn-success') . HTML::button(OSCOM::getDef('image_cancel'), null, OSCOM::link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $cInfo->categories_id), null, 'btn-link'));
          }
          break;

        case 'delete_category':
          if (isset($cInfo)) {
            $heading[] = array('text' => OSCOM::getDef('text_info_heading_delete_category'));

            $contents = array('form' => HTML::form('categories', OSCOM::link(FILENAME_CATEGORIES, 'action=delete_category_confirm&cPath=' . $cPath)) . HTML::hiddenField('categories_id', $cInfo->categories_id));
            $contents[] = array('text' => OSCOM::getDef('text_delete_category_intro'));
            $contents[] = array('text' => '<strong>' . $cInfo->categories_name . '</strong>');
            if ($cInfo->childs_count > 0) $contents[] = array('text' => OSCOM::getDef('text_delete_warning_childs', ['childs_count' => $cInfo->childs_count]));
            if ($cInfo->products_count > 0) $contents[] = array('text' => OSCOM::getDef('text_delete_warning_products', ['products_count' => $cInfo->products_count]));
            $contents[] = array('text' => HTML::button(OSCOM::getDef('image_delete'), 'fa fa-trash', null, null, 'btn-danger') . HTML::button(OSCOM::getDef('image_cancel'), null, OSCOM::link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $cInfo->categories_id), null, 'btn-link'));
          }
          break;

        case 'move_category':
          if (isset($cInfo)) {
            $heading[] = array('text' => OSCOM::getDef('text_info_heading_move_category'));

            $contents = array('form' => HTML::form('categories', OSCOM::link(FILENAME_CATEGORIES, 'action=move_category_confirm&cPath=' . $cPath)) . HTML::hiddenField('categories_id', $cInfo->categories_id));
            $contents[] = array('text' => OSCOM::getDef('text_move_categories_intro', ['categories_name' => $cInfo->categories_name]));
            $contents[] = array('text' => OSCOM::getDef('text_move', ['item_name' => $cInfo->categories_name]) . '<br />' . HTML::selectField('move_to_category_id', tep_get_category_tree(), $current_category_id));
            $contents[] = array('text' => HTML::button(OSCOM::getDef('image_move'), 'fa fa-share', null, null, 'btn-success') . HTML::button(OSCOM::getDef('image_cancel'), null, OSCOM::link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $cInfo->categories_id), null, 'btn-link'));
          }
          break;

        case 'delete_product':
          if (isset($pInfo)) {
            $heading[] = array('text' => OSCOM::getDef('text_info_heading_delete_product'));

            $contents = array('form' => HTML::form('products', OSCOM::link(FILENAME_CATEGORIES, 'action=delete_product_confirm&cPath=' . $cPath)) . HTML::hiddenField('products_id', $pInfo->products_id));
            $contents[] = array('text' => OSCOM::getDef('text_delete_product_intro'));
            $contents[] = array('text' => '<strong>' . $pInfo->products_name . '</strong>');

            $product_categories_string = '';
            $product_categories = tep_generate_category_path($pInfo->products_id, 'product');
            for ($i = 0, $n = sizeof($product_categories); $i < $n; $i++) {
              $category_path = '';
              for ($j = 0, $k = sizeof($product_categories[$i]); $j < $k; $j++) {
                $category_path .= $product_categories[$i][$j]['text'] . '&nbsp;&gt;&nbsp;';
              }
              $category_path = substr($category_path, 0, -16);
              $product_categories_string .= HTML::checkboxField('product_categories[]', $product_categories[$i][sizeof($product_categories[$i])-1]['id'], true) . '&nbsp;' . $category_path . '<br />';
            }
            $product_categories_string = substr($product_categories_string, 0, -4);

            $contents[] = array('text' => $product_categories_string);
            $contents[] = array('text' => HTML::button(OSCOM::getDef('image_delete'), 'fa fa-trash', null, null, 'btn-danger') . HTML::button(OSCOM::getDef('image_cancel'), null, OSCOM::link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id), null, 'btn-link'));
          }
          break;

        case 'move_product':
          $heading[] = array('text' => OSCOM::getDef('text_info_heading_move_product'));

          $contents = array('form' => HTML::form('products', OSCOM::link(FILENAME_CATEGORIES, 'action=move_product_confirm&cPath=' . $cPath)) . HTML::hiddenField('products_id', $pInfo->products_id));
          $contents[] = array('text' => OSCOM::getDef('text_move_products_intro', ['products_name' => $pInfo->products_name]));
          $contents[] = array('text' => OSCOM::getDef('text_info_current_categories') . '<br /><strong>' . tep_output_generated_category_path($pInfo->products_id, 'product') . '</strong>');
          $contents[] = array('text' => OSCOM::getDef('text_move', ['item_name' => $pInfo->products_name]) . '<br />' . HTML::selectField('move_to_category_id', tep_get_category_tree(), $current_category_id));
          $contents[] = array('text' => HTML::button(OSCOM::getDef('image_move'), 'fa fa-share', null, null, 'btn-success') . HTML::button(OSCOM::getDef('image_cancel'), null, OSCOM::link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id), null, 'btn-link'));
          break;

        case 'copy_to':
          $heading[] = array('text' => OSCOM::getDef('text_info_heading_copy_to'));

          $contents = array('form' => HTML::form('copy_to', OSCOM::link(FILENAME_CATEGORIES, 'action=copy_to_confirm&cPath=' . $cPath)) . HTML::hiddenField('products_id', $pInfo->products_id));
          $contents[] = array('text' => OSCOM::getDef('text_info_copy_to_intro'));
          $contents[] = array('text' => OSCOM::getDef('text_info_current_categories') . '<br /><strong>' . tep_output_generated_category_path($pInfo->products_id, 'product') . '</strong>');
          $contents[] = array('text' => OSCOM::getDef('text_categories') . '<br />' . HTML::selectField('categories_id', tep_get_category_tree(), $current_category_id));
          $contents[] = array('text' => OSCOM::getDef('text_how_to_copy') . '<br />' . HTML::radioField('copy_as', 'link', true) . ' ' . OSCOM::getDef('text_copy_as_link') . '<br />' . HTML::radioField('copy_as', 'duplicate') . ' ' . OSCOM::getDef('text_copy_as_duplicate'));
          $contents[] = array('text' => HTML::button(OSCOM::getDef('image_copy'), 'fa fa-copy', null, null, 'btn-success') . HTML::button(OSCOM::getDef('image_cancel'), null, OSCOM::link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id), null, 'btn-link'));
          break;
      }

      if (tep_not_null($heading) && tep_not_null($contents)) {
        $show_listing = false;

        echo HTML::panel($heading, $contents, ['type' => 'info']);
      }
    }
  }

  if ($show_listing === true) {
?>

<table class="oscom-table table table-hover">
  <thead>
    <tr class="info">
      <th><?= OSCOM::getDef('table_heading_categories_products'); ?></th>
      <th class="text-right">Qty</th>
      <th class="text-right">Price</th>
      <th class="text-right"><?= OSCOM::getDef('table_heading_status'); ?></th>
      <th class="action"></th>
    </tr>
  </thead>
  <tbody>

<?php
    if (isset($_GET['search'])) {
      $search = HTML::sanitize($_GET['search']);

      $Qcategories = $OSCOM_Db->get([
        'categories c',
        'categories_description cd'
      ], [
        'c.categories_id',
        'cd.categories_name',
        'c.categories_image',
        'c.parent_id',
        'c.sort_order',
        'c.date_added',
        'c.last_modified'
      ], [
        'c.categories_id' => 'cd.categories_id',
        'cd.language_id' => $OSCOM_Language->getId(),
        'cd.categories_name' => [
          'op' => 'like',
          'val' => '%' . $search . '%'
        ]
      ], [
        'c.sort_order',
        'cd.categories_name'
      ]);
    } else {
      $Qcategories = $OSCOM_Db->get([
        'categories c',
        'categories_description cd'
      ], [
        'c.categories_id',
        'cd.categories_name',
        'c.categories_image',
        'c.parent_id',
        'c.sort_order',
        'c.date_added',
        'c.last_modified'
      ], [
        'c.parent_id' => (int)$current_category_id,
        'c.categories_id' => [
          'rel' => 'cd.categories_id'
        ],
        'cd.language_id' => $OSCOM_Language->getId()
      ], [
        'c.sort_order',
        'cd.categories_name'
      ]);
    }

    while ($Qcategories->fetch()) {
// Get parent_id for subcategories if search
      if (isset($_GET['search'])) $cPath= $Qcategories->valueInt('parent_id');

      $category_path_string = '';
      $category_path = tep_generate_category_path($Qcategories->valueInt('categories_id'));
      for ($i=(sizeof($category_path[0])-1); $i>0; $i--) {
        $category_path_string .= $category_path[0][$i]['id'] . '_';
      }
      $category_path_string = substr($category_path_string, 0, -1);
?>

    <tr>
      <td><?= '<a href="' . OSCOM::link(FILENAME_CATEGORIES, tep_get_path($Qcategories->valueInt('categories_id'))) . '"><i class="fa fa-play"></i>&nbsp;' . $Qcategories->value('categories_name') . '</a>'; ?></td>
      <td></td>
      <td></td>
      <td class="text-right"></td>
      <td class="action"><?=
        '<a href="' . OSCOM::link(FILENAME_CATEGORIES, 'cPath=' . $category_path_string . '&cID=' . $Qcategories->valueInt('categories_id') . '&action=edit_category') . '"><i class="fa fa-pencil" title="' . OSCOM::getDef('image_edit') . '"></i></a>
         <a href="' . OSCOM::link(FILENAME_CATEGORIES, 'cPath=' . $category_path_string . '&cID=' . $Qcategories->valueInt('categories_id') . '&action=delete_category') . '"><i class="fa fa-trash" title="' . OSCOM::getDef('image_delete') . '"></i></a>
         <a href="' . OSCOM::link(FILENAME_CATEGORIES, 'cPath=' . $category_path_string . '&cID=' . $Qcategories->valueInt('categories_id') . '&action=move_category') . '"><i class="fa fa-share" title="' . OSCOM::getDef('image_move') . '"></i></a>';
      ?></td>
    </tr>

<?php
    }

    if (isset($_GET['search'])) {
      $Qproducts = $OSCOM_Db->get([
        'products p',
        'products_description pd',
        'products_to_categories p2c'
      ], [
        'p.products_id',
        'pd.products_name',
        'p.products_quantity',
        'p.products_image',
        'p.products_price',
        'p.products_date_added',
        'p.products_last_modified',
        'p.products_date_available',
        'p.products_status',
        'p2c.categories_id'
      ], [
        'p.products_id' => [
          'rel' => [
            'pd.products_id',
            'p2c.products_id'
          ]
        ],
        'pd.language_id' => $OSCOM_Language->getId(),
        'pd.products_name' => [
          'op' => 'like',
          'val' => '%' . $search . '%'
        ]
      ], 'pd.products_name');
    } else {
      $Qproducts = $OSCOM_Db->get([
        'products p',
        'products_description pd',
        'products_to_categories p2c'
      ], [
        'p.products_id',
        'pd.products_name',
        'p.products_quantity',
        'p.products_image',
        'p.products_price',
        'p.products_date_added',
        'p.products_last_modified',
        'p.products_date_available',
        'p.products_status'
      ], [
        'p.products_id' => [
          'rel' => [
            'pd.products_id',
            'p2c.products_id'
          ]
        ],
        'pd.language_id' => $OSCOM_Language->getId(),
        'p2c.categories_id' => (int)$current_category_id
      ], 'pd.products_name');
    }

    while ($Qproducts->fetch()) {
// Get categories_id for product if search
      if (isset($_GET['search'])) $cPath = $Qproducts->valueInt('categories_id');
?>

    <tr>
      <td><?= '<a href="' . OSCOM::link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $Qproducts->valueInt('products_id') . '&action=new_product_preview') . '">' . $Qproducts->value('products_name') . '</a>'; ?></td>
      <td class="text-right"><?= $Qproducts->valueInt('products_quantity'); ?></td>
      <td class="text-right"><?= $currencies->format($Qproducts->value('products_price')); ?></td>
      <td class="text-right">

<?php
      if ($Qproducts->valueInt('products_status') === 1) {
        echo '<i class="fa fa-circle text-success" title="' . OSCOM::getDef('image_icon_status_green') . '"></i>&nbsp;<a href="' . OSCOM::link(FILENAME_CATEGORIES, 'action=setflag&flag=0&pID=' . $Qproducts->valueInt('products_id') . '&cPath=' . $cPath) . '"><i class="fa fa-circle-o text-danger" title="' . OSCOM::getDef('image_icon_status_red_light') . '"></i></a>';
      } else {
        echo '<a href="' . OSCOM::link(FILENAME_CATEGORIES, 'action=setflag&flag=1&pID=' . $Qproducts->valueInt('products_id') . '&cPath=' . $cPath) . '"><i class="fa fa-circle-o text-success" title="' . OSCOM::getDef('image_icon_status_green_light') . '"></i></a>&nbsp;<i class="fa fa-circle text-danger" title="' . OSCOM::getDef('image_icon_status_red') . '"></i>';
      }
?>

      </td>
      <td class="action"><?=
        '<a href="' . OSCOM::link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $Qproducts->valueInt('products_id') . '&action=new_product') . '"><i class="fa fa-pencil" title="' . OSCOM::getDef('image_edit') . '"></i></a>
         <a href="' . OSCOM::link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $Qproducts->valueInt('products_id') . '&action=delete_product') . '"><i class="fa fa-trash" title="' . OSCOM::getDef('image_delete') . '"></i></a>
         <a href="' . OSCOM::link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $Qproducts->valueInt('products_id') . '&action=move_product') . '"><i class="fa fa-share" title="' . OSCOM::getDef('image_move') . '"></i></a>
         <a href="' . OSCOM::link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $Qproducts->valueInt('products_id') . '&action=copy_to') . '"><i class="fa fa-copy" title="' . OSCOM::getDef('image_copy_to') . '"></i></a>';
      ?></td>
    </tr>

<?php
    }
?>

  </tbody>
</table>

<?php
  }

  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
