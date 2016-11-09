<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

////
// Return a product's name
// TABLES: products
  function tep_get_products_name($product_id, $language_id = null) {
    $OSCOM_Db = Registry::get('Db');
    $OSCOM_Language = Registry::get('Language');

    if (empty($language_id) || !is_numeric($language_id)) $language_id = $OSCOM_Language->getId();

    $Qproduct = $OSCOM_Db->prepare('select products_name from :table_products_description where products_id = :products_id and language_id = :language_id');
    $Qproduct->bindInt(':products_id', $product_id);
    $Qproduct->bindInt(':language_id', $language_id);
    $Qproduct->execute();

    return $Qproduct->value('products_name');
  }

////
// Return a product's special price (returns nothing if there is no offer)
// TABLES: products
  function tep_get_products_special_price($product_id) {
    $OSCOM_Db = Registry::get('Db');

    $result = false;

    $Qproduct = $OSCOM_Db->prepare('select specials_new_products_price from :table_specials where products_id = :products_id and status = 1');
    $Qproduct->bindInt(':products_id', $product_id);
    $Qproduct->execute();

    if ($Qproduct->fetch() !== false) {
      $result = $Qproduct->valueDecimal('specials_new_products_price');
    }

    return $result;
  }

////
// Return a product's stock
// TABLES: products
  function tep_get_products_stock($products_id) {
    $OSCOM_Db = Registry::get('Db');

    $Qproduct = $OSCOM_Db->prepare('select products_quantity from :table_products where products_id = :products_id');
    $Qproduct->bindInt(':products_id', tep_get_prid($products_id));
    $Qproduct->execute();

    return $Qproduct->valueInt('products_quantity');
  }

////
// Check if the required stock is available
// If insufficent stock is available return an out of stock message
  function tep_check_stock($products_id, $products_quantity) {
    $stock_left = tep_get_products_stock($products_id) - $products_quantity;
    $out_of_stock = '';

    if ($stock_left < 0) {
      $out_of_stock = '<span class="text-danger"><b>' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . '</b></span>';
    }

    return $out_of_stock;
  }

////
// Break a word in a string if it is longer than a specified length ($len)
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

////
// Return all $_GET variables, except those passed as a parameter
  function tep_get_all_get_params($exclude_array = '') {
    if (!is_array($exclude_array)) $exclude_array = array();

    $exclude_array[] = session_name();
    $exclude_array[] = 'error';
    $exclude_array[] = 'x';
    $exclude_array[] = 'y';

    $get_url = '';

    if (is_array($_GET) && (!empty($_GET))) {
      foreach ($_GET as $key => $value) {
        if ( !in_array($key, $exclude_array) ) {
          $get_url .= $key . '=' . rawurlencode($value) . '&';
        }
     }
  }
    return $get_url;
}

////
// Returns an array with countries
// TABLES: countries
  function tep_get_countries($countries_id = '', $with_iso_codes = false) {
    $OSCOM_Db = Registry::get('Db');

    $countries_array = array();

    if (tep_not_null($countries_id)) {
      if ($with_iso_codes == true) {
        $Qcountries = $OSCOM_Db->prepare('select countries_name, countries_iso_code_2, countries_iso_code_3 from :table_countries where countries_id = :countries_id');
        $Qcountries->bindInt(':countries_id', $countries_id);
        $Qcountries->execute();

        $countries_array = $Qcountries->toArray();
      } else {
        $Qcountries = $OSCOM_Db->prepare('select countries_name from :table_countries where countries_id = :countries_id');
        $Qcountries->bindInt(':countries_id', $countries_id);
        $Qcountries->execute();

        $countries_array = $Qcountries->toArray();
      }
    } else {
      $countries_array = $OSCOM_Db->query('select countries_id, countries_name from :table_countries order by countries_name')->fetchAll();
    }

    return $countries_array;
  }

////
// Alias function to tep_get_countries, which also returns the countries iso codes
  function tep_get_countries_with_iso_codes($countries_id) {
    return tep_get_countries($countries_id, true);
  }

////
// Generate a path to categories
  function tep_get_path($current_category_id = '') {
    global $cPath_array;

    $OSCOM_Db = Registry::get('Db');

    if (tep_not_null($current_category_id)) {
      $cp_size = sizeof($cPath_array);
      if ($cp_size == 0) {
        $cPath_new = $current_category_id;
      } else {
        $cPath_new = '';

        $QlastCategory = $OSCOM_Db->prepare('select parent_id from :table_categories where categories_id = :categories_id');
        $QlastCategory->bindInt(':categories_id', $cPath_array[($cp_size-1)]);
        $QlastCategory->execute();

        $QcurrentCategory = $OSCOM_Db->prepare('select parent_id from :table_categories where categories_id = :categories_id');
        $QcurrentCategory->bindInt(':categories_id', $current_category_id);
        $QcurrentCategory->execute();

        if ($QlastCategory->valueInt('parent_id') == $QcurrentCategory->valueInt('parent_id')) {
          for ($i=0; $i<($cp_size-1); $i++) {
            $cPath_new .= '_' . $cPath_array[$i];
          }
        } else {
          for ($i=0; $i<$cp_size; $i++) {
            $cPath_new .= '_' . $cPath_array[$i];
          }
        }
        $cPath_new .= '_' . $current_category_id;

        if (substr($cPath_new, 0, 1) == '_') {
          $cPath_new = substr($cPath_new, 1);
        }
      }
    } else {
      $cPath_new = implode('_', $cPath_array);
    }

    return 'cPath=' . $cPath_new;
  }

////
// Alias function to tep_get_countries()
  function tep_get_country_name($country_id) {
    $country_array = tep_get_countries($country_id);

    return $country_array['countries_name'];
  }

////
// Returns the zone (State/Province) name
// TABLES: zones
  function tep_get_zone_name($country_id, $zone_id, $default_zone) {
    $OSCOM_Db = Registry::get('Db');

    $Qzone = $OSCOM_Db->prepare('select zone_name from :table_zones where zone_country_id = :zone_country_id and zone_id = :zone_id');
    $Qzone->bindInt(':zone_country_id', $country_id);
    $Qzone->bindInt(':zone_id', $zone_id);
    $Qzone->execute();

    if ($Qzone->fetch() !== false) {
      return $Qzone->value('zone_name');
    } else {
      return $default_zone;
    }
  }

////
// Returns the zone (State/Province) code
// TABLES: zones
  function tep_get_zone_code($country_id, $zone_id, $default_zone) {
    $OSCOM_Db = Registry::get('Db');

    $Qzone = $OSCOM_Db->prepare('select zone_code from :table_zones where zone_country_id = :zone_country_id and zone_id = :zone_id');
    $Qzone->bindInt(':zone_country_id', $country_id);
    $Qzone->bindInt(':zone_id', $zone_id);
    $Qzone->execute();

    if ($Qzone->fetch() !== false) {
      return $Qzone->value('zone_code');
    } else {
      return $default_zone;
    }
  }

////
// Wrapper function for round()
  function tep_round($number, $precision) {
    if (strpos($number, '.') && (strlen(substr($number, strpos($number, '.')+1)) > $precision)) {
      $number = substr($number, 0, strpos($number, '.') + 1 + $precision + 1);

      if (substr($number, -1) >= 5) {
        if ($precision > 1) {
          $number = substr($number, 0, -1) + ('0.' . str_repeat(0, $precision-1) . '1');
        } elseif ($precision == 1) {
          $number = substr($number, 0, -1) + 0.1;
        } else {
          $number = substr($number, 0, -1) + 1;
        }
      } else {
        $number = substr($number, 0, -1);
      }
    }

    return $number;
  }

////
// Returns the tax rate for a zone / class
// TABLES: tax_rates, zones_to_geo_zones
  function tep_get_tax_rate($class_id, $country_id = -1, $zone_id = -1) {
    static $tax_rates = array();

    $OSCOM_Db = Registry::get('Db');

    if ( ($country_id == -1) && ($zone_id == -1) ) {
      if (!isset($_SESSION['customer_id'])) {
        $country_id = STORE_COUNTRY;
        $zone_id = STORE_ZONE;
      } else {
        $country_id = $_SESSION['customer_country_id'];
        $zone_id = $_SESSION['customer_zone_id'];
      }
    }

    if (!isset($tax_rates[$class_id][$country_id][$zone_id]['rate'])) {
      $Qtax = $OSCOM_Db->prepare('select sum(tr.tax_rate) as tax_rate from :table_tax_rates tr left join :table_zones_to_geo_zones za on (tr.tax_zone_id = za.geo_zone_id) left join :table_geo_zones tz on (tz.geo_zone_id = tr.tax_zone_id) where (za.zone_country_id is null or za.zone_country_id = 0 or za.zone_country_id = :zone_country_id) and (za.zone_id is null or za.zone_id = 0 or za.zone_id = :zone_id) and tr.tax_class_id = :tax_class_id group by tr.tax_priority');
      $Qtax->bindInt(':zone_country_id', $country_id);
      $Qtax->bindInt(':zone_id', $zone_id);
      $Qtax->bindInt(':tax_class_id', $class_id);
      $Qtax->execute();

      if ($Qtax->fetch() !== false) {
        $tax_multiplier = 1.0;

        do {
          $tax_multiplier *= 1.0 + ($Qtax->valueDecimal('tax_rate') / 100);
        } while ($Qtax->fetch());

        $tax_rates[$class_id][$country_id][$zone_id]['rate'] = ($tax_multiplier - 1.0) * 100;
      } else {
        $tax_rates[$class_id][$country_id][$zone_id]['rate'] = 0;
      }
    }

    return $tax_rates[$class_id][$country_id][$zone_id]['rate'];
  }

////
// Return the tax description for a zone / class
// TABLES: tax_rates;
  function tep_get_tax_description($class_id, $country_id, $zone_id) {
    static $tax_rates = array();

    $OSCOM_Db = Registry::get('Db');

    if (!isset($tax_rates[$class_id][$country_id][$zone_id]['description'])) {
      $Qtax = $OSCOM_Db->prepare('select tr.tax_description from :table_tax_rates tr left join :table_zones_to_geo_zones za on (tr.tax_zone_id = za.geo_zone_id) left join :table_geo_zones tz on (tz.geo_zone_id = tr.tax_zone_id) where (za.zone_country_id is null or za.zone_country_id = 0 or za.zone_country_id = :zone_country_id) and (za.zone_id is null or za.zone_id = 0 or za.zone_id = :zone_id) and tr.tax_class_id = :tax_class_id order by tr.tax_priority');
      $Qtax->bindInt(':zone_country_id', $country_id);
      $Qtax->bindInt(':zone_id', $zone_id);
      $Qtax->bindInt(':tax_class_id', $class_id);
      $Qtax->execute();

      if ($Qtax->fetch() !== false) {
        $tax_description = '';

        do {
          $tax_description .= $Qtax->value('tax_description') . ' + ';
        } while ($Qtax->fetch());

        $tax_description = substr($tax_description, 0, -3);

        $tax_rates[$class_id][$country_id][$zone_id]['description'] = $tax_description;
      } else {
        $tax_rates[$class_id][$country_id][$zone_id]['description'] = OSCOM::getDef('text_unknown_tax_rate');
      }
    }

    return $tax_rates[$class_id][$country_id][$zone_id]['description'];
  }

////
// Add tax to a products price
  function tep_add_tax($price, $tax) {
    if ( (DISPLAY_PRICE_WITH_TAX == 'true') && ($tax > 0) ) {
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
// Return the number of products in a category
// TABLES: products, products_to_categories, categories
  function tep_count_products_in_category($category_id, $include_inactive = false) {
    $OSCOM_Db = Registry::get('Db');

    $products_count = 0;

    $products_query = 'select count(*) as total from :table_products p, :table_products_to_categories p2c where p.products_id = p2c.products_id and p2c.categories_id = :categories_id';

    if ($include_inactive == false) {
      $products_query .= ' and p.products_status = 1';
    }

    $Qproducts = $OSCOM_Db->prepare($products_query);
    $Qproducts->bindInt(':categories_id', $category_id);
    $Qproducts->execute();

    if ($Qproducts->fetch() !== false) {
      $products_count += $Qproducts->valueInt('total');
    }

    $Qcategories = $OSCOM_Db->prepare('select categories_id from :table_categories where parent_id = :parent_id');
    $Qcategories->bindInt(':parent_id', $category_id);
    $Qcategories->execute();

    if ($Qcategories->fetch() !== false) {
      do {
        $products_count += tep_count_products_in_category($Qcategories->valueInt('categories_id'), $include_inactive);
      } while ($Qcategories->fetch());
    }

    return $products_count;
  }

////
// Return true if the category has subcategories
// TABLES: categories
  function tep_has_category_subcategories($category_id) {
    $OSCOM_Db = Registry::get('Db');

    $Qcheck = $OSCOM_Db->prepare('select categories_id from :table_categories where parent_id = :parent_id limit 1');
    $Qcheck->bindInt(':parent_id', $category_id);
    $Qcheck->execute();

    return ($Qcheck->fetch() !== false);
  }

////
// Returns the address_format_id for the given country
// TABLES: countries;
  function tep_get_address_format_id($country_id) {
    $OSCOM_Db = Registry::get('Db');

    $format_id = 1;

    $Qformat = $OSCOM_Db->prepare('select address_format_id from :table_countries where countries_id = :countries_id');
    $Qformat->bindInt(':countries_id', $country_id);
    $Qformat->execute();

    if ($Qformat->fetch() !== false) {
      $format_id = $Qformat->valueInt('address_format_id');
    }

    return $format_id;
  }

////
// Return a formatted address
// TABLES: address_format
  function tep_address_format($address_format_id, $address, $html, $boln, $eoln) {
    $OSCOM_Db = Registry::get('Db');

    $Qformat = $OSCOM_Db->prepare('select address_format from :table_address_format where address_format_id = :address_format_id');
    $Qformat->bindInt(':address_format_id', $address_format_id);
    $Qformat->execute();

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
      $replace['$country'] = HTML::outputProtected($address['country']['title']);
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

    $address = strtr($Qformat->value('address_format'), $replace);

    if ( (ACCOUNT_COMPANY == 'true') && tep_not_null($replace['$company']) ) {
      $address = $replace['$company'] . $replace['$cr'] . $address;
    }

    return $address;
  }

////
// Return a formatted address
// TABLES: customers, address_book
  function tep_address_label($customers_id, $address_id = 1, $html = false, $boln = '', $eoln = "\n") {
    $OSCOM_Db = Registry::get('Db');

    if (is_array($address_id) && !empty($address_id)) {
      return tep_address_format($address_id['address_format_id'], $address_id, $html, $boln, $eoln);
    }

    $Qaddress = $OSCOM_Db->prepare('select entry_firstname as firstname, entry_lastname as lastname, entry_company as company, entry_street_address as street_address, entry_suburb as suburb, entry_city as city, entry_postcode as postcode, entry_state as state, entry_zone_id as zone_id, entry_country_id as country_id from :table_address_book where address_book_id = :address_book_id and customers_id = :customers_id');
    $Qaddress->bindInt(':address_book_id', $address_id);
    $Qaddress->bindInt(':customers_id', $customers_id);
    $Qaddress->execute();

    $format_id = tep_get_address_format_id($Qaddress->valueInt('country_id'));

    return tep_address_format($format_id, $Qaddress->toArray(), $html, $boln, $eoln);
  }

  function tep_row_number_format($number) {
    if ( ($number < 10) && (substr($number, 0, 1) != '0') ) $number = '0' . $number;

    return $number;
  }

  function tep_get_categories($categories_array = '', $parent_id = '0', $indent = '') {
    $OSCOM_Db = Registry::get('Db');
    $OSCOM_Language = Registry::get('Language');

    if (!is_array($categories_array)) $categories_array = array();

    $Qcategories = $OSCOM_Db->prepare('select c.categories_id, cd.categories_name from :table_categories c, :table_categories_description cd where c.parent_id = :parent_id and c.categories_id = cd.categories_id and cd.language_id = :language_id order by c.sort_order, cd.categories_name');
    $Qcategories->bindInt(':parent_id', $parent_id);
    $Qcategories->bindInt(':language_id', $OSCOM_Language->getId());
    $Qcategories->execute();

    while ($Qcategories->fetch()) {
      $categories_array[] = array('id' => $Qcategories->valueInt('categories_id'),
                                  'text' => $indent . $Qcategories->value('categories_name'));

      if ($Qcategories->valueInt('categories_id') != $parent_id) {
        $categories_array = tep_get_categories($categories_array, $Qcategories->valueInt('categories_id'), $indent . '&nbsp;&nbsp;');
      }
    }

    return $categories_array;
  }

  function tep_get_manufacturers($manufacturers_array = '') {
    $OSCOM_Db = Registry::get('Db');

    if (!is_array($manufacturers_array)) $manufacturers_array = array();

    $Qmanufacturers = $OSCOM_Db->query('select manufacturers_id, manufacturers_name from :table_manufacturers order by manufacturers_name');

    while ($Qmanufacturers->fetch()) {
      $manufacturers_array[] = array('id' => $Qmanufacturers->valueInt('manufacturers_id'), 'text' => $Qmanufacturers->value('manufacturers_name'));
    }

    return $manufacturers_array;
  }

////
// Return all subcategory IDs
// TABLES: categories
  function tep_get_subcategories(&$subcategories_array, $parent_id = 0) {
    $OSCOM_Db = Registry::get('Db');

    $Qsub = $OSCOM_Db->prepare('select categories_id from :table_categories where parent_id = :parent_id');
    $Qsub->bindInt(':parent_id', $parent_id);
    $Qsub->execute();

    while ($Qsub->fetch()) {
      $subcategories_array[sizeof($subcategories_array)] = $Qsub->valueInt('categories_id');

      if ($Qsub->valueInt('categories_id') != $parent_id) {
        tep_get_subcategories($subcategories_array, $Qsub->valueInt('categories_id'));
      }
    }
  }

////
// Parse search string into indivual objects
  function tep_parse_search_string($search_str = '', &$objects) {
    $search_str = trim(strtolower($search_str));

// Break up $search_str on whitespace; quoted string will be reconstructed later
    $pieces = preg_split('/[[:space:]]+/', $search_str);
    $objects = array();
    $tmpstring = '';
    $flag = '';

    for ($k=0; $k<count($pieces); $k++) {
      while (substr($pieces[$k], 0, 1) == '(') {
        $objects[] = '(';
        if (strlen($pieces[$k]) > 1) {
          $pieces[$k] = substr($pieces[$k], 1);
        } else {
          $pieces[$k] = '';
        }
      }

      $post_objects = array();

      while (substr($pieces[$k], -1) == ')')  {
        $post_objects[] = ')';
        if (strlen($pieces[$k]) > 1) {
          $pieces[$k] = substr($pieces[$k], 0, -1);
        } else {
          $pieces[$k] = '';
        }
      }

// Check individual words

      if ( (substr($pieces[$k], -1) != '"') && (substr($pieces[$k], 0, 1) != '"') ) {
        $objects[] = trim($pieces[$k]);

        for ($j=0; $j<count($post_objects); $j++) {
          $objects[] = $post_objects[$j];
        }
      } else {
/* This means that the $piece is either the beginning or the end of a string.
   So, we'll slurp up the $pieces and stick them together until we get to the
   end of the string or run out of pieces.
*/

// Add this word to the $tmpstring, starting the $tmpstring
        $tmpstring = trim(preg_replace('/"/', ' ', $pieces[$k]));

// Check for one possible exception to the rule. That there is a single quoted word.
        if (substr($pieces[$k], -1 ) == '"') {
// Turn the flag off for future iterations
          $flag = 'off';

          $objects[] = trim(preg_replace('/"/', ' ', $pieces[$k]));

          for ($j=0; $j<count($post_objects); $j++) {
            $objects[] = $post_objects[$j];
          }

          unset($tmpstring);

// Stop looking for the end of the string and move onto the next word.
          continue;
        }

// Otherwise, turn on the flag to indicate no quotes have been found attached to this word in the string.
        $flag = 'on';

// Move on to the next word
        $k++;

// Keep reading until the end of the string as long as the $flag is on

        while ( ($flag == 'on') && ($k < count($pieces)) ) {
          while (substr($pieces[$k], -1) == ')') {
            $post_objects[] = ')';
            if (strlen($pieces[$k]) > 1) {
              $pieces[$k] = substr($pieces[$k], 0, -1);
            } else {
              $pieces[$k] = '';
            }
          }

// If the word doesn't end in double quotes, append it to the $tmpstring.
          if (substr($pieces[$k], -1) != '"') {
// Tack this word onto the current string entity
            $tmpstring .= ' ' . $pieces[$k];

// Move on to the next word
            $k++;
            continue;
          } else {
/* If the $piece ends in double quotes, strip the double quotes, tack the
   $piece onto the tail of the string, push the $tmpstring onto the $haves,
   kill the $tmpstring, turn the $flag "off", and return.
*/
            $tmpstring .= ' ' . trim(preg_replace('/"/', ' ', $pieces[$k]));

// Push the $tmpstring onto the array of stuff to search for
            $objects[] = trim($tmpstring);

            for ($j=0; $j<count($post_objects); $j++) {
              $objects[] = $post_objects[$j];
            }

            unset($tmpstring);

// Turn off the flag to exit the loop
            $flag = 'off';
          }
        }
      }
    }

// add default logical operators if needed
    $temp = array();
    for($i=0; $i<(count($objects)-1); $i++) {
      $temp[] = $objects[$i];
      if ( ($objects[$i] != 'and') &&
           ($objects[$i] != 'or') &&
           ($objects[$i] != '(') &&
           ($objects[$i+1] != 'and') &&
           ($objects[$i+1] != 'or') &&
           ($objects[$i+1] != ')') ) {
        $temp[] = ADVANCED_SEARCH_DEFAULT_OPERATOR;
      }
    }
    $temp[] = $objects[$i];
    $objects = $temp;

    $keyword_count = 0;
    $operator_count = 0;
    $balance = 0;
    for($i=0; $i<count($objects); $i++) {
      if ($objects[$i] == '(') $balance --;
      if ($objects[$i] == ')') $balance ++;
      if ( ($objects[$i] == 'and') || ($objects[$i] == 'or') ) {
        $operator_count ++;
      } elseif ( ($objects[$i]) && ($objects[$i] != '(') && ($objects[$i] != ')') ) {
        $keyword_count ++;
      }
    }

    if ( ($operator_count < $keyword_count) && ($balance == 0) ) {
      return true;
    } else {
      return false;
    }
  }

////
// Check date
  function tep_checkdate($date_to_check, $format_string, &$date_array) {
    $separator_idx = -1;

    $separators = array('-', ' ', '/', '.');
    $month_abbr = array('jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec');
    $no_of_days = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

    $format_string = strtolower($format_string);

    if (strlen($date_to_check) != strlen($format_string)) {
      return false;
    }

    $size = sizeof($separators);
    for ($i=0; $i<$size; $i++) {
      $pos_separator = strpos($date_to_check, $separators[$i]);
      if ($pos_separator != false) {
        $date_separator_idx = $i;
        break;
      }
    }

    for ($i=0; $i<$size; $i++) {
      $pos_separator = strpos($format_string, $separators[$i]);
      if ($pos_separator != false) {
        $format_separator_idx = $i;
        break;
      }
    }

    if ($date_separator_idx != $format_separator_idx) {
      return false;
    }

    if ($date_separator_idx != -1) {
      $format_string_array = explode( $separators[$date_separator_idx], $format_string );
      if (sizeof($format_string_array) != 3) {
        return false;
      }

      $date_to_check_array = explode( $separators[$date_separator_idx], $date_to_check );
      if (sizeof($date_to_check_array) != 3) {
        return false;
      }

      $size = sizeof($format_string_array);
      for ($i=0; $i<$size; $i++) {
        if ($format_string_array[$i] == 'mm' || $format_string_array[$i] == 'mmm') $month = $date_to_check_array[$i];
        if ($format_string_array[$i] == 'dd') $day = $date_to_check_array[$i];
        if ( ($format_string_array[$i] == 'yyyy') || ($format_string_array[$i] == 'aaaa') ) $year = $date_to_check_array[$i];
      }
    } else {
      if (strlen($format_string) == 8 || strlen($format_string) == 9) {
        $pos_month = strpos($format_string, 'mmm');
        if ($pos_month != false) {
          $month = substr( $date_to_check, $pos_month, 3 );
          $size = sizeof($month_abbr);
          for ($i=0; $i<$size; $i++) {
            if ($month == $month_abbr[$i]) {
              $month = $i;
              break;
            }
          }
        } else {
          $month = substr($date_to_check, strpos($format_string, 'mm'), 2);
        }
      } else {
        return false;
      }

      $day = substr($date_to_check, strpos($format_string, 'dd'), 2);
      $year = substr($date_to_check, strpos($format_string, 'yyyy'), 4);
    }

    if (strlen($year) != 4) {
      return false;
    }

    if (!settype($year, 'integer') || !settype($month, 'integer') || !settype($day, 'integer')) {
      return false;
    }

    if ($month > 12 || $month < 1) {
      return false;
    }

    if ($day < 1) {
      return false;
    }

    if (tep_is_leap_year($year)) {
      $no_of_days[1] = 29;
    }

    if ($day > $no_of_days[$month - 1]) {
      return false;
    }

    $date_array = array($year, $month, $day);

    return true;
  }

////
// Check if year is a leap year
  function tep_is_leap_year($year) {
    if ($year % 100 == 0) {
      if ($year % 400 == 0) return true;
    } else {
      if (($year % 4) == 0) return true;
    }

    return false;
  }

////
// Return table heading with sorting capabilities
  function tep_create_sort_heading($sortby, $colnum, $heading) {
    global $PHP_SELF;

    $sort_prefix = '';
    $sort_suffix = '';

    if ($sortby) {
      $sort_prefix = '<a href="' . OSCOM::link($PHP_SELF, tep_get_all_get_params(array('page', 'info', 'sort')) . 'page=1&sort=' . $colnum . ($sortby == $colnum . 'a' ? 'd' : 'a')) . '" title="' . HTML::output(OSCOM::getDef('text_sort_products') . ($sortby == $colnum . 'd' || substr($sortby, 0, 1) != $colnum ? OSCOM::getDef('text_ascendingly') : OSCOM::getDef('text_descendingly')) . OSCOM::getDef('text_by') . $heading) . '" class="productListing-heading">' ;
      $sort_suffix = (substr($sortby, 0, 1) == $colnum ? (substr($sortby, 1, 1) == 'a' ? '+' : '-') : '') . '</a>';
    }

    return $sort_prefix . $heading . $sort_suffix;
  }

////
// Recursively go through the categories and retreive all parent categories IDs
// TABLES: categories
  function tep_get_parent_categories(&$categories, $categories_id) {
    $OSCOM_Db = Registry::get('Db');

    $Qparent = $OSCOM_Db->prepare('select parent_id from :table_categories where categories_id = :categories_id');
    $Qparent->bindInt(':categories_id', $categories_id);
    $Qparent->execute();

    while ($Qparent->fetch()) {
      if ($Qparent->valueInt('parent_id') == 0) return true;

      $categories[sizeof($categories)] = $Qparent->valueInt('parent_id');

      if ($Qparent->valueInt('parent_id') != $categories_id) {
        tep_get_parent_categories($categories, $Qparent->valueInt('parent_id'));
      }
    }
  }

////
// Construct a category path to the product
// TABLES: products_to_categories
  function tep_get_product_path($products_id) {
    $OSCOM_Db = Registry::get('Db');

    $cPath = '';

    $Qcategory = $OSCOM_Db->prepare('select p2c.categories_id from :table_products p, :table_products_to_categories p2c where p.products_id = :products_id and p.products_status = 1 and p.products_id = p2c.products_id limit 1');
    $Qcategory->bindInt(':products_id', $products_id);
    $Qcategory->execute();

    if ($Qcategory->fetch() !== false) {
      $categories = array();
      tep_get_parent_categories($categories, $Qcategory->valueInt('categories_id'));

      $categories = array_reverse($categories);

      $cPath = implode('_', $categories);

      if (tep_not_null($cPath)) $cPath .= '_';
      $cPath .= $Qcategory->valueInt('categories_id');
    }

    return $cPath;
  }

////
// Return a product ID with attributes
  function tep_get_uprid($prid, $params) {
    if (is_numeric($prid)) {
      $uprid = (int)$prid;

      if (is_array($params) && (!empty($params))) {
        $attributes_check = true;
        $attributes_ids = '';

        foreach ($params as $option => $value) {
          if (is_numeric($option) && is_numeric($value)) {
            $attributes_ids .= '{' . (int)$option . '}' . (int)$value;
          } else {
            $attributes_check = false;
            break;
          }
        }

        if ($attributes_check == true) {
          $uprid .= $attributes_ids;
        }
      }
    } else {
      $uprid = tep_get_prid($prid);

      if (is_numeric($uprid)) {
        if (strpos($prid, '{') !== false) {
          $attributes_check = true;
          $attributes_ids = '';

// strpos()+1 to remove up to and including the first { which would create an empty array element in explode()
          $attributes = explode('{', substr($prid, strpos($prid, '{')+1));

          for ($i=0, $n=sizeof($attributes); $i<$n; $i++) {
            $pair = explode('}', $attributes[$i]);

            if (is_numeric($pair[0]) && is_numeric($pair[1])) {
              $attributes_ids .= '{' . (int)$pair[0] . '}' . (int)$pair[1];
            } else {
              $attributes_check = false;
              break;
            }
          }

          if ($attributes_check == true) {
            $uprid .= $attributes_ids;
          }
        }
      } else {
        return false;
      }
    }

    return $uprid;
  }

////
// Return a product ID from a product ID with attributes
  function tep_get_prid($uprid) {
    $pieces = explode('{', $uprid);

    if (is_numeric($pieces[0])) {
      return (int)$pieces[0];
    } else {
      return false;
    }
  }

////
// Check if product has attributes
  function tep_has_product_attributes($products_id) {
    $OSCOM_Db = Registry::get('Db');

    $Qattributes = $OSCOM_Db->prepare('select products_id from :table_products_attributes where products_id = :products_id limit 1');
    $Qattributes->bindInt(':products_id', $products_id);
    $Qattributes->execute();

    return $Qattributes->fetch() !== false;
  }

  function tep_count_modules($modules = '') {
    $count = 0;

    if (empty($modules)) return $count;

    $modules_array = explode(';', $modules);

    for ($i=0, $n=sizeof($modules_array); $i<$n; $i++) {
      $class = substr($modules_array[$i], 0, strrpos($modules_array[$i], '.'));

      if (isset($GLOBALS[$class]) && is_object($GLOBALS[$class])) {
        if ($GLOBALS[$class]->enabled) {
          $count++;
        }
      }
    }

    return $count;
  }

  function tep_count_payment_modules() {
    return tep_count_modules(MODULE_PAYMENT_INSTALLED);
  }

  function tep_count_shipping_modules() {
    return tep_count_modules(MODULE_SHIPPING_INSTALLED);
  }

  function tep_array_to_string($array, $exclude = '', $equals = '=', $separator = '&') {
    if (!is_array($exclude)) $exclude = array();

    $get_string = '';
    if (!empty($array)) {
      foreach ($array as $key => $value) {
        if ( (!in_array($key, $exclude)) && ($key != 'x') && ($key != 'y') ) {
          $get_string .= $key . $equals . $value . $separator;
        }
      }
      $remove_chars = strlen($separator);
      $get_string = substr($get_string, 0, -$remove_chars);
    }

    return $get_string;
  }

  function tep_not_null($value) {
    if (is_array($value)) {
      if (!empty($value)) {
        return true;
      } else {
        return false;
      }
    } elseif(is_object($value)) {
      if (count(get_object_vars($value)) === 0) {
        return false;
      } else {
        return true;
      }
    } else {
      if (($value != '') && (strtolower($value) != 'null') && (strlen(trim($value)) > 0)) {
        return true;
      } else {
        return false;
      }
    }
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

////
// Checks to see if the currency code exists as a currency
// TABLES: currencies
  function tep_currency_exists($code) {
    $OSCOM_Db = Registry::get('Db');

    $Qcurrency = $OSCOM_Db->prepare('select code from :table_currencies where code = :code limit 1');
    $Qcurrency->bindValue(':code', $code);
    $Qcurrency->execute();

    if ($Qcurrency->fetch() !== false) {
      return $Qcurrency->value('code');
    }

    return false;
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

  function tep_count_customer_orders($id = '', $check_session = true) {
    $OSCOM_Db = Registry::get('Db');
    $OSCOM_Language = Registry::get('Language');

    if (is_numeric($id) == false) {
      if (isset($_SESSION['customer_id'])) {
        $id = $_SESSION['customer_id'];
      } else {
        return 0;
      }
    }

    if ($check_session == true) {
      if (!isset($_SESSION['customer_id']) || ($id != $_SESSION['customer_id'])) {
        return 0;
      }
    }

    $Qorders = $OSCOM_Db->prepare('select count(*) as total from :table_orders o, :table_orders_status s where o.customers_id = :customers_id and o.orders_status = s.orders_status_id and s.language_id = :language_id and s.public_flag = 1');
    $Qorders->bindInt(':customers_id', $id);
    $Qorders->bindInt(':language_id', $OSCOM_Language->getId());
    $Qorders->execute();

    if ($Qorders->fetch() !== false) {
      return $Qorders->valueInt('total');
    }

    return 0;
  }

  function tep_count_customer_address_book_entries($id = '', $check_session = true) {
    $OSCOM_Db = Registry::get('Db');

    if (is_numeric($id) == false) {
      if (isset($_SESSION['customer_id'])) {
        $id = $_SESSION['customer_id'];
      } else {
        return 0;
      }
    }

    if ($check_session == true) {
      if (!isset($_SESSION['customer_id']) || ($id != $_SESSION['customer_id'])) {
        return 0;
      }
    }

    $Qaddresses = $OSCOM_Db->prepare('select count(*) as total from :table_address_book where customers_id = :customers_id');
    $Qaddresses->bindInt(':customers_id', $id);
    $Qaddresses->execute();

    if ($Qaddresses->fetch() !== false) {
      return $Qaddresses->valueInt('total');
    }

    return 0;
  }

////
// Creates a pull-down list of countries
  function tep_get_country_list($name, $selected = '', $parameters = '') {
    $countries_array = array(array('id' => '', 'text' => OSCOM::getDef('pull_down_default')));
    $countries = tep_get_countries();

    for ($i=0, $n=sizeof($countries); $i<$n; $i++) {
      $countries_array[] = array('id' => $countries[$i]['countries_id'], 'text' => $countries[$i]['countries_name']);
    }

    return HTML::selectField($name, $countries_array, $selected, $parameters);
  }
?>
