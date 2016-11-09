<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\DateTime;
  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  $OSCOM_Language->loadDefinitions('advanced_search');

  $error = false;

  if ( (isset($_GET['keywords']) && empty($_GET['keywords'])) &&
       (isset($_GET['dfrom']) && (empty($_GET['dfrom']) || ($_GET['dfrom'] == OSCOM::getDef('dob_format_string')))) &&
       (isset($_GET['dto']) && (empty($_GET['dto']) || ($_GET['dto'] == OSCOM::getDef('dob_format_string')))) &&
       (isset($_GET['pfrom']) && !is_numeric($_GET['pfrom'])) &&
       (isset($_GET['pto']) && !is_numeric($_GET['pto'])) ) {
    $error = true;

    $messageStack->add_session('search', OSCOM::getDef('error_at_least_one_input'));
  } else {
    $dfrom = '';
    $dto = '';
    $pfrom = '';
    $pto = '';
    $keywords = '';

    if (isset($_GET['dfrom'])) {
      $dfrom = (($_GET['dfrom'] == OSCOM::getDef('dob_format_string')) ? '' : $_GET['dfrom']);
    }

    if (isset($_GET['dto'])) {
      $dto = (($_GET['dto'] == OSCOM::getDef('dob_format_string')) ? '' : $_GET['dto']);
    }

    if (isset($_GET['pfrom'])) {
      $pfrom = $_GET['pfrom'];
    }

    if (isset($_GET['pto'])) {
      $pto = $_GET['pto'];
    }

    if (isset($_GET['keywords'])) {
      $keywords = HTML::sanitize($_GET['keywords']);
    }

    $date_check_error = false;
    if (tep_not_null($dfrom)) {
      $dfromDateTime = new DateTime($dfrom);

      if ($dfromDateTime->isValid() === false) {
        $error = true;
        $date_check_error = true;

        $messageStack->add_session('search', OSCOM::getDef('error_invalid_from_date'));
      }
    }

    if (tep_not_null($dto)) {
      $dtoDateTime = new DateTime($dto);

      if ($dtoDateTime->isValid() === false) {
        $error = true;
        $date_check_error = true;

        $messageStack->add_session('search', OSCOM::getDef('error_invalid_to_date'));
      }
    }

    if (($date_check_error == false) && isset($dfromDateTime) && $dfromDateTime->isValid() && isset($dtoDateTime) && $dtoDateTime->isValid()) {
      if ($dfromDateTime->get() > $dtoDateTime->get()) {
        $error = true;

        $messageStack->add_session('search', OSCOM::getDef('error_to_date_less_than_from_date'));
      }
    }

    $price_check_error = false;
    if (tep_not_null($pfrom)) {
      if (!settype($pfrom, 'double')) {
        $error = true;
        $price_check_error = true;

        $messageStack->add_session('search', OSCOM::getDef('error_price_from_must_be_num'));
      }
    }

    if (tep_not_null($pto)) {
      if (!settype($pto, 'double')) {
        $error = true;
        $price_check_error = true;

        $messageStack->add_session('search', OSCOM::getDef('error_price_to_must_be_num'));
      }
    }

    if (($price_check_error == false) && is_float($pfrom) && is_float($pto)) {
      if ($pfrom >= $pto) {
        $error = true;

        $messageStack->add_session('search', OSCOM::getDef('error_price_to_less_than_price_from'));
      }
    }

    if (tep_not_null($keywords)) {
      $search_keywords = explode(' ', $keywords);

      if (empty($search_keywords)) {
        $error = true;

        $messageStack->add_session('search', OSCOM::getDef('error_invalid_keywords'));
      }
    }
  }

  if ((!isset($dfromDateTime) || !$dfromDateTime->isValid()) && (!isset($dtoDateTime) || !$dtoDateTime->isValid()) && empty($pfrom) && empty($pto) && empty($keywords)) {
    $error = true;

    $messageStack->add_session('search', OSCOM::getDef('error_at_least_one_input'));
  }

  if ($error == true) {
    OSCOM::redirect('advanced_search.php', tep_get_all_get_params(), true, false);
  }

  $breadcrumb->add(OSCOM::getDef('navbar_title_1'), OSCOM::link('advanced_search.php'));
  $breadcrumb->add(OSCOM::getDef('navbar_title_2'), OSCOM::link('advanced_search_result.php', tep_get_all_get_params(), true, false));

  require($oscTemplate->getFile('template_top.php'));
?>

<div class="page-header">
  <h1><?php echo OSCOM::getDef('heading_title_2'); ?></h1>
</div>

<div class="contentContainer">

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

  foreach($define_list as $key => $value) {
    if ($value > 0) $column_list[] = $key;
  }

  $search_query = 'select SQL_CALC_FOUND_ROWS distinct';

  for ($i=0, $n=sizeof($column_list); $i<$n; $i++) {
    switch ($column_list[$i]) {
      case 'PRODUCT_LIST_MODEL':
        $search_query .= ' p.products_model,';
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

  $search_query .= ' m.manufacturers_id, p.products_id, SUBSTRING_INDEX(pd.products_description, " ", 20) as products_description, pd.products_name, p.products_price, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price';

  if ( (DISPLAY_PRICE_WITH_TAX == 'true') && (tep_not_null($pfrom) || tep_not_null($pto)) ) {
    $search_query .= ', SUM(tr.tax_rate) as tax_rate';
  }

  $search_query .= ' from :table_products p left join :table_manufacturers m using(manufacturers_id) left join :table_specials s on p.products_id = s.products_id';

  if ( (DISPLAY_PRICE_WITH_TAX == 'true') && (tep_not_null($pfrom) || tep_not_null($pto)) ) {
    if (!isset($_SESSION['customer_country_id'])) {
      $_SESSION['customer_country_id'] = STORE_COUNTRY;
      $_SESSION['customer_zone_id'] = STORE_ZONE;
    }
    $search_query .= ' left join :table_tax_rates tr on p.products_tax_class_id = tr.tax_class_id left join :table_zones_to_geo_zones gz on tr.tax_zone_id = gz.geo_zone_id and (gz.zone_country_id is null or gz.zone_country_id = "0" or gz.zone_country_id = :zone_country_id) and (gz.zone_id is null or gz.zone_id = "0" or gz.zone_id = :zone_id)';
  }

  $search_query .= ', :table_products_description pd, :table_categories c, :table_products_to_categories p2c where p.products_status = "1" and p.products_id = pd.products_id and pd.language_id = :language_id and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id';

  if (isset($_GET['categories_id']) && tep_not_null($_GET['categories_id'])) {
    if (isset($_GET['inc_subcat']) && ($_GET['inc_subcat'] == '1')) {
      $subcategories_array = array();
      tep_get_subcategories($subcategories_array, $_GET['categories_id']);

      $search_query .= ' and (p2c.categories_id = :categories_id';

      for ($i=0, $n=sizeof($subcategories_array); $i<$n; $i++ ) {
        $search_query .= ' or p2c.categories_id = :categories_id_' . $i;
      }

      $search_query .= ')';
    } else {
      $search_query .= ' and p2c.categories_id = :categories_id';
    }
  }

  if (isset($_GET['manufacturers_id']) && tep_not_null($_GET['manufacturers_id'])) {
    $search_query .= ' and m.manufacturers_id = :manufacturers_id';
  }

  if (isset($search_keywords) && (sizeof($search_keywords) > 0)) {
    $search_query .= ' and (';

    for ($i=0, $n=sizeof($search_keywords); $i<$n; $i++ ) {
      $search_query .= '(pd.products_name like :products_name_' . $i . ' or p.products_model like :products_model_' . $i . ' or m.manufacturers_name like :manufacturers_name_' . $i;

      if (isset($_GET['search_in_description']) && ($_GET['search_in_description'] == '1')) {
        $search_query .= ' or pd.products_description like :products_description_' . $i;
      }

      $search_query .= ') and ';
    }

    $search_query = substr($search_query, 0, -5) . ')';
  }

  if (isset($dfromDateTime) && $dfromDateTime->isValid()) {
    $search_query .= ' and p.products_date_added >= :products_date_added_from';
  }

  if (isset($dtoDateTime) && $dtoDateTime->isValid()) {
    $search_query .= ' and p.products_date_added <= :products_date_added_to';
  }

  if (tep_not_null($pfrom) || tep_not_null($pto)) {
    $rate = $currencies->get_value($_SESSION['currency']);

    if (tep_not_null($pfrom)) {
      $pfrom = $pfrom / $rate;
    }

    if (tep_not_null($pto)) {
      $pto = $pto / $rate;
    }
  }

  if (DISPLAY_PRICE_WITH_TAX == 'true') {
    if ($pfrom > 0) {
      $search_query .= ' and (IF(s.status, s.specials_new_products_price, p.products_price) * if(gz.geo_zone_id is null, 1, 1 + (tr.tax_rate / 100) ) >= :price_from)';
    }

    if ($pto > 0) {
      $search_query .= ' and (IF(s.status, s.specials_new_products_price, p.products_price) * if(gz.geo_zone_id is null, 1, 1 + (tr.tax_rate / 100) ) <= :price_to)';
    }
  } else {
    if ($pfrom > 0) {
      $search_query .= ' and (IF(s.status, s.specials_new_products_price, p.products_price) >= :price_from)';
    }

    if ($pto > 0) {
      $search_query .= ' and (IF(s.status, s.specials_new_products_price, p.products_price) <= :price_to)';
    }
  }

  if ( (DISPLAY_PRICE_WITH_TAX == 'true') && (tep_not_null($pfrom) || tep_not_null($pto)) ) {
    $search_query .= ' group by p.products_id, tr.tax_priority';
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

  if ( (DISPLAY_PRICE_WITH_TAX == 'true') && (tep_not_null($pfrom) || tep_not_null($pto)) ) {
    $Qlisting->bindInt(':zone_country_id', $_SESSION['customer_country_id']);
    $Qlisting->bindInt(':zone_id', $_SESSION['customer_zone_id']);
  }

  $Qlisting->bindInt(':language_id', $OSCOM_Language->getId());

  if (isset($_GET['categories_id']) && tep_not_null($_GET['categories_id'])) {
    $Qlisting->bindInt(':categories_id', $_GET['categories_id']);

    if (isset($_GET['inc_subcat']) && ($_GET['inc_subcat'] == '1')) {
      for ($i=0, $n=sizeof($subcategories_array); $i<$n; $i++ ) {
        $Qlisting->bindInt(':categories_id_' . $i, $subcategories_array[$i]);
      }
    }
  }

  if (isset($_GET['manufacturers_id']) && tep_not_null($_GET['manufacturers_id'])) {
    $Qlisting->bindInt(':manufacturers_id', $_GET['manufacturers_id']);
  }

  if (isset($search_keywords) && (sizeof($search_keywords) > 0)) {
    for ($i=0, $n=sizeof($search_keywords); $i<$n; $i++ ) {
      $Qlisting->bindValue(':products_name_' . $i, '%' . $search_keywords[$i] . '%');
      $Qlisting->bindValue(':products_model_' . $i, '%' . $search_keywords[$i] . '%');
      $Qlisting->bindValue(':manufacturers_name_' . $i, '%' . $search_keywords[$i] . '%');

      if (isset($_GET['search_in_description']) && ($_GET['search_in_description'] == '1')) {
        $Qlisting->bindValue(':products_description_' . $i, '%' . $search_keywords[$i] . '%');
      }
    }
  }

  if (isset($dfromDateTime) && $dfromDateTime->isValid()) {
    $Qlisting->bindValue(':products_date_added_from', $dfromDateTime->getRaw(false));
  }

  if (isset($dtoDateTime) && $dtoDateTime->isValid()) {
    $Qlisting->bindValue(':products_date_added_to', $dtoDateTime->getRaw(false));
  }

  if (DISPLAY_PRICE_WITH_TAX == 'true') {
    if ($pfrom > 0) {
      $Qlisting->bindDecimal(':price_from', $pfrom);
    }

    if ($pto > 0) {
      $Qlisting->bindDecimal(':price_to', $pto);
    }
  } else {
    if ($pfrom > 0) {
      $Qlisting->bindDecimal(':price_from', $pfrom);
    }

    if ($pto > 0) {
      $Qlisting->bindDecimal(':price_to', $pto);
    }
  }

  $Qlisting->setPageSet(isset($_GET['view']) && ($_GET['view'] == 'all') ? 999999 : MAX_DISPLAY_SEARCH_RESULTS);
  $Qlisting->execute();

  require('includes/content/product_listing.php');
?>

  <br />

  <div class="buttonSet">
    <?php echo HTML::button(OSCOM::getDef('image_button_back'), 'fa fa-angle-left', OSCOM::link('advanced_search.php', tep_get_all_get_params(array('sort', 'page')), true, false)); ?>
  </div>
</div>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
