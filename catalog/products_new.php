<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  $OSCOM_Language->loadDefinitions('products_new');

  $breadcrumb->add(OSCOM::getDef('navbar_title'), OSCOM::link('products_new.php'));

  require($oscTemplate->getFile('template_top.php'));
?>

<div class="page-header">
  <h1><?php echo OSCOM::getDef('heading_title'); ?></h1>
</div>

<?php
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

  foreach ($define_list as $key => $value) {
    if ($value > 0) $column_list[] = $key;
  }

  $column_list[] = 'PRODUCT_LIST_ID';

  $listing_sql = 'select SQL_CALC_FOUND_ROWS';

  for ($i=0, $n=sizeof($column_list); $i<$n; $i++) {
    switch ($column_list[$i]) {
      case 'PRODUCT_LIST_MODEL':
        $listing_sql .= ' p.products_model,';
        break;
      case 'PRODUCT_LIST_NAME':
        $listing_sql .= ' pd.products_name,';
        break;
      case 'PRODUCT_LIST_MANUFACTURER':
        $listing_sql .= ' m.manufacturers_name,';
        break;
      case 'PRODUCT_LIST_QUANTITY':
        $listing_sql .= ' p.products_quantity,';
        break;
      case 'PRODUCT_LIST_IMAGE':
        $listing_sql .= ' p.products_image,';
        break;
      case 'PRODUCT_LIST_WEIGHT':
        $listing_sql .= ' p.products_weight,';
        break;
    }
  }

  $listing_sql .= ' p.products_id, SUBSTRING_INDEX(pd.products_description, " ", 20) as products_description, p.manufacturers_id, p.products_price, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from :table_products_description pd, :table_products p left join :table_manufacturers m on p.manufacturers_id = m.manufacturers_id left join :table_specials s on p.products_id = s.products_id where p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :language_id';

  if ( (!isset($_GET['sort'])) || (!preg_match('/^[1-8][ad]$/', $_GET['sort'])) || (substr($_GET['sort'], 0, 1) > sizeof($column_list)) ) {
    for ($i=0, $n=sizeof($column_list); $i<$n; $i++) {
      if ($column_list[$i] == 'PRODUCT_LIST_ID') {
        $_GET['sort'] = $i+1 . 'd';
        $listing_sql .= ' order by p.products_id DESC';
        break;
      }
    }
  } else {
    $sort_col = substr($_GET['sort'], 0 , 1);
    $sort_order = substr($_GET['sort'], 1);

    switch ($column_list[$sort_col-1]) {
      case 'PRODUCT_LIST_MODEL':
        $listing_sql .= ' order by p.products_model ' . ($sort_order == 'd' ? 'desc' : '') . ', pd.products_name';
        break;
      case 'PRODUCT_LIST_NAME':
        $listing_sql .= ' order by pd.products_name ' . ($sort_order == 'd' ? 'desc' : '');
        break;
      case 'PRODUCT_LIST_MANUFACTURER':
        $listing_sql .= ' order by m.manufacturers_name ' . ($sort_order == 'd' ? 'desc' : '') . ', pd.products_name';
        break;
      case 'PRODUCT_LIST_QUANTITY':
        $listing_sql .= ' order by p.products_quantity ' . ($sort_order == 'd' ? 'desc' : '') . ', pd.products_name';
        break;
      case 'PRODUCT_LIST_IMAGE':
        $listing_sql .= ' order by pd.products_name';
        break;
      case 'PRODUCT_LIST_WEIGHT':
        $listing_sql .= ' order by p.products_weight ' . ($sort_order == 'd' ? 'desc' : '') . ', pd.products_name';
        break;
      case 'PRODUCT_LIST_PRICE':
        $listing_sql .= ' order by final_price ' . ($sort_order == 'd' ? 'desc' : '') . ', pd.products_name';
        break;
      case 'PRODUCT_LIST_ID':
        $listing_sql .= ' order by p.products_id ' . ($sort_order == 'd' ? 'desc' : '');
        break;
    }
  }

  $listing_sql .= ' limit :page_set_offset, :page_set_max_results';

  $Qlisting = $OSCOM_Db->prepare($listing_sql);
  $Qlisting->bindInt(':language_id', $OSCOM_Language->getId());
  $Qlisting->setPageSet(MAX_DISPLAY_SEARCH_RESULTS);
  $Qlisting->execute();

  include('includes/content/product_listing.php');

  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
