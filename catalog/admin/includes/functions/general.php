<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\Cache;
  use OSC\OM\HTML;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

////
// Parse the data used in the html tags to ensure the tags will not break
  function tep_parse_input_field_data($data, $parse) {
    return strtr(trim($data), $parse);
  }

  function tep_customers_name($customers_id) {
    $Qcustomer = Registry::get('Db')->get('customers', [
      'customers_firstname',
      'customers_lastname'
    ], [
      'customers_id' => (int)$customers_id
    ]);

    return $Qcustomer->value('customers_firstname') . ' ' . $Qcustomer->value('customers_lastname');
  }

  function tep_get_path($current_category_id = '') {
    global $cPath_array;

    $OSCOM_Db = Registry::get('Db');

    if ($current_category_id == '') {
      $cPath_new = implode('_', $cPath_array);
    } else {
      if (sizeof($cPath_array) == 0) {
        $cPath_new = $current_category_id;
      } else {
        $cPath_new = '';

        $Qlast = $OSCOM_Db->get('categories', 'parent_id', ['categories_id' => (int)$cPath_array[(sizeof($cPath_array)-1)]]);

        $Qcurrent = $OSCOM_Db->get('categories', 'parent_id', ['categories_id' => (int)$current_category_id]);

        if ($Qlast->valueInt('parent_id') === $Qcurrent->valueInt('parent_id')) {
          for ($i = 0, $n = sizeof($cPath_array) - 1; $i < $n; $i++) {
            $cPath_new .= '_' . $cPath_array[$i];
          }
        } else {
          for ($i = 0, $n = sizeof($cPath_array); $i < $n; $i++) {
            $cPath_new .= '_' . $cPath_array[$i];
          }
        }

        $cPath_new .= '_' . $current_category_id;

        if (substr($cPath_new, 0, 1) == '_') {
          $cPath_new = substr($cPath_new, 1);
        }
      }
    }

    return 'cPath=' . $cPath_new;
  }

  function tep_get_all_get_params($exclude_array = '') {

    if ($exclude_array == '') {
      $exclude_array = array();
    } elseif (is_string($exclude_array)) {
      $exclude_array = [
        $exclude_array
      ];
    }

    $get_url = '';

    foreach ( $_GET as $key => $value ) {
      if (($key != session_name()) && ($key != 'error') && (!in_array($key, $exclude_array))) $get_url .= $key . '=' . $value . '&';
    }

    return $get_url;
  }

  function tep_get_category_tree($parent_id = '0', $spacing = '', $exclude = '', $category_tree_array = '', $include_itself = false) {
    $OSCOM_Db = Registry::get('Db');
    $OSCOM_Language = Registry::get('Language');

    if (!is_array($category_tree_array)) $category_tree_array = array();
    if ( (sizeof($category_tree_array) < 1) && ($exclude != '0') ) $category_tree_array[] = array('id' => '0', 'text' => OSCOM::getDef('text_top'));

    if ($include_itself) {
      $Qcategory = $OSCOM_Db->get('categories_description', 'cd.categories_name', ['cd.language_id' => $OSCOM_Language->getId(), 'cd.categories_id' => (int)$parent_id]);

      $category_tree_array[] = [
        'id' => $parent_id,
        'text' => $Qcategory->value('categories_name')
      ];
    }

    $Qcategories = $OSCOM_Db->get([
      'categories c',
      'categories_description cd'
    ], [
      'c.categories_id',
      'cd.categories_name',
      'c.parent_id'
    ], [
      'c.categories_id' => [
        'rel' => 'cd.categories_id'
      ],
      'cd.language_id' => $OSCOM_Language->getId(),
      'c.parent_id' => (int)$parent_id
    ], [
      'c.sort_order',
      'cd.categories_name'
    ]);

    while ($Qcategories->fetch()) {
      if ($exclude != $Qcategories->valueInt('categories_id')) $category_tree_array[] = array('id' => $Qcategories->valueInt('categories_id'), 'text' => $spacing . $Qcategories->value('categories_name'));
      $category_tree_array = tep_get_category_tree($Qcategories->valueInt('categories_id'), $spacing . '&nbsp;&nbsp;&nbsp;', $exclude, $category_tree_array);
    }

    return $category_tree_array;
  }

  function tep_draw_products_pull_down($name, $parameters = '', $exclude = '') {
    global $currencies;

    $OSCOM_Db = Registry::get('Db');
    $OSCOM_Language = Registry::get('Language');

    if ($exclude == '') {
      $exclude = array();
    }

    $select_string = '<select name="' . $name . '"';

    if ($parameters) {
      $select_string .= ' ' . $parameters;
    }

    $select_string .= '>';

    $Qproducts = $OSCOM_Db->get([
      'products p',
      'products_description pd'
    ], [
      'p.products_id',
      'pd.products_name',
      'p.products_price'
    ], [
      'p.products_id' => [
        'rel' => 'pd.products_id'
      ],
      'pd.language_id' => $OSCOM_Language->getId()
    ], 'products_name');

    while ($Qproducts->fetch()) {
      if (!in_array($Qproducts->valueInt('products_id'), $exclude)) {
        $select_string .= '<option value="' . $Qproducts->valueInt('products_id') . '">' . $Qproducts->value('products_name') . ' (' . $currencies->format($Qproducts->value('products_price')) . ')</option>';
      }
    }

    $select_string .= '</select>';

    return $select_string;
  }

  function tep_format_system_info_array($array) {

    $output = '';
    foreach ($array as $section => $child) {
      $output .= '[' . $section . ']' . "\n";
      foreach ($child as $variable => $value) {
        if (is_array($value)) {
          $output .= $variable . ' = ' . implode(',', $value) ."\n";
        } else {
          $output .= $variable . ' = ' . $value . "\n";
        }
      }

    $output .= "\n";
    }
    return trim($output);

  }

  function tep_options_name($options_id) {
    $OSCOM_Db = Registry::get('Db');
    $OSCOM_Language = Registry::get('Language');

    $Qoptions = $OSCOM_Db->get('products_options', 'products_options_name', ['products_options_id' => (int)$options_id, 'language_id' => $OSCOM_Language->getId()]);

    return $Qoptions->value('products_options_name');
  }

  function tep_values_name($values_id) {
    $OSCOM_Db = Registry::get('Db');
    $OSCOM_Language = Registry::get('Language');

    $Qvalues = $OSCOM_Db->get('products_options_values', 'products_options_values_name', ['products_options_values_id' => (int)$values_id, 'language_id' => $OSCOM_Language->getId()]);

    return $Qvalues->value('products_options_values_name');
  }

  function tep_info_image($image, $alt, $width = '', $height = '') {
    if (tep_not_null($image) && is_file(OSCOM::getConfig('dir_root', 'Shop') . 'images/' . $image)) {
      $image = HTML::image(OSCOM::linkImage('Shop/' . $image), $alt, $width, $height);
    } else {
      $image = TEXT_IMAGE_NONEXISTENT;
    }

    return $image;
  }

  function tep_break_string($string, $len, $break_char = '-') {
    $l = 0;
    $output = '';
    for ($i=0, $n=strlen($string); $i<$n; $i++) {
      $char = substr($string, $i, 1);
      if ($char != ' ') {
        $l++;
      } else {
        $l = 0;
      }
      if ($l > $len) {
        $l = 1;
        $output .= $break_char;
      }
      $output .= $char;
    }

    return $output;
  }

  function tep_get_country_name($country_id) {
    $Qcountry = Registry::get('Db')->get('countries', 'countries_name', ['countries_id' => (int)$country_id]);

    if ($Qcountry->fetch() !== false) {
      return $Qcountry->value('countries_name');
    }

    return $country_id;
  }

  function tep_get_zone_name($country_id, $zone_id, $default_zone) {
    $Qzone = Registry::get('Db')->get('zones', 'zone_name', ['zone_country_id' => (int)$country_id, 'zone_id' => (int)$zone_id]);

    if ($Qzone->fetch() !== false) {
      return $Qzone->value('zone_name');
    }

    return $default_zone;
  }

  function tep_not_null($value) {
    if (is_array($value)) {
      if (sizeof($value) > 0) {
        return true;
      } else {
        return false;
      }
    } else {
      if ( (is_string($value) || is_int($value)) && ($value != '') && ($value != 'NULL') && (strlen(trim($value)) > 0)) {
        return true;
      } else {
        return false;
      }
    }
  }

  function tep_tax_classes_pull_down($parameters, $selected = '') {
    $select_string = '<select ' . $parameters . '>';

    $Qclasses = Registry::get('Db')->get('tax_class', [
      'tax_class_id',
      'tax_class_title'
    ], null, 'tax_class_title');

    while ($Qclasses->fetch()) {
      $select_string .= '<option value="' . $Qclasses->valueInt('tax_class_id') . '"';

      if ($selected == $Qclasses->valueInt('tax_class_id')) {
        $select_string .= ' SELECTED';
      }

      $select_string .= '>' . $Qclasses->value('tax_class_title') . '</option>';
    }

    $select_string .= '</select>';

    return $select_string;
  }

  function tep_geo_zones_pull_down($parameters, $selected = '') {
    $select_string = '<select ' . $parameters . '>';

    $Qzones = Registry::get('Db')->get('geo_zones', [
      'geo_zone_id',
      'geo_zone_name'
    ], null, 'geo_zone_name');

    while ($Qzones->fetch()) {
      $select_string .= '<option value="' . $Qzones->valueInt('geo_zone_id') . '"';

      if ($selected == $Qzones->valueInt('geo_zone_id')) {
        $select_string .= ' SELECTED';
      }

      $select_string .= '>' . $Qzones->value('geo_zone_name') . '</option>';
    }

    $select_string .= '</select>';

    return $select_string;
  }

  function tep_get_geo_zone_name($geo_zone_id) {
    $Qzones = Registry::get('Db')->get('geo_zones', 'geo_zone_name', ['geo_zone_id' => (int)$geo_zone_id]);

    if ($Qzones->fetch() !== false) {
      return $Qzones->value('geo_zone_name');
    }

    return $geo_zone_id;
  }

  function tep_address_format($address_format_id, $address, $html, $boln, $eoln) {
    $Qaddress = Registry::get('Db')->get('address_format', 'address_format', ['address_format_id' => (int)$address_format_id]);

    $replace = [
      '$company' => HTML::outputProtected($address['company']),
      '$firstname' => '',
      '$lastname' => '',
      '$street' => HTML::outputProtected($address['street_address']),
      '$suburb' => HTML::outputProtected($address['suburb']),
      '$city' => HTML::outputProtected($address['city']),
      '$state' => HTML::outputProtected($address['state']),
      '$postcode' => HTML::outputProtected($address['postcode']),
      '$country' => ''
    ];

    if (isset($address['firstname']) && tep_not_null($address['firstname'])) {
      $replace['$firstname'] = HTML::outputProtected($address['firstname']);
      $replace['$lastname'] = HTML::outputProtected($address['lastname']);
    } elseif (isset($address['name']) && tep_not_null($address['name'])) {
      $replace['$firstname'] = HTML::outputProtected($address['name']);
    }

    if (isset($address['country_id']) && tep_not_null($address['country_id'])) {
      $replace['$country'] = tep_get_country_name($address['country_id']);

      if (isset($address['zone_id']) && tep_not_null($address['zone_id'])) {
        $replace['$state'] = tep_get_zone_code($address['country_id'], $address['zone_id'], $replace['$state']);
      }
    } elseif (isset($address['country']) && tep_not_null($address['country'])) {
      $replace['$country'] = HTML::outputProtected($address['country']);
    }

    $replace['$zip'] = $replace['$postcode'];

    if ($html) {
// HTML Mode
      $HR = '<hr />';
      $hr = '<hr />';
      if ( ($boln == '') && ($eoln == "\n") ) { // Values not specified, use rational defaults
        $CR = '<br />';
        $cr = '<br />';
        $eoln = $cr;
      } else { // Use values supplied
        $CR = $eoln . $boln;
        $cr = $CR;
      }
    } else {
// Text Mode
      $CR = $eoln;
      $cr = $CR;
      $HR = '----------------------------------------';
      $hr = '----------------------------------------';
    }

    $replace['$CR'] = $CR;
    $replace['$cr'] = $cr;
    $replace['$HR'] = $HR;
    $replace['$hr'] = $hr;

    $replace['$statecomma'] = '';
    $replace['$streets'] = $replace['$street'];
    if ($replace['$suburb'] != '') $replace['$streets'] = $replace['$street'] . $replace['$cr'] . $replace['$suburb'];
    if ($replace['$state'] != '') $replace['$statecomma'] = $replace['$state'] . ', ';

    $address = strtr($Qaddress->value('address_format'), $replace);

    if ( (ACCOUNT_COMPANY == 'true') && tep_not_null($replace['$company']) ) {
      $address = $replace['$company'] . $replace['$cr'] . $address;
    }

    return $address;
  }

  ////////////////////////////////////////////////////////////////////////////////////////////////
  //
  // Function    : tep_get_zone_code
  //
  // Arguments   : country           country code string
  //               zone              state/province zone_id
  //               def_state         default string if zone==0
  //
  // Return      : state_prov_code   state/province code
  //
  // Description : Function to retrieve the state/province code (as in FL for Florida etc)
  //
  ////////////////////////////////////////////////////////////////////////////////////////////////
  function tep_get_zone_code($country, $zone, $def_state) {
    $Qstate = Registry::get('Db')->get('zones', 'zone_code', ['zone_country_id' => (int)$country, 'zone_id' => (int)$zone]);

    if ($Qstate->fetch() !== false) {
      return $Qstate->value('zone_code');
    }

    return $def_state;
  }

  function tep_get_uprid($prid, $params) {
    $uprid = $prid;
    if ( (is_array($params)) && (!strstr($prid, '{')) ) {
      foreach ( $params as $option => $value ) {
        $uprid = $uprid . '{' . $option . '}' . $value;
      }
    }

    return $uprid;
  }

  function tep_get_prid($uprid) {
    $pieces = explode('{', $uprid);

    return $pieces[0];
  }

  function tep_get_languages() {
    $languages_array = [];

    $Qlanguages = Registry::get('Db')->get('languages', [
      'languages_id',
      'name',
      'code',
      'image',
      'directory'
    ], null, 'sort_order');

    while ($Qlanguages->fetch()) {
      $languages_array[] = [
        'id' => $Qlanguages->valueInt('languages_id'),
        'name' => $Qlanguages->value('name'),
        'code' => $Qlanguages->value('code'),
        'image' => $Qlanguages->value('image'),
        'directory' => $Qlanguages->value('directory')
      ];
    }

    return $languages_array;
  }

  function tep_get_category_name($category_id, $language_id) {
    $Qcategory = Registry::get('Db')->get('categories_description', 'categories_name', ['categories_id' => (int)$category_id, 'language_id' => (int)$language_id]);

    return $Qcategory->value('categories_name');
  }

  function tep_get_orders_status_name($orders_status_id, $language_id = '') {
    $OSCOM_Db = Registry::get('Db');
    $OSCOM_Language = Registry::get('Language');

    if (empty($language_id) || !is_numeric($language_id)) $language_id = $OSCOM_Language->getId();

    $Qstatus = $OSCOM_Db->get('orders_status', 'orders_status_name', ['orders_status_id' => (int)$orders_status_id, 'language_id' => (int)$language_id]);

    return $Qstatus->value('orders_status_name');
  }

  function tep_get_orders_status() {
    $OSCOM_Db = Registry::get('Db');
    $OSCOM_Language = Registry::get('Language');

    $orders_status_array = [];

    $Qstatus = $OSCOM_Db->get('orders_status', [
      'orders_status_id',
      'orders_status_name'
    ], [
      'language_id' => $OSCOM_Language->getId()
    ], 'orders_status_id');

    while ($Qstatus->fetch()) {
      $orders_status_array[] = [
        'id' => $Qstatus->valueInt('orders_status_id'),
        'text' => $Qstatus->value('orders_status_name')
      ];
    }

    return $orders_status_array;
  }

  function tep_get_products_name($product_id, $language_id = 0) {
    $OSCOM_Db = Registry::get('Db');
    $OSCOM_Language = Registry::get('Language');

    if (empty($language_id) || !is_numeric($language_id)) $language_id = $OSCOM_Language->getId();

    $Qproduct = Registry::get('Db')->get('products_description', 'products_name', ['products_id' => (int)$product_id, 'language_id' => (int)$language_id]);

    return $Qproduct->value('products_name');
  }

  function tep_get_products_description($product_id, $language_id) {
    $Qproduct = Registry::get('Db')->get('products_description', 'products_description', ['products_id' => (int)$product_id, 'language_id' => (int)$language_id]);

    return $Qproduct->value('products_description');
  }

  function tep_get_products_url($product_id, $language_id) {
    $Qproduct = Registry::get('Db')->get('products_description', 'products_url', ['products_id' => (int)$product_id, 'language_id' => (int)$language_id]);

    return $Qproduct->value('products_url');
  }

////
// Return the manufacturers URL in the needed language
// TABLES: manufacturers_info
  function tep_get_manufacturer_url($manufacturer_id, $language_id) {
    $Qmanufacturer = Registry::get('Db')->get('manufacturers_info', 'manufacturers_url', ['manufacturers_id' => (int)$manufacturer_id, 'languages_id' => (int)$language_id]);

    return $Qmanufacturer->value('manufacturers_url');
  }

////
// Count how many products exist in a category
// TABLES: products, products_to_categories, categories
  function tep_products_in_category_count($categories_id, $include_deactivated = false) {
    $OSCOM_Db = Registry::get('Db');

    if ($include_deactivated) {
      $Qproducts = $OSCOM_Db->get([
        'products p',
        'products_to_categories p2c'
      ], [
        'count(*) as total'
      ], [
        'p.products_id' => [
          'rel' => 'p2c.products_id'
        ],
        'p2c.categories_id' => (int)$categories_id
      ]);
    } else {
      $Qproducts = $OSCOM_Db->get([
        'products p',
        'products_to_categories p2c'
      ], [
        'count(*) as total'
      ], [
        'p.products_id' => [
          'rel' => 'p2c.products_id'
        ],
        'p.products_status' => '1',
        'p2c.categories_id' => (int)$categories_id
      ]);
    }

    $products_count = $Qproducts->valueInt('total');

    $Qchildren = $OSCOM_Db->get('categories', 'categories_id', ['parent_id' => (int)$categories_id]);

    while ($Qchildren->fetch()) {
      $products_count += call_user_func(__FUNCTION__, $Qchildren->valueInt('categories_id'), $include_deactivated);
    }

    return $products_count;
  }

////
// Count how many subcategories exist in a category
// TABLES: categories
  function tep_childs_in_category_count($categories_id) {
    $categories_count = 0;

    $Qcategories = Registry::get('Db')->get('categories', 'categories_id', ['parent_id' => (int)$categories_id]);

    while ($Qcategories->fetch()) {
      $categories_count++;

      $categories_count += call_user_func(__FUNCTION__, $Qcategories->valueInt('categories_id'));
    }

    return $categories_count;
  }

////
// Returns an array with countries
// TABLES: countries
  function tep_get_countries($default = '') {
    $countries_array = array();
    if ($default) {
      $countries_array[] = array('id' => '',
                                 'text' => $default);
    }

    $Qcountries = Registry::get('Db')->get('countries', ['countries_id', 'countries_name'], null, 'countries_name');

    while ($Qcountries->fetch()) {
      $countries_array[] = [
        'id' => $Qcountries->valueInt('countries_id'),
        'text' => $Qcountries->value('countries_name')
      ];
    }

    return $countries_array;
  }

////
// return an array with country zones
  function tep_get_country_zones($country_id) {
    $zones_array = array();

    $Qzones = Registry::get('Db')->get('zones', [
      'zone_id',
      'zone_name'
    ], [
      'zone_country_id' => (int)$country_id
    ], 'zone_name');

    while ($Qzones->fetch()) {
      $zones_array[] = [
        'id' => $Qzones->valueInt('zone_id'),
        'text' => $Qzones->value('zone_name')
      ];
    }

    return $zones_array;
  }

  function tep_prepare_country_zones_pull_down($country_id = '') {
    $zones = tep_get_country_zones($country_id);

    if (sizeof($zones) > 0) {
      $zones_select = array(array('id' => '', 'text' => PLEASE_SELECT));
      $zones = array_merge($zones_select, $zones);
    } else {
      $zones = array(array('id' => '', 'text' => TYPE_BELOW));
    }

    return $zones;
  }

////
// Get list of address_format_id's
  function tep_get_address_formats() {
    $address_format_array = [];

    $Qaddress = Registry::get('Db')->get('address_format', 'address_format_id', null, 'address_format_id');

    while ($Qaddress->fetch()) {
      $address_format_array[] = [
        'id' => $Qaddress->valueInt('address_format_id'),
        'text' => $Qaddress->valueInt('address_format_id')
      ];
    }

    return $address_format_array;
  }

////
// Alias function for Store configuration values in the Administration Tool
  function tep_cfg_pull_down_country_list($country_id) {
    return HTML::selectField('configuration_value', tep_get_countries(), $country_id);
  }

  function tep_cfg_pull_down_zone_list($zone_id) {
    return HTML::selectField('configuration_value', tep_get_country_zones(STORE_COUNTRY), $zone_id);
  }

  function tep_cfg_pull_down_tax_classes($tax_class_id, $key = '') {
    $name = tep_not_null($key) ? 'configuration[' . $key . ']' : 'configuration_value';

    $tax_class_array = [
      [
        'id' => '0',
        'text' => OSCOM::getDef('text_none')
      ]
    ];

    $Qclass = Registry::get('Db')->get('tax_class', ['tax_class_id', 'tax_class_title'], null, 'tax_class_title');

    while ($Qclass->fetch()) {
      $tax_class_array[] = [
        'id' => $Qclass->valueInt('tax_class_id'),
        'text' => $Qclass->value('tax_class_title')
      ];
    }

    return HTML::selectField($name, $tax_class_array, $tax_class_id);
  }

////
// Function to read in text area in admin
 function tep_cfg_textarea($text, $key = '') {
    $name = tep_not_null($key) ? 'configuration[' . $key . ']' : 'configuration_value';

    return HTML::textareaField($name, 35, 5, $text);
  }

  function tep_cfg_get_zone_name($zone_id) {
    $Qzone = Registry::get('Db')->get('zones', 'zone_name', ['zone_id' => (int)$zone_id]);

    if ($Qzone->fetch()) {
      return $Qzone->value('zone_name');
    }

    return $zone_id;
  }

////
// Sets the status of a banner
  function tep_set_banner_status($banners_id, $status) {
    $OSCOM_Db = Registry::get('Db');

    if ($status == '1') {
      return $OSCOM_Db->save('banners', [
        'status' => '1',
        'expires_impressions' => 'null',
        'expires_date' => 'null',
        'date_status_change' => 'null'
      ], [
        'banners_id' => (int)$banners_id
      ]);
    } elseif ($status == '0') {
      return $OSCOM_Db->save('banners', [
        'status' => '0',
        'date_status_change' => 'now()'
      ], [
        'banners_id' => (int)$banners_id
      ]);
    } else {
      return -1;
    }
  }

////
// Sets the status of a product
  function tep_set_product_status($products_id, $status) {
    $OSCOM_Db = Registry::get('Db');

    if ($status == '1') {
      return $OSCOM_Db->save('products', [
        'products_status' => '1',
        'products_last_modified' => 'now()'
      ], [
        'products_id' => (int)$products_id
      ]);
    } elseif ($status == '0') {
      return $OSCOM_Db->save('products', [
        'products_status' => '0',
        'products_last_modified' => 'now()'
      ], [
        'products_id' => (int)$products_id
      ]);
    } else {
      return -1;
    }
  }

////
// Sets the status of a review
  function tep_set_review_status($reviews_id, $status) {
    $OSCOM_Db = Registry::get('Db');

    if ($status == '1') {
      return $OSCOM_Db->save('reviews', [
        'reviews_status' => '1',
        'last_modified' => 'now()'
      ], [
        'reviews_id' => (int)$reviews_id
      ]);
    } elseif ($status == '0') {
      return $OSCOM_Db->save('reviews', [
        'reviews_status' => '0',
        'last_modified' => 'now()'
      ], [
        'reviews_id' => (int)$reviews_id
      ]);
    } else {
      return -1;
    }
  }

////
// Sets the status of a product on special
  function tep_set_specials_status($specials_id, $status) {
    $OSCOM_Db = Registry::get('Db');

    if ($status == '1') {
      return $OSCOM_Db->save('specials', [
        'status' => '1',
        'expires_date' => 'null',
        'date_status_change' => 'null'
      ], [
        'specials_id' => (int)$specials_id
      ]);
    } elseif ($status == '0') {
      return $OSCOM_Db->save('specials', [
        'status' => '0',
        'date_status_change' => 'now()'
      ], [
        'specials_id' => (int)$specials_id
      ]);
    } else {
      return -1;
    }
  }

////
// Sets timeout for the current script.
// Cant be used in safe mode.
  function tep_set_time_limit($limit) {
    if (!get_cfg_var('safe_mode')) {
      set_time_limit($limit);
    }
  }

////
// Alias function for Store configuration values in the Administration Tool
  function tep_cfg_select_option($select_array, $key_value, $key = '') {
    $string = '';

    for ($i=0, $n=sizeof($select_array); $i<$n; $i++) {
      $name = ((tep_not_null($key)) ? 'configuration[' . $key . ']' : 'configuration_value');

      $string .= '<br /><input type="radio" name="' . $name . '" value="' . $select_array[$i] . '"';

      if ($key_value == $select_array[$i]) $string .= ' checked="checked"';

      $string .= ' /> ' . $select_array[$i];
    }

    return $string;
  }

////
// Alias function for module configuration keys
  function tep_mod_select_option($select_array, $key_name, $key_value) {
    foreach ( $select_array as $key => $value ) {
      if (is_int($key)) $key = $value;
      $string .= '<br /><input type="radio" name="configuration[' . $key_name . ']" value="' . $key . '"';
      if ($key_value == $key) $string .= ' checked="checked"';
      $string .= ' /> ' . $value;
    }

    return $string;
  }

////
// Retreive server information
  function tep_get_system_information() {
    $OSCOM_Db = Registry::get('Db');

    $Qdate = $OSCOM_Db->query('select now() as datetime');

    @list($system, $host, $kernel) = preg_split('/[\s,]+/', @exec('uname -a'), 5);

    $data = array();

    $data['oscommerce']  = array('version' => OSCOM::getVersion());

    $data['system'] = array('date' => date('Y-m-d H:i:s O T'),
                            'os' => PHP_OS,
                            'kernel' => $kernel,
                            'uptime' => @exec('uptime'),
                            'http_server' => $_SERVER['SERVER_SOFTWARE']);

    $data['mysql']  = array('version' => $OSCOM_Db->getAttribute(\PDO::ATTR_SERVER_VERSION),
                            'date' => $Qdate->value('datetime'));

    $data['php']    = array('version' => PHP_VERSION,
                            'zend' => zend_version(),
                            'sapi' => PHP_SAPI,
                            'int_size'	=> defined('PHP_INT_SIZE') ? PHP_INT_SIZE : '',
                            'safe_mode'	=> (int) @ini_get('safe_mode'),
                            'open_basedir' => (int) @ini_get('open_basedir'),
                            'memory_limit' => @ini_get('memory_limit'),
                            'error_reporting' => error_reporting(),
                            'display_errors' => (int)@ini_get('display_errors'),
                            'allow_url_fopen' => (int) @ini_get('allow_url_fopen'),
                            'allow_url_include' => (int) @ini_get('allow_url_include'),
                            'file_uploads' => (int) @ini_get('file_uploads'),
                            'upload_max_filesize' => @ini_get('upload_max_filesize'),
                            'post_max_size' => @ini_get('post_max_size'),
                            'disable_functions' => @ini_get('disable_functions'),
                            'disable_classes' => @ini_get('disable_classes'),
                            'enable_dl'	=> (int) @ini_get('enable_dl'),
                            'filter.default'   => @ini_get('filter.default'),
                            'default_charset' => @ini_get('default_charset'),
                            'mbstring.func_overload' => @ini_get('mbstring.func_overload'),
                            'mbstring.internal_encoding' => @ini_get('mbstring.internal_encoding'),
                            'unicode.semantics' => (int) @ini_get('unicode.semantics'),
                            'zend_thread_safty'	=> (int) function_exists('zend_thread_id'),
                            'opcache.enable' => @ini_get('opcache.enable'),
                            'extensions' => get_loaded_extensions());

    return $data;
  }

  function tep_generate_category_path($id, $from = 'category', $categories_array = '', $index = 0) {
    $OSCOM_Db = Registry::get('Db');
    $OSCOM_Language = Registry::get('Language');

    if (!is_array($categories_array)) {
      $categories_array = [];
    }

    if ($from == 'product') {
      $Qcategories = $OSCOM_Db->get('products_to_categories', 'categories_id', ['products_id' => (int)$id]);

      while ($Qcategories->fetch()) {
        if ($Qcategories->valueInt('categories_id') === 0) {
          $categories_array[$index][] = [
            'id' => '0',
            'text' => OSCOM::getDef('text_top')
          ];
        } else {
          $Qcategory = $OSCOM_Db->get([
            'categories c',
            'categories_description cd'
          ], [
            'cd.categories_name',
            'c.parent_id'
          ], [
            'c.categories_id' => [
              'val' => $Qcategories->valueInt('categories_id'),
              'rel' => 'cd.categories_id'
            ],
            'cd.language_id' => $OSCOM_Language->getId()
          ]);

          $categories_array[$index][] = [
            'id' => $Qcategories->valueInt('categories_id'),
            'text' => $Qcategory->value('categories_name')
          ];

          if ($Qcategory->valueInt('parent_id') > 0) {
            $categories_array = call_user_func(__FUNCTION__, $Qcategory->valueInt('parent_id'), 'category', $categories_array, $index);
          }

          $categories_array[$index] = array_reverse($categories_array[$index]);
        }

        $index++;
      }
    } elseif ($from == 'category') {
      $Qcategory = $OSCOM_Db->get([
        'categories c',
        'categories_description cd'
      ], [
        'cd.categories_name',
        'c.parent_id'
      ], [
        'c.categories_id' => [
          'val' => (int)$id,
          'rel' => 'cd.categories_id'
        ],
        'cd.language_id' => $OSCOM_Language->getId()
      ]);

      $categories_array[$index][] = [
        'id' => (int)$id,
        'text' => $Qcategory->value('categories_name')
      ];

      if ($Qcategory->valueInt('parent_id') > 0) {
        $categories_array = call_user_func(__FUNCTION__, $Qcategory->valueInt('parent_id'), 'category', $categories_array, $index);
      }
    }

    return $categories_array;
  }

  function tep_output_generated_category_path($id, $from = 'category') {
    $calculated_category_path_string = '';
    $calculated_category_path = tep_generate_category_path($id, $from);
    for ($i=0, $n=sizeof($calculated_category_path); $i<$n; $i++) {
      for ($j=0, $k=sizeof($calculated_category_path[$i]); $j<$k; $j++) {
        $calculated_category_path_string .= $calculated_category_path[$i][$j]['text'] . '&nbsp;&gt;&nbsp;';
      }
      $calculated_category_path_string = substr($calculated_category_path_string, 0, -16) . '<br />';
    }
    $calculated_category_path_string = substr($calculated_category_path_string, 0, -6);

    if (strlen($calculated_category_path_string) < 1) $calculated_category_path_string = OSCOM::getDef('text_top');

    return $calculated_category_path_string;
  }

  function tep_get_generated_category_path_ids($id, $from = 'category') {
    $calculated_category_path_string = '';
    $calculated_category_path = tep_generate_category_path($id, $from);
    for ($i=0, $n=sizeof($calculated_category_path); $i<$n; $i++) {
      for ($j=0, $k=sizeof($calculated_category_path[$i]); $j<$k; $j++) {
        $calculated_category_path_string .= $calculated_category_path[$i][$j]['id'] . '_';
      }
      $calculated_category_path_string = substr($calculated_category_path_string, 0, -1) . '<br />';
    }
    $calculated_category_path_string = substr($calculated_category_path_string, 0, -6);

    if (strlen($calculated_category_path_string) < 1) $calculated_category_path_string = OSCOM::getDef('text_top');

    return $calculated_category_path_string;
  }

  function tep_remove_category($category_id) {
    $OSCOM_Db = Registry::get('Db');

    $Qimage = $OSCOM_Db->get('categories', 'categories_image', ['categories_id' => (int)$category_id]);

    $Qduplicate = $OSCOM_Db->get('categories', 'categories_id', [
      'categories_image' => $Qimage->value('categories_image'),
      'categories_id' => [
        'op' => '!=',
        'val' => (int)$category_id
      ]
    ], null, 1);

    if ($Qduplicate->fetch() === false) {
      if (!empty($Qimage->value('categories_image')) && is_file(OSCOM::getConfig('dir_root', 'Shop') . 'images/' . $Qimage->value('categories_image'))) {
        unlink(OSCOM::getConfig('dir_root', 'Shop') . 'images/' . $Qimage->value('categories_image'));
      }
    }

    $OSCOM_Db->delete('categories', ['categories_id' => (int)$category_id]);
    $OSCOM_Db->delete('categories_description', ['categories_id' => (int)$category_id]);
    $OSCOM_Db->delete('products_to_categories', ['categories_id' => (int)$category_id]);

    Cache::clear('categories');
    Cache::clear('products-also_purchased');
  }

  function tep_remove_product($product_id) {
    $OSCOM_Db = Registry::get('Db');

    $Qimage = $OSCOM_Db->get('products', 'products_image', ['products_id' => (int)$product_id]);

    $Qduplicate = $OSCOM_Db->get('products', 'products_id', [
      'products_image' => $Qimage->value('products_image'),
      'products_id' => [
        'op' => '!=',
        'val' => (int)$product_id
      ]
    ], null, 1);

    if ($Qduplicate->fetch() === false) {
      if (!empty($Qimage->value('products_image')) && is_file(OSCOM::getConfig('dir_root', 'Shop') . 'images/' . $Qimage->value('products_image'))) {
        unlink(OSCOM::getConfig('dir_root', 'Shop') . 'images/' . $Qimage->value('products_image'));
      }
    }

    $Qimages = $OSCOM_Db->get('products_images', 'image', ['products_id' => (int)$product_id]);

    if ($Qimages->fetch() !== false) {
      do {
        $Qduplicate = $OSCOM_Db->get('products_images', 'id', [
          'image' => $Qimages->value('image'),
          'products_id' => [
            'op' => '!=',
            'val' => (int)$product_id
          ]
        ], null, 1);

        if ($Qduplicate->fetch() === false) {
          if (is_file(OSCOM::getConfig('dir_root', 'Shop') . 'images/' . $Qimages->value('image'))) {
            unlink(OSCOM::getConfig('dir_root', 'Shop') . 'images/' . $Qimages->value('image'));
          }
        }
      } while ($Qimages->fetch());

      $OSCOM_Db->delete('products_images', ['products_id' => (int)$product_id]);
    }

    $OSCOM_Db->delete('specials', ['products_id' => (int)$product_id]);
    $OSCOM_Db->delete('products', ['products_id' => (int)$product_id]);
    $OSCOM_Db->delete('products_to_categories', ['products_id' => (int)$product_id]);
    $OSCOM_Db->delete('products_description', ['products_id' => (int)$product_id]);
    $OSCOM_Db->delete('products_attributes', ['products_id' => (int)$product_id]);

    $Qdel = $OSCOM_Db->prepare('delete from :table_customers_basket where products_id = :products_id or products_id like :products_id_att');
    $Qdel->bindInt(':products_id', (int)$product_id);
    $Qdel->bindInt(':products_id_att', (int)$product_id . '{%');
    $Qdel->execute();

    $Qdel = $OSCOM_Db->prepare('delete from :table_customers_basket_attributes where products_id = :products_id or products_id like :products_id_att');
    $Qdel->bindInt(':products_id', (int)$product_id);
    $Qdel->bindInt(':products_id_att', (int)$product_id . '{%');
    $Qdel->execute();

    $Qreviews = $OSCOM_Db->get('reviews', 'reviews_id', ['products_id' => (int)$product_id]);

    while ($Qreviews->fetch()) {
      $OSCOM_Db->delete('reviews_description', ['reviews_id' => $Qreviews->valueInt('reviews_id')]);
    }

    $OSCOM_Db->delete('reviews', ['products_id' => (int)$product_id]);

    Cache::clear('categories');
    Cache::clear('products-also_purchased');
  }

  function tep_remove_order($order_id, $restock = false) {
    $OSCOM_Db = Registry::get('Db');

    if ($restock == 'on') {
      $Qproducts = $OSCOM_Db->get('orders_products', [
        'products_id',
        'products_quantity'
      ], [
        'orders_id' => (int)$order_id
      ]);

      while ($Qproducts->fetch()) {
        $Qupdate = $OSCOM_Db->prepare('update :table_products set products_quantity = products_quantity + ' . $Qproducts->valueInt('products_quantity') . ', products_ordered = products_ordered - ' . $Qproducts->valueInt('products_quantity') . ' where products_id = :products_id');
        $Qupdate->bindInt(':products_id', $Qproducts->valueInt('products_id'));
        $Qupdate->execute();
      }
    }

    $OSCOM_Db->delete('orders', ['orders_id' => (int)$order_id]);
    $OSCOM_Db->delete('orders_products', ['orders_id' => (int)$order_id]);
    $OSCOM_Db->delete('orders_products_attributes', ['orders_id' => (int)$order_id]);
    $OSCOM_Db->delete('orders_status_history', ['orders_id' => (int)$order_id]);
    $OSCOM_Db->delete('orders_total', ['orders_id' => (int)$order_id]);
  }

  function tep_get_file_permissions($mode) {
// determine type
    if ( ($mode & 0xC000) == 0xC000) { // unix domain socket
      $type = 's';
    } elseif ( ($mode & 0x4000) == 0x4000) { // directory
      $type = 'd';
    } elseif ( ($mode & 0xA000) == 0xA000) { // symbolic link
      $type = 'l';
    } elseif ( ($mode & 0x8000) == 0x8000) { // regular file
      $type = '-';
    } elseif ( ($mode & 0x6000) == 0x6000) { //bBlock special file
      $type = 'b';
    } elseif ( ($mode & 0x2000) == 0x2000) { // character special file
      $type = 'c';
    } elseif ( ($mode & 0x1000) == 0x1000) { // named pipe
      $type = 'p';
    } else { // unknown
      $type = '?';
    }

// determine permissions
    $owner['read']    = ($mode & 00400) ? 'r' : '-';
    $owner['write']   = ($mode & 00200) ? 'w' : '-';
    $owner['execute'] = ($mode & 00100) ? 'x' : '-';
    $group['read']    = ($mode & 00040) ? 'r' : '-';
    $group['write']   = ($mode & 00020) ? 'w' : '-';
    $group['execute'] = ($mode & 00010) ? 'x' : '-';
    $world['read']    = ($mode & 00004) ? 'r' : '-';
    $world['write']   = ($mode & 00002) ? 'w' : '-';
    $world['execute'] = ($mode & 00001) ? 'x' : '-';

// adjust for SUID, SGID and sticky bit
    if ($mode & 0x800 ) $owner['execute'] = ($owner['execute'] == 'x') ? 's' : 'S';
    if ($mode & 0x400 ) $group['execute'] = ($group['execute'] == 'x') ? 's' : 'S';
    if ($mode & 0x200 ) $world['execute'] = ($world['execute'] == 'x') ? 't' : 'T';

    return $type .
           $owner['read'] . $owner['write'] . $owner['execute'] .
           $group['read'] . $group['write'] . $group['execute'] .
           $world['read'] . $world['write'] . $world['execute'];
  }

////
// Output the tax percentage with optional padded decimals
  function tep_display_tax_value($value, $padding = TAX_DECIMAL_PLACES) {
    if (strpos($value, '.')) {
      $loop = true;
      while ($loop) {
        if (substr($value, -1) == '0') {
          $value = substr($value, 0, -1);
        } else {
          $loop = false;
          if (substr($value, -1) == '.') {
            $value = substr($value, 0, -1);
          }
        }
      }
    }

    if ($padding > 0) {
      if ($decimal_pos = strpos($value, '.')) {
        $decimals = strlen(substr($value, ($decimal_pos+1)));
        for ($i=$decimals; $i<$padding; $i++) {
          $value .= '0';
        }
      } else {
        $value .= '.';
        for ($i=0; $i<$padding; $i++) {
          $value .= '0';
        }
      }
    }

    return $value;
  }

  function tep_get_tax_class_title($tax_class_id) {
    if ($tax_class_id == '0') {
      return OSCOM::getDef('text_none');
    } else {
      $Qclass = Registry::get('Db')->get('tax_class', 'tax_class_title', ['tax_class_id' => (int)$tax_class_id]);

      return $Qclass->value('tax_class_title');
    }
  }

  function tep_banner_image_extension() {
    if (function_exists('imagetypes')) {
      if (imagetypes() & IMG_PNG) {
        return 'png';
      } elseif (imagetypes() & IMG_JPG) {
        return 'jpg';
      } elseif (imagetypes() & IMG_GIF) {
        return 'gif';
      }
    } elseif (function_exists('imagecreatefrompng') && function_exists('imagepng')) {
      return 'png';
    } elseif (function_exists('imagecreatefromjpeg') && function_exists('imagejpeg')) {
      return 'jpg';
    } elseif (function_exists('imagecreatefromgif') && function_exists('imagegif')) {
      return 'gif';
    }

    return false;
  }

////
// Wrapper function for round() for php3 compatibility
  function tep_round($value, $precision) {
    return round($value, $precision);
  }

////
// Add tax to a products price
  function tep_add_tax($price, $tax, $override = false) {
    if ( ( (DISPLAY_PRICE_WITH_TAX == 'true') || ($override == true) ) && ($tax > 0) ) {
      return $price + tep_calculate_tax($price, $tax);
    } else {
      return $price;
    }
  }

// Calculates Tax rounding the result
  function tep_calculate_tax($price, $tax) {
    return $price * $tax / 100;
  }

////
// Returns the tax rate for a zone / class
// TABLES: tax_rates, zones_to_geo_zones
  function tep_get_tax_rate($class_id, $country_id = -1, $zone_id = -1) {
    global $customer_zone_id, $customer_country_id;

    if ( ($country_id == -1) && ($zone_id == -1) ) {
      $country_id = STORE_COUNTRY;
      $zone_id = STORE_ZONE;
    }

    $Qtax = Registry::get('Db')->prepare('select sum(tax_rate) as tax_rate from :table_tax_rates tr left join :table_zones_to_geo_zones za on tr.tax_zone_id = za.geo_zone_id left join :table_geo_zones tz on tz.geo_zone_id = tr.tax_zone_id where (za.zone_country_id IS NULL OR za.zone_country_id = "0" OR za.zone_country_id = :zone_country_id) AND (za.zone_id IS NULL OR za.zone_id = "0" OR za.zone_id = :zone_id) AND tr.tax_class_id = :tax_class_id group by tr.tax_priority');
    $Qtax->bindInt(':zone_country_id', (int)$country_id);
    $Qtax->bindInt(':zone_id', (int)$zone_id);
    $Qtax->bindInt(':tax_class_id', (int)$class_id);
    $Qtax->execute();

    if ($Qtax->fetch() !== false) {
      $tax_multiplier = 0;

      do {
        $tax_multiplier += $Qtax->value('tax_rate');
      } while ($Qtax->fetch());

      return $tax_multiplier;
    } else {
      return 0;
    }
  }

////
// Returns the tax rate for a tax class
// TABLES: tax_rates
  function tep_get_tax_rate_value($class_id) {
    return tep_get_tax_rate($class_id, -1, -1);
  }

  function tep_call_function($function, $parameter, $object = '') {
    if ($object == '') {
      return call_user_func($function, $parameter);
    } else {
      return call_user_func(array($object, $function), $parameter);
    }
  }

  function tep_get_zone_class_title($zone_class_id) {
    if ($zone_class_id == '0') {
      return OSCOM::getDef('text_none');
    } else {
      $Qclass = Registry::get('Db')->get('geo_zones', [
        'geo_zone_name'
      ], [
        'geo_zone_id' => (int)$zone_class_id
      ]);

      return $Qclass->value('geo_zone_name');
    }
  }

  function tep_cfg_pull_down_zone_classes($zone_class_id, $key = '') {
    $name = !empty($key) ? 'configuration[' . $key . ']' : 'configuration_value';

    $zone_class_array = [
      [
        'id' => '0',
        'text' => OSCOM::getDef('text_none')
      ]
    ];

    $Qclass = Registry::get('Db')->get('geo_zones', [
      'geo_zone_id',
      'geo_zone_name'
    ], null, 'geo_zone_name');

    while ($Qclass->fetch()) {
      $zone_class_array[] = [
        'id' => $Qclass->valueInt('geo_zone_id'),
        'text' => $Qclass->value('geo_zone_name')
      ];
    }

    return HTML::selectField($name, $zone_class_array, $zone_class_id);
  }

  function tep_cfg_pull_down_order_statuses($order_status_id, $key = '') {
    $OSCOM_Db = Registry::get('Db');
    $OSCOM_Language = Registry::get('Language');

    $name = !empty($key) ? 'configuration[' . $key . ']' : 'configuration_value';

    $statuses_array = [
      [
        'id' => '0',
        'text' => OSCOM::getDef('text_default')
      ]
    ];

    $Qstatus = $OSCOM_Db->get('orders_status', [
      'orders_status_id',
      'orders_status_name'
    ], [
      'language_id' => $OSCOM_Language->getId()
    ], 'orders_status_name');

    while ($Qstatus->fetch()) {
      $statuses_array[] = [
        'id' => $Qstatus->valueInt('orders_status_id'),
        'text' => $Qstatus->value('orders_status_name')
      ];
    }

    return HTML::selectField($name, $statuses_array, $order_status_id);
  }

  function tep_get_order_status_name($order_status_id, $language_id = '') {
    $OSCOM_Db = Registry::get('Db');
    $OSCOM_Language = Registry::get('Language');

    if ($order_status_id < 1) return OSCOM::getDef('text_default');

    if (empty($language_id) || !is_numeric($language_id)) $language_id = $OSCOM_Language->getId();

    $Qstatus = $OSCOM_Db->get('orders_status', 'orders_status_name', ['orders_status_id' => (int)$order_status_id, 'language_id' => (int)$language_id]);

    return $Qstatus->value('orders_status_name');
  }

////
// Parse and secure the cPath parameter values
  function tep_parse_category_path($cPath) {
// make sure the category IDs are integers
    $cPath_array = array_map(function ($string) {
      return (int)$string;
    }, explode('_', $cPath));

// make sure no duplicate category IDs exist which could lock the server in a loop
    $tmp_array = array();
    $n = sizeof($cPath_array);
    for ($i=0; $i<$n; $i++) {
      if (!in_array($cPath_array[$i], $tmp_array)) {
        $tmp_array[] = $cPath_array[$i];
      }
    }

    return $tmp_array;
  }

////
// javascript to dynamically update the states/provinces list when the country is changed
// TABLES: zones
  function tep_js_zone_list($country, $form, $field) {
    $OSCOM_Db = Registry::get('Db');

    $num_country = 1;
    $output_string = '';

    $Qcountries = $OSCOM_Db->get('zones', 'distinct zone_country_id', null, 'zone_country_id');

    while ($Qcountries->fetch()) {
      if ($num_country == 1) {
        $output_string .= '  if (' . $country . ' == "' . $Qcountries->valueInt('zone_country_id') . '") {' . "\n";
      } else {
        $output_string .= '  } else if (' . $country . ' == "' . $Qcountries->valueInt('zone_country_id') . '") {' . "\n";
      }

      $num_state = 1;

      $Qstates = $OSCOM_Db->get('zones', [
        'zone_name',
        'zone_id'
      ], [
        'zone_country_id' => $Qcountries->valueInt('zone_country_id')
      ], 'zone_name');

      while ($Qstates->fetch()) {
        if ($num_state == '1') $output_string .= '    ' . $form . '.' . $field . '.options[0] = new Option("' . PLEASE_SELECT . '", "");' . "\n";
        $output_string .= '    ' . $form . '.' . $field . '.options[' . $num_state . '] = new Option("' . $Qstates->value('zone_name') . '", "' . $Qstates->valueInt('zone_id') . '");' . "\n";
        $num_state++;
      }
      $num_country++;
    }
    $output_string .= '  } else {' . "\n" .
                      '    ' . $form . '.' . $field . '.options[0] = new Option("' . TYPE_BELOW . '", "");' . "\n" .
                      '  }' . "\n";

    return $output_string;
  }
?>
