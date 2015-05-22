<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

// the following cPath references come from application_top.php
  $category_depth = 'top';
  if (isset($cPath) && tep_not_null($cPath)) {
    $Qcheck = $OSCOM_Db->prepare('select products_id from :table_products_to_categories where categories_id = :categories_id limit 1');
    $Qcheck->bindInt(':categories_id', $current_category_id);
    $Qcheck->execute();

    if ($Qcheck->fetch() !== false) {
      $category_depth = 'products'; // display products
    } else {
      $Qcheck = $OSCOM_Db->prepare('select categories_id from :table_categories where parent_id = :parent_id');
      $Qcheck->bindInt(':parent_id', $current_category_id);
      $Qcheck->execute();

      if ($Qcheck->fetch() !== false) {
        $category_depth = 'nested'; // navigate through the categories
      } else {
        $category_depth = 'products'; // category has no products, but display the 'no products' message
      }
    }
  }

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/index.php');

  require('includes/template_top.php');

  if ($category_depth == 'nested') {
    $Qcategory = $OSCOM_Db->prepare('select cd.categories_name, c.categories_image from :table_categories c, :table_categories_description cd where c.categories_id = :categories_id and c.categories_id = cd.categories_id and cd.language_id = :language_id');
    $Qcategory->bindInt(':categories_id', $current_category_id);
    $Qcategory->bindInt(':language_id', $_SESSION['languages_id']);
    $Qcategory->execute();
?>

<div class="page-header">
  <h1><?php echo $Qcategory->value('categories_name'); ?></h1>
</div>

<?php
  if ($messageStack->size('product_action') > 0) {
    echo $messageStack->output('product_action');
  }
?>

<div class="contentContainer">
  <div class="contentText">
    <div class="row">
<?php
    $deepest_category_id = $current_category_id;

    if (isset($cPath) && strpos('_', $cPath)) {
// check to see if there are deeper categories within the current category
      $category_links = array_reverse($cPath_array);
      for($i=0, $n=sizeof($category_links); $i<$n; $i++) {
        $Qcheck = $OSCOM_Db->prepare('select c.categories_id from :table_categories c, :table_categories_description cd where c.parent_id = :parent_id and c.categories_id = cd.categories_id and cd.language_id = :language_id');
        $Qcheck->bindInt(':parent_id', $category_links[$i]);
        $Qcheck->bindInt(':language_id', $_SESSION['languages_id']);
        $Qcheck->execute();

        if ($Qcheck->fetch() === false) {
          // do nothing, go through the loop
        } else {
          $deepest_category_id = $category_links[$i];
          break; // we've found the deepest category the customer is in
        }
      }
    }

    $Qcategories = $OSCOM_Db->prepare('select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from :table_categories c, :table_categories_description cd where c.parent_id = :parent_id and c.categories_id = cd.categories_id and cd.language_id = :language_id order by sort_order, cd.categories_name');
    $Qcategories->bindInt(':parent_id', $deepest_category_id);
    $Qcategories->bindInt(':language_id', $_SESSION['languages_id']);
    $Qcategories->execute();

    while ($Qcategories->fetch()) {
      $cPath_new = tep_get_path($Qcategories->valueInt('categories_id'));

      echo '<div class="col-xs-6 col-sm-4">';
      echo '  <div class="text-center">';
      echo '    <a href="' . OSCOM::link('index.php', $cPath_new) . '">' . tep_image(DIR_WS_IMAGES . $Qcategories->value('categories_image'), $Qcategories->value('categories_name'), SUBCATEGORY_IMAGE_WIDTH, SUBCATEGORY_IMAGE_HEIGHT) . '</a>';
      echo '    <div class="caption text-center">';
      echo '      <h5><a href="' . OSCOM::link('index.php', $cPath_new) . '">' . $Qcategories->value('categories_name') . '</a></h5>';
      echo '    </div>';
      echo '  </div>';
      echo '</div>';
    }

// needed for the new products module shown below
    $new_products_category_id = $current_category_id;
?>
      </div>

<div class="clearfix"></div>

<?php include('includes/modules/new_products.php'); ?>

  </div>
</div>

<?php
  } elseif ($category_depth == 'products' || (isset($_GET['manufacturers_id']) && !empty($_GET['manufacturers_id']))) {
// create column list
    $define_list = array('PRODUCT_LIST_MODEL' => PRODUCT_LIST_MODEL,
                         'PRODUCT_LIST_NAME' => PRODUCT_LIST_NAME,
                         'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
                         'PRODUCT_LIST_PRICE' => PRODUCT_LIST_PRICE,
                         'PRODUCT_LIST_QUANTITY' => PRODUCT_LIST_QUANTITY,
                         'PRODUCT_LIST_WEIGHT' => PRODUCT_LIST_WEIGHT,
                         'PRODUCT_LIST_IMAGE' => PRODUCT_LIST_IMAGE,
                         'PRODUCT_LIST_BUY_NOW' => PRODUCT_LIST_BUY_NOW);

    asort($define_list);

    $column_list = array();
    foreach($define_list as $key => $value) {
      if ($value > 0) $column_list[] = $key;
    }

    $search_query = 'select SQL_CALC_FOUND_ROWS';

    for ($i=0, $n=sizeof($column_list); $i<$n; $i++) {
      switch ($column_list[$i]) {
        case 'PRODUCT_LIST_MODEL':
          $search_query .= ' p.products_model,';
          break;
        case 'PRODUCT_LIST_NAME':
          $search_query .= ' pd.products_name,';
          break;
        case 'PRODUCT_LIST_MANUFACTURER':
          $search_query .= ' m.manufacturers_name,';
          break;
        case 'PRODUCT_LIST_QUANTITY':
          $search_query .= ' p.products_quantity,';
          break;
        case 'PRODUCT_LIST_IMAGE':
          $search_query .= ' p.products_image,';
          break;
        case 'PRODUCT_LIST_WEIGHT':
          $search_query .= ' p.products_weight,';
          break;
      }
    }

// show the products of a specified manufacturer
    if (isset($_GET['manufacturers_id']) && !empty($_GET['manufacturers_id'])) {
      if (isset($_GET['filter_id']) && tep_not_null($_GET['filter_id'])) {
// We are asked to show only a specific category
        $search_query .= ' p.products_id, SUBSTRING_INDEX(pd.products_description, " ", 20) as products_description, p.manufacturers_id, p.products_price, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from :table_products p left join :table_specials s on p.products_id = s.products_id, :table_products_description pd, :table_manufacturers m, :table_products_to_categories p2c where p.products_status = "1" and p.manufacturers_id = m.manufacturers_id and m.manufacturers_id = :manufacturers_id and p.products_id = p2c.products_id and pd.products_id = p2c.products_id and pd.language_id = :language_id and p2c.categories_id = :categories_id';
      } else {
// We show them all
        $search_query .= ' p.products_id, SUBSTRING_INDEX(pd.products_description, " ", 20) as products_description, p.manufacturers_id, p.products_price, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from :table_products p left join :table_specials s on p.products_id = s.products_id, :table_products_description pd, :table_manufacturers m where p.products_status = "1" and pd.products_id = p.products_id and pd.language_id = :language_id and p.manufacturers_id = m.manufacturers_id and m.manufacturers_id = :manufacturers_id';
      }
    } else {
// show the products in a given categorie
      if (isset($_GET['filter_id']) && tep_not_null($_GET['filter_id'])) {
// We are asked to show only specific catgeory
        $search_query .= ' p.products_id, SUBSTRING_INDEX(pd.products_description, " ", 20) as products_description, p.manufacturers_id, p.products_price, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from :table_products p left join :table_specials s on p.products_id = s.products_id, :table_products_description pd, :table_manufacturers m, :table_products_to_categories p2c where p.products_status = "1" and p.manufacturers_id = m.manufacturers_id and m.manufacturers_id = :manufacturers_id and p.products_id = p2c.products_id and pd.products_id = p2c.products_id and pd.language_id = :language_id and p2c.categories_id = :categories_id';
      } else {
// We show them all
        $search_query .= ' p.products_id, SUBSTRING_INDEX(pd.products_description, " ", 20) as products_description, p.manufacturers_id, p.products_price, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from :table_products_description pd, :table_products p left join :table_manufacturers m on p.manufacturers_id = m.manufacturers_id left join :table_specials s on p.products_id = s.products_id, :table_products_to_categories p2c where p.products_status = "1" and p.products_id = p2c.products_id and pd.products_id = p2c.products_id and pd.language_id = :language_id and p2c.categories_id = :categories_id';
      }
    }

    if ( (!isset($_GET['sort'])) || (!preg_match('/^[1-8][ad]$/', $_GET['sort'])) || (substr($_GET['sort'], 0, 1) > sizeof($column_list)) ) {
      for ($i=0, $n=sizeof($column_list); $i<$n; $i++) {
        if ($column_list[$i] == 'PRODUCT_LIST_NAME') {
          $_GET['sort'] = $i+1 . 'a';
          $search_query .= ' order by pd.products_name';
          break;
        }
      }
    } else {
      $sort_col = substr($_GET['sort'], 0 , 1);
      $sort_order = substr($_GET['sort'], 1);

      switch ($column_list[$sort_col-1]) {
        case 'PRODUCT_LIST_MODEL':
          $search_query .= ' order by p.products_model ' . ($sort_order == 'd' ? 'desc' : '') . ', pd.products_name';
          break;
        case 'PRODUCT_LIST_NAME':
          $search_query .= ' order by pd.products_name ' . ($sort_order == 'd' ? 'desc' : '');
          break;
        case 'PRODUCT_LIST_MANUFACTURER':
          $search_query .= ' order by m.manufacturers_name ' . ($sort_order == 'd' ? 'desc' : '') . ', pd.products_name';
          break;
        case 'PRODUCT_LIST_QUANTITY':
          $search_query .= ' order by p.products_quantity ' . ($sort_order == 'd' ? 'desc' : '') . ', pd.products_name';
          break;
        case 'PRODUCT_LIST_IMAGE':
          $search_query .= ' order by pd.products_name';
          break;
        case 'PRODUCT_LIST_WEIGHT':
          $search_query .= ' order by p.products_weight ' . ($sort_order == 'd' ? 'desc' : '') . ', pd.products_name';
          break;
        case 'PRODUCT_LIST_PRICE':
          $search_query .= ' order by final_price ' . ($sort_order == 'd' ? 'desc' : '') . ', pd.products_name';
          break;
      }
    }

    $search_query .= ' limit :page_set_offset, :page_set_max_results';

    $Qlisting = $OSCOM_Db->prepare($search_query);

    if (isset($_GET['manufacturers_id']) && !empty($_GET['manufacturers_id'])) {
      if (isset($_GET['filter_id']) && tep_not_null($_GET['filter_id'])) {
        $Qlisting->bindInt(':manufacturers_id', $_GET['manufacturers_id']);
        $Qlisting->bindInt(':language_id', $_SESSION['languages_id']);
        $Qlisting->bindInt(':categories_id', $_GET['filter_id']);
      } else {
        $Qlisting->bindInt(':language_id', $_SESSION['languages_id']);
        $Qlisting->bindInt(':manufacturers_id', $_GET['manufacturers_id']);
      }
    } else {
      if (isset($_GET['filter_id']) && tep_not_null($_GET['filter_id'])) {
        $Qlisting->bindInt(':manufacturers_id', $_GET['filter_id']);
        $Qlisting->bindInt(':language_id', $_SESSION['languages_id']);
        $Qlisting->bindInt(':categories_id', $current_category_id);
      } else {
        $Qlisting->bindInt(':language_id', $_SESSION['languages_id']);
        $Qlisting->bindInt(':categories_id', $current_category_id);
      }
    }

    $Qlisting->setPageSet(MAX_DISPLAY_SEARCH_RESULTS);
    $Qlisting->execute();

    $catname = HEADING_TITLE;
    if (isset($_GET['manufacturers_id']) && !empty($_GET['manufacturers_id'])) {
      $Qtitle = $OSCOM_Db->prepare('select manufacturers_image, manufacturers_name as catname from :table_manufacturers where manufacturers_id = :manufacturers_id');
      $Qtitle->bindInt(':manufacturers_id', $_GET['manufacturers_id']);
      $Qtitle->execute();

      $catname = $Qtitle->value('catname');
    } elseif ($current_category_id) {
      $Qtitle = $OSCOM_Db->prepare('select c.categories_image, cd.categories_name as catname from :table_categories c, :table_categories_description cd where c.categories_id = :categories_id and c.categories_id = cd.categories_id and cd.language_id = :language_id');
      $Qtitle->bindInt(':categories_id', $current_category_id);
      $Qtitle->bindInt(':language_id', $_SESSION['languages_id']);
      $Qtitle->execute();

      $catname = $Qtitle->value('catname');
    }
?>

<div class="page-header">
  <h1><?php echo $catname; ?></h1>
</div>

<div class="contentContainer">

<?php
// optional Product List Filter
    if (PRODUCT_LIST_FILTER > 0) {
      if (isset($_GET['manufacturers_id']) && !empty($_GET['manufacturers_id'])) {
        $Qfilter = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS distinct c.categories_id as id, cd.categories_name as name from :table_products p, :table_products_to_categories p2c, :table_categories c, :table_categories_description cd where p.manufacturers_id = :manufacturers_id and p.products_status = "1" and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and c.categories_id = cd.categories_id and cd.language_id = :language_id order by cd.categories_name');
        $Qfilter->bindInt(':language_id', $_SESSION['languages_id']);
        $Qfilter->bindInt(':manufacturers_id', $_GET['manufacturers_id']);
        $Qfilter->execute();
      } else {
        $Qfilter = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS distinct m.manufacturers_id as id, m.manufacturers_name as name from :table_products p, :table_products_to_categories p2c, :table_manufacturers m where p.products_status = "1" and p.manufacturers_id = m.manufacturers_id and p.products_id = p2c.products_id and p2c.categories_id = :categories_id order by m.manufacturers_name');
        $Qfilter->bindInt(':categories_id', $current_category_id);
        $Qfilter->execute();
      }

      $QfilterTotalRows = $OSCOM_Db->query('select found_rows()')->fetchColumn();

      if ($QfilterTotalRows > 1) {
        echo '<div>' . HTML::form('filter', OSCOM::link('index.php'), 'get') . '<p align="right">' . TEXT_SHOW . '&nbsp;';
        if (isset($_GET['manufacturers_id']) && !empty($_GET['manufacturers_id'])) {
          echo HTML::hiddenField('manufacturers_id', $_GET['manufacturers_id']);
          $options = array(array('id' => '', 'text' => TEXT_ALL_CATEGORIES));
        } else {
          echo HTML::hiddenField('cPath', $cPath);
          $options = array(array('id' => '', 'text' => TEXT_ALL_MANUFACTURERS));
        }
        echo HTML::hiddenField('sort', $_GET['sort']);
        while ($Qfilter->fetch()) {
          $options[] = array('id' => $Qfilter->valueInt('id'), 'text' => $Qfilter->value('name'));
        }
        echo tep_draw_pull_down_menu('filter_id', $options, (isset($_GET['filter_id']) ? $_GET['filter_id'] : ''), 'onchange="this.form.submit()"');
        echo tep_hide_session_id() . '</p></form></div>' . "\n";
      }
    }

    include('includes/modules/product_listing.php');
?>

</div>

<?php
  } else { // default page
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<?php
  if ($messageStack->size('product_action') > 0) {
    echo $messageStack->output('product_action');
  }
?>

<div class="contentContainer">
   <div class="alert alert-info">
    <?php echo tep_customer_greeting(); ?>
  </div>

<?php
    if (tep_not_null(TEXT_MAIN)) {
?>

  <div class="contentText">
    <?php echo TEXT_MAIN; ?>
  </div>

<?php
    }

    include('includes/modules/new_products.php');
    include('includes/modules/upcoming_products.php');
?>

</div>

<?php
  }

  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
