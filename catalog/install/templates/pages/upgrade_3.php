<?php
/*
  $Id: upgrade_3.php,v 1.62 2003/07/12 09:00:26 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/
?>

<p class="pageTitle">Upgrade</p>

<?php
  $db = array();
  $db['DB_SERVER'] = trim(stripslashes($HTTP_POST_VARS['DB_SERVER']));
  $db['DB_SERVER_USERNAME'] = trim(stripslashes($HTTP_POST_VARS['DB_SERVER_USERNAME']));
  $db['DB_SERVER_PASSWORD'] = trim(stripslashes($HTTP_POST_VARS['DB_SERVER_PASSWORD']));
  $db['DB_DATABASE'] = trim(stripslashes($HTTP_POST_VARS['DB_DATABASE']));

  osc_db_connect($db['DB_SERVER'], $db['DB_SERVER_USERNAME'], $db['DB_SERVER_PASSWORD']);
  osc_db_select_db($db['DB_DATABASE']);

  function osc_get_languages() {
    $languages_query = osc_db_query("select languages_id, name, code, image, directory from languages order by sort_order");
    while ($languages = osc_db_fetch_array($languages_query)) {
      $languages_array[] = array('id' => $languages['languages_id'],
                                 'name' => $languages['name'],
                                 'code' => $languages['code'],
                                 'image' => $languages['image'],
                                 'directory' => $languages['directory']
                                );
    }

    return $languages_array;
  }

  function osc_currency_format($number, $calculate_currency_value = true, $currency_code = DEFAULT_CURRENCY, $value = '') {
    $currency_query = osc_db_query("select symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, value from currencies where code = '" . $currency_code . "'");
    $currency = osc_db_fetch_array($currency_query);

    if ($calculate_currency_value == true) {
      if (strlen($currency_code) == 3) {
        if ($value) {
          $rate = $value;
        } else {
          $rate = $currency['value'];
        }
      } else {
        $rate = 1;
      }
      $number2currency = $currency['symbol_left'] . number_format(($number * $rate), $currency['decimal_places'], $currency['decimal_point'], $currency['thousands_point']) . $currency['symbol_right'];
    } else {
      $number2currency = $currency['symbol_left'] . number_format($number, $currency['decimal_places'], $currency['decimal_point'], $currency['thousands_point']) . $currency['symbol_right'];
    }

    return $number2currency;
  }

  osc_set_time_limit(0);

  $languages = osc_get_languages();

// send data to the browser, so the flushing works with IE
  for ($i=0; $i<300; $i++) print(' ');
  print ("\n");
?>

<p><span id="addressBook"><span id="addressBookMarker">-</span> Address Book</span><br>
<span id="banners"><span id="bannersMarker">-</span> Banners</span><br>
<span id="categories"><span id="categoriesMarker">-</span> Categories</span><br>
<span id="configuration"><span id="configurationMarker">-</span> Configuration</span><br>
<span id="currencies"><span id="currenciesMarker">-</span> Currencies</span><br>
<span id="customers"><span id="customersMarker">-</span> Customers</span><br>
<span id="images"><span id="imagesMarker">-</span> Images</span><br>
<span id="languages"><span id="languagesMarker">-</span> Languages</span><br>
<span id="manufacturers"><span id="manufacturersMarker">-</span> Manufacturers</span><br>
<span id="orders"><span id="ordersMarker">-</span> Orders</span><br>
<span id="products"><span id="productsMarker">-</span> Products</span><br>
<span id="reviews"><span id="reviewsMarker">-</span> Reviews</span><br>
<span id="sessions"><span id="sessionsMarker">-</span> Sessions</span><br>
<span id="specials"><span id="specialsMarker">-</span> Specials</span><br>
<span id="taxes"><span id="taxesMarker">-</span> Taxes</span><br>
<span id="whosOnline"><span id="whosOnlineMarker">-</span> Whos Online</span></p>

<p>Status: <span id="statusText">Preparing</span></p>

<?php flush(); ?>

<script language="javascript"><!--
changeStyle('addressBook', 'bold');
changeText('addressBookMarker', '?');
changeText('statusText', 'Updating Address Book');
//--></script>

<?php
  flush();

  osc_db_query("alter table address_book add customers_id int not null after address_book_id");
  osc_db_query("alter table address_book add entry_company varchar(32) after entry_gender");

  osc_db_query("alter table customers add customers_default_address_id int(5) not null after customers_email_address");

  $entries_query = osc_db_query("select address_book_id, customers_id from address_book_to_customers");
  while ($entries = osc_db_fetch_array($entries_query)) {
    osc_db_query("update address_book set customers_id = '" . $entries['customers_id'] . "' where address_book_id = '" . $entries['address_book_id'] . "'");
  }

  $customer_query = osc_db_query("select customers_id, customers_gender, customers_firstname, customers_lastname, customers_street_address, customers_suburb, customers_postcode, customers_city, customers_state, customers_country_id, customers_zone_id from customers");
  while ($customer = osc_db_fetch_array($customer_query)) {
    osc_db_query("insert into address_book (customers_id, entry_gender, entry_company, entry_firstname, entry_lastname, entry_street_address, entry_suburb, entry_postcode, entry_city, entry_state, entry_country_id, entry_zone_id) values ('" . $customer['customers_id'] . "', '" . $customer['customers_gender'] . "', '', '" . addslashes($customer['customers_firstname']) . "', '" . addslashes($customer['customers_lastname']) . "', '" . addslashes($customer['customers_street_address']) . "', '" . addslashes($customer['customers_suburb']) . "', '" . addslashes($customer['customers_postcode']) . "', '" . addslashes($customer['customers_city']) . "', '" . addslashes($customer['customers_state']) . "', '" . $customer['customers_country_id'] . "', '" . $customer['customers_zone_id'] . "')");

    $address_book_id = osc_db_insert_id();

    osc_db_query("update customers set customers_default_address_id = '" . $address_book_id . "' where customers_id = '" . $customer['customers_id'] . "'");
  }

  osc_db_query("alter table address_book add index idx_address_book_customers_id (customers_id)");

  osc_db_query("drop table address_book_to_customers");
?>
<script language="javascript"><!--
changeStyle('addressBook', 'normal');
changeText('addressBookMarker', '*');
changeText('statusText', 'Updating Address Book .. done!');

changeStyle('banners', 'bold');
changeText('bannersMarker', '?');
changeText('statusText', 'Updating Banners');
//--></script>

<?php
  flush();

  osc_db_query("create table banners ( banners_id int(5) not null auto_increment, banners_title varchar(64) not null, banners_url varchar(255) not null, banners_image varchar(64) not null, banners_group varchar(10) not null, banners_html_text text, expires_impressions int(7) default '0', expires_date datetime default null, date_scheduled datetime default null, date_added datetime not null, date_status_change datetime default null, status int(1) default '1' not null, primary key (banners_id) )");
  osc_db_query("create table banners_history ( banners_history_id int(5) not null auto_increment, banners_id int(5) not null, banners_shown int(5) not null default '0', banners_clicked int(5) not null default '0', banners_history_date datetime not null, primary key (banners_history_id) )");
  osc_db_query("insert into banners values (1, 'osCommerce', 'http://www.oscommerce.com', 'banners/oscommerce.gif', '468x50', '', 0, null, null, now(), null, 1)");

?>
<script language="javascript"><!--
changeStyle('banners', 'normal');
changeText('bannersMarker', '*');
changeText('statusText', 'Updating Banners .. done!');

changeStyle('categories', 'bold');
changeText('categoriesMarker', '?');
changeText('statusText', 'Updating Categories');
//--></script>

<?php
  flush();

  osc_db_query("create table categories_description ( categories_id int(5) default '0' not null, language_id int(5) default '1' not null, categories_name varchar(32) not null, primary key (categories_id, language_id), key idx_categories_name (categories_name) )");

  $categories_query = osc_db_query("select categories_id, categories_name from categories order by categories_id");
  while ($categories = osc_db_fetch_array($categories_query)) {
    for ($i=0; $i<sizeof($languages); $i++) {
      osc_db_query("insert into categories_description (categories_id, language_id, categories_name) values ('" . $categories['categories_id'] . "', '" . $languages[$i]['id'] . "', '" . addslashes($categories['categories_name']) . "')");
    }
  }

  osc_db_query("alter table categories drop index IDX_CATEGORIES_NAME");
  osc_db_query("alter table categories drop categories_name");
  osc_db_query("alter table categories change parent_id parent_id int(5) not null default '0'");
  osc_db_query("alter table categories add date_added datetime after sort_order");
  osc_db_query("alter table categories add last_modified datetime after date_added");
  osc_db_query("alter table categories add index idx_categories_parent_id (parent_id)");
?>
<script language="javascript"><!--
changeStyle('categories', 'normal');
changeText('categoriesMarker', '*');
changeText('statusText', 'Updating Categories .. done!');

changeStyle('configuration', 'bold');
changeText('configurationMarker', '?');
changeText('statusText', 'Updating Configuration');
//--></script>

<?php
  flush();

  osc_db_query("alter table configuration change last_modified last_modified datetime");
  osc_db_query("alter table configuration change date_added date_added datetime not null");
  osc_db_query("alter table configuration modify use_function varchar(255)");
  osc_db_query("alter table configuration add set_function varchar(255) after use_function");

  osc_db_query("update configuration set configuration_key = 'SHIPPING_ORIGIN_COUNTRY' where configuration_key = 'STORE_ORIGIN_COUNTRY'");
  osc_db_query("update configuration set configuration_key = 'SHIPPING_ORIGIN_ZIP' where configuration_key = 'STORE_ORIGIN_ZIP'");
  osc_db_query("update configuration set set_function = 'tep_cfg_pull_down_country_list(' where configuration_key = 'STORE_COUNTRY' or configuration_key = 'SHIPPING_ORIGIN_COUNTRY'");
  osc_db_query("update configuration set configuration_value = 'desc', configuration_description = 'This is the sort order used in the expected products box.', set_function = 'tep_cfg_select_option(array(\'asc\', \'desc\'), ' where configuration_key = 'EXPECTED_PRODUCTS_SORT'");
  osc_db_query("update configuration set configuration_value = 'date_expected', configuration_description = 'The column to sort by in the expected products box.', set_function = 'tep_cfg_select_option(array(\'products_name\', \'date_expected\'), ' where configuration_key = 'EXPECTED_PRODUCTS_FIELD'");
  osc_db_query("update configuration set use_function = 'tep_cfg_get_zone_name' where configuration_key = 'STORE_ZONE'");

  $config_query = osc_db_query("select configuration_key, configuration_value from configuration where configuration_key = 'IMAGE_REQUIRED'");
  $config_value = osc_db_fetch_array($config_query);
  if ($config_value['configuration_value'] == '1') $config_flag = 'true';
  else $config_flag = 'false';
  osc_db_query("update configuration set configuration_value = '" . $config_flag . "', set_function = 'tep_cfg_select_option(array(\'true\', \'false\'),' where configuration_key = 'IMAGE_REQUIRED'");

  $config_query = osc_db_query("select configuration_key, configuration_value from configuration where configuration_key = 'CONFIG_CALCULATE_IMAGE_SIZE'");
  $config_value = osc_db_fetch_array($config_query);
  if ($config_value['configuration_value'] == '1') $config_flag = 'true';
  else $config_flag = 'false';
  osc_db_query("update configuration set configuration_value = '" . $config_flag . "', set_function = 'tep_cfg_select_option(array(\'true\', \'false\'),' where configuration_key = 'CONFIG_CALCULATE_IMAGE_SIZE'");

  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Zone', 'STORE_ZONE', '18', 'The zone my store is located in', '1', '7', 'tep_cfg_get_zone_name', 'tep_cfg_pull_down_zone_list(', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Switch To Default Currency', 'USE_DEFAULT_LANGUAGE_CURRENCY', 'false', 'Automatically switch to the language\'s currency when it is changed', '1', '10', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Send Extra Order E-Mails To', 'SEND_EXTRA_ORDER_EMAILS_TO', '', 'Send extra order e-mails to the following e-mail addresses, in this format: Name 1 &lt;email@address1&gt;, Name 2 &lt;email@address2&gt;', '1', '11', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Use Search-Engine Safe URLs', 'SEARCH_ENGINE_FRIENDLY_URLS', 'false', 'Use search-engine safe urls for all site links', '1', '12', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display Cart After Adding Product', 'DISPLAY_CART', 'true', 'Display the shopping cart after adding a product (or return back to their origin)', '1', '14', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Allow Guest To Tell A Friend', 'ALLOW_GUEST_TO_TELL_A_FRIEND', 'false', 'Allow guests to tell a friend about a product', '1', '15', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Default Search Operator', 'ADVANCED_SEARCH_DEFAULT_OPERATOR', 'and', 'Default search operators', '1', '17', 'tep_cfg_select_option(array(\'and\', \'or\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Store Address and Phone', 'STORE_NAME_ADDRESS', '', 'This is the Store Name, Address and Phone used on printable documents and displayed online', '1', '18', 'tep_cfg_textarea(', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Show Category Counts', 'SHOW_COUNTS', 'true', 'Count recursively how many products are in each category', '1', '19', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Tax Decimal Places', 'TAX_DECIMAL_PLACES', '2', 'Pad the tax value this amount of decimal places', '1', '20', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display Prices with Tax', 'DISPLAY_PRICE_WITH_TAX', 'false', 'Display prices with tax included (true) or add the tax at the end (false)', '1', '21', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");

  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Company', 'ENTRY_COMPANY_LENGTH', '2', 'Minimum length of company name', '2', '6', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Best Sellers', 'MIN_DISPLAY_BESTSELLERS', '1', 'Minimum number of best sellers to display', '2', '15', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Also Purchased', 'MIN_DISPLAY_ALSO_PURCHASED', '1', 'Minimum number of products to display in the \'This Customer Also Purchased\' box', '2', '16', now())");

  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Manufacturers Select Size', 'MAX_MANUFACTURERS_LIST', '1', 'Used in manufacturers box; when this value is \'1\' the classic drop-down list will be used for the manufacturers box. Otherwise, a list-box with the specified number of rows will be displayed.', '3', '7', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('New Products Listing', 'MAX_DISPLAY_PRODUCTS_NEW', '10', 'Maximum number of new products to display in new products page', '3', '14', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Best Sellers', 'MAX_DISPLAY_BESTSELLERS', '10', 'Maximum number of best sellers to display', '3', '15', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Also Purchased', 'MAX_DISPLAY_ALSO_PURCHASED', '5', 'Maximum number of products to display in the \'This Customer Also Purchased\' box', '3', '16', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Customer Order History Box', 'MAX_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX', '6', 'Maximum number of products to display in the customer order history box', '3', '17', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Order History', 'MAX_DISPLAY_ORDER_HISTORY', '10', 'Maximum number of orders to display in the order history page', '3', '18', now())");

  osc_db_query("delete from configuration where configuration_group_id = '5'");

  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Gender', 'ACCOUNT_GENDER', 'true', 'Display gender in the customers account', '5', '1', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Date of Birth', 'ACCOUNT_DOB', 'true', 'Display date of birth in the customers account', '5', '2', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Company', 'ACCOUNT_COMPANY', 'true', 'Display company in the customers account', '5', '3', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Suburb', 'ACCOUNT_SUBURB', 'true', 'Display suburb in the customers account', '5', '4', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('State', 'ACCOUNT_STATE', 'true', 'Display state in the customers account', '5', '5', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");

  osc_db_query("delete from configuration where configuration_group_id = '6'");

  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Installed Modules', 'MODULE_PAYMENT_INSTALLED', 'cc.php;cod.php', 'List of payment module filenames separated by a semi-colon. This is automatically updated. No need to edit. (Example: cc.php;cod.php;paypal.php)', '6', '0', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Installed Modules', 'MODULE_SHIPPING_INSTALLED', '', 'List of shipping module filenames separated by a semi-colon. This is automatically updated. No need to edit. (Example: ups.php;flat.php;item.php)', '6', '0', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Installed Modules', 'MODULE_ORDER_TOTAL_INSTALLED', 'ot_subtotal.php;ot_tax.php;ot_shipping.php;ot_total.php', 'List of order_total module filenames separated by a semi-colon. This is automatically updated. No need to edit. (Example: ot_subtotal.php;ot_tax.php;ot_shipping.php;ot_total.php)', '6', '0', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Cash On Delivery Module', 'MODULE_PAYMENT_COD_STATUS', 'True', 'Do you want to accept Cash On Delevery payments?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_COD_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_COD_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_COD_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Credit Card Module', 'MODULE_PAYMENT_CC_STATUS', 'True', 'Do you want to accept credit card payments?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Split Credit Card E-Mail Address', 'MODULE_PAYMENT_CC_EMAIL', '', 'If an e-mail address is entered, the middle digits of the credit card number will be sent to the e-mail address (the outside digits are stored in the database with the middle digits censored)', '6', '0', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_CC_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0' , now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_CC_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_CC_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Default Currency', 'DEFAULT_CURRENCY', 'USD', 'Default Currency', '6', '0', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Default Language', 'DEFAULT_LANGUAGE', 'en', 'Default Language', '6', '0', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Default Order Status For New Orders', 'DEFAULT_ORDERS_STATUS_ID', '1', 'When a new order is created, this order status will be assigned to it.', '6', '0', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display Shipping', 'MODULE_ORDER_TOTAL_SHIPPING_STATUS', 'true', 'Do you want to display the order shipping cost?', '6', '1','tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER', '2', 'Sort order of display.', '6', '2', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Allow Free Shipping', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING', 'false', 'Do you want to allow free shipping?', '6', '3', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added) values ('Free Shipping For Orders Over', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER', '50', 'Provide free shipping for orders over the set amount.', '6', '4', 'currencies->format', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Provide Free Shipping For Orders Made', 'MODULE_ORDER_TOTAL_SHIPPING_DESTINATION', 'national', 'Provide free shipping for orders sent to the set destination.', '6', '5', 'tep_cfg_select_option(array(\'national\', \'international\', \'both\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display Sub-Total', 'MODULE_ORDER_TOTAL_SUBTOTAL_STATUS', 'true', 'Do you want to display the order sub-total cost?', '6', '1','tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER', '1', 'Sort order of display.', '6', '2', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display Tax', 'MODULE_ORDER_TOTAL_TAX_STATUS', 'true', 'Do you want to display the order tax value?', '6', '1','tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ORDER_TOTAL_TAX_SORT_ORDER', '3', 'Sort order of display.', '6', '2', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display Total', 'MODULE_ORDER_TOTAL_TOTAL_STATUS', 'true', 'Do you want to display the total order value?', '6', '1','tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER', '4', 'Sort order of display.', '6', '2', now())");

  osc_db_query("delete from configuration where configuration_group_id = '7' and configuration_key != 'SHIPPING_BOX_WEIGHT' and configuration_key != 'SHIPPING_BOX_PADDING' and configuration_key != 'SHIPPING_MAX_WEIGHT' and configuration_key != 'SHIPPING_ORIGIN_ZIP' and configuration_key != 'SHIPPING_ORIGIN_COUNTRY'");
  osc_db_query("update configuration set sort_order = '5' where sort_order = '2'");
  osc_db_query("update configuration set configuration_group_id = '7', sort_order = '1' where configuration_key = 'SHIPPING_ORIGIN_ZIP'");
  osc_db_query("update configuration set configuration_group_id = '7', sort_order = '2' where configuration_key = 'SHIPPING_ORIGIN_COUNTRY'");

  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Check stock level', 'STOCK_CHECK', 'false', 'Check to see if sufficent stock is available', '9', '1', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Subtract stock', 'STOCK_LIMITED', 'true', 'Subtract product in stock by product orders', '9', '2', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Allow Checkout', 'STOCK_ALLOW_CHECKOUT', 'true', 'Allow customer to checkout even if there is insufficient stock', '9', '3', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Mark product out of stock', 'STOCK_MARK_PRODUCT_OUT_OF_STOCK', '***', 'Display something on screen so customer can see which product has insufficient stock', '9', '4', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Stock Re-order level', 'STOCK_REORDER_LEVEL', '5', 'Define when stock needs to be re-ordered', '9', '5', now())");
  
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Store Page Parse Time', 'STORE_PAGE_PARSE_TIME', 'false', 'Store the time it takes to parse a page', '10', '1', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Log Destination', 'STORE_PAGE_PARSE_TIME_LOG', '/var/log/www/tep/page_parse_time.log', 'Directory and filename of the page parse time log', '10', '2', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Log Date Format', 'STORE_PARSE_DATE_TIME_FORMAT', '%d/%m/%Y %H:%M:%S', 'The date format', '10', '3', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display The Page Parse Time', 'DISPLAY_PAGE_PARSE_TIME', 'true', 'Display the page parse time (store page parse time must be enabled)', '10', '4', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Store Database Queries', 'STORE_DB_TRANSACTIONS', 'false', 'Store the database queries in the page parse time log (PHP4 only)', '10', '5', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");

  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Use Cache', 'USE_CACHE', 'false', 'Use caching features', '11', '1', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Cache Directory', 'DIR_FS_CACHE', '/tmp/', 'The directory where the cached files are saved', '11', '2', now())");

  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('E-Mail Transport Method', 'EMAIL_TRANSPORT', 'sendmail', 'Defines if this server uses a local connection to sendmail or uses an SMTP connection via TCP/IP. Servers running ong Windows or MacOS should change this setting to SMTP.', '12', '1', 'tep_cfg_select_option(array(\'sendmail\', \'smtp\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('E-Mail Linefeeds', 'EMAIL_LINEFEED', 'LF', 'Defines the character sequence used to separate mail headers. When using sendmail use LF, when using smtp use CRLF.', '12', '2', 'tep_cfg_select_option(array(\'LF\', \'CRLF\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Use MIME HTML When Sending E-Mails', 'EMAIL_USE_HTML', 'false', 'Send e-mails in HTML format', '12', '3', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Verfiy E-Mail Addresses Through DNS', 'ENTRY_EMAIL_ADDRESS_CHECK', 'false', 'Verfiy e-mail address through a DNS server', '12', '4', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Send E-Mails', 'SEND_EMAILS', 'true', 'Send out e-mails', '12', '5', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");

  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('enable download', 'download_enabled', 'false', 'enable the products download functions.', '13', '1', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('download by redirect', 'download_by_redirect', 'false', 'use browser redirection for download. disable on non-unix systems.', '13', '2', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('expiry delay (days)' ,'download_max_days', '7', 'set number of days before the download link expires. 0 means no limit.', '13', '3', '', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('maximum number of downloads' ,'download_max_count', '5', 'set the maximum number of downloads. 0 means no download authorized.', '13', '4', '', now())");

  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable GZip Compression', 'GZIP_COMPRESSION', 'false', 'Enable HTTP GZip compression.', '14', '1', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Compression Level', 'GZIP_LEVEL', '5', 'Use this compression level 0-9 (0 = minimum, 9 = maximum).', '14', '2', now())");

  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Session Directory', 'SESSION_WRITE_DIRECTORY', '/tmp', 'If sessions are file based, store them in this directory.', '15', '1', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Force Cookie Use', 'SESSION_FORCE_COOKIE_USE', 'False', 'Force the use of sessions when cookies are only enabled.', '15', '2', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Check SSL Session ID', 'SESSION_CHECK_SSL_SESSION_ID', 'False', 'Validate the SSL_SESSION_ID on every secure HTTPS page request.', '15', '3', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Check User Agent', 'SESSION_CHECK_USER_AGENT', 'False', 'Validate the clients browser user agent on every page request.', '15', '4', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Check IP Address', 'SESSION_CHECK_IP_ADDRESS', 'False', 'Validate the clients IP address on every page request.', '15', '5', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Prevent Spider Sessions', 'SESSION_BLOCK_SPIDERS', 'False', 'Prevent known spiders from starting a session.', '15', '6', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Recreate Session', 'SESSION_RECREATE', 'False', 'Recreate the session to generate a new session ID when the customer logs on or creates an account (PHP >=4.1 needed).', '15', '7', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");

  osc_db_query("delete from configuration_group");

  osc_db_query("alter table configuration_group add visible int(1) default '1'");

  osc_db_query("insert into configuration_group values ('1', 'My Store', 'General information about my store', '1', '1')");
  osc_db_query("insert into configuration_group values ('2', 'Minimum Values', 'The minimum values for functions / data', '2', '1')");
  osc_db_query("insert into configuration_group values ('3', 'Maximum Values', 'The maximum values for functions / data', '3', '1')");
  osc_db_query("insert into configuration_group values ('4', 'Images', 'Image parameters', '4', '1')");
  osc_db_query("insert into configuration_group values ('6', 'Module Options', 'Hidden from configuration', '6', '0')");
  osc_db_query("insert into configuration_group values ('5', 'Customer Details', 'Customer account configuration', '5', '1')");
  osc_db_query("insert into configuration_group values ('7', 'Shipping/Packaging', 'Shipping options available at my store', '7', '1')");
  osc_db_query("insert into configuration_group values ('8', 'Product Listing', 'Product Listing    configuration options', '8', '1')");
  osc_db_query("insert into configuration_group values ('9', 'Stock', 'Stock configuration options', '9', '1')");
  osc_db_query("insert into configuration_group values ('10', 'Logging', 'Logging configuration options', '10', '1')");
  osc_db_query("insert into configuration_group values ('11', 'Cache', 'Caching configuration options', '11', '1')");
  osc_db_query("insert into configuration_group values ('12', 'E-Mail Options', 'General setting for E-Mail transport and HTML E-Mails', '12', '1')");
  osc_db_query("insert into configuration_group values ('13', 'Download', 'Downloadable products options', '13', '1')");
  osc_db_query("insert into configuration_group values ('14', 'GZip Compression', 'GZip compression options', '14', '1')");
  osc_db_query("insert into configuration_group values ('15', 'Sessions', 'Session options', '15', '1')");

?>

<script language="javascript"><!--
changeStyle('configuration', 'normal');
changeText('configurationMarker', '*');
changeText('statusText', 'Updating Configuration .. done!');

changeStyle('currencies', 'bold');
changeText('currenciesMarker', '?');
changeText('statusText', 'Updating Currencies');
//--></script>

<?php
  flush();

  osc_db_query("alter table currencies add value float(13,8)");
  osc_db_query("alter table currencies add last_updated datetime");

  osc_db_query("update currencies set value = '1'");
?>

<script language="javascript"><!--
changeStyle('currencies', 'normal');
changeText('currenciesMarker', '*');
changeText('statusText', 'Updating Currencies .. done!');

changeStyle('customers', 'bold');
changeText('customersMarker', '?');
changeText('statusText', 'Updating Customers');
//--></script>

<?php
  flush();

  osc_db_query("alter table customers drop customers_street_address");
  osc_db_query("alter table customers drop customers_suburb");
  osc_db_query("alter table customers drop customers_postcode");
  osc_db_query("alter table customers drop customers_city");
  osc_db_query("alter table customers drop customers_state");
  osc_db_query("alter table customers drop customers_zone_id");
  osc_db_query("alter table customers drop customers_country_id");
  osc_db_query("alter table customers change customers_dob customers_dob datetime not null default '0000-00-00 00:00:00'");
  osc_db_query("alter table customers add customers_newsletter char(1)");

  osc_db_query("alter table customers_basket change products_id products_id tinytext not null");
  osc_db_query("alter table customers_basket change customers_basket_date_added customers_basket_date_added varchar(8)");
  osc_db_query("alter table customers_basket change final_price final_price decimal(15,4) not null");

  osc_db_query("alter table customers_basket_attributes change products_id products_id tinytext not null");

  osc_db_query("alter table customers_info change customers_info_date_account_created customers_info_date_account_created datetime");
  osc_db_query("alter table customers_info change customers_info_date_of_last_logon customers_info_date_of_last_logon datetime");
  osc_db_query("alter table customers_info change customers_info_date_account_last_modified customers_info_date_account_last_modified datetime");
  osc_db_query("alter table customers_info add global_product_notifications int(1) default '0'");

  osc_db_query("create table newsletters ( newsletters_id int(5) not null auto_increment, title varchar(255) not null, content text not null, module varchar(255) not null, date_added datetime not null, date_sent datetime, status int(1), locked int(1) default '0', primary key (newsletters_id))");
?>

<script language="javascript"><!--
changeStyle('customers', 'normal');
changeText('customersMarker', '*');
changeText('statusText', 'Updating Customers .. done!');

changeStyle('images', 'bold');
changeText('imagesMarker', '?');
changeText('statusText', 'Updating Images');
//--></script>

<?php
  flush();

// categories
  $categories_query = osc_db_query("select categories_id, categories_image from categories where left(categories_image, 7) = 'images/'");
  while ($categories = osc_db_fetch_array($categories_query)) {
    osc_db_query("update categories set categories_image = substring('" . $categories['categories_image'] . "', 8) where categories_id = '" . $categories['categories_id'] . "'");
  }

// manufacturers
  $manufacturers_query = osc_db_query("select manufacturers_id, manufacturers_image from manufacturers where left(manufacturers_image, 7) = 'images/'");
  while ($manufacturers = osc_db_fetch_array($manufacturers_query)) {
    osc_db_query("update manufacturers set manufacturers_image = substring('" . $manufacturers['manufacturers_image'] . "', 8) where manufacturers_id = '" . $manufacturers['manufacturers_id'] . "'");
  }

// products
  $products_query = osc_db_query("select products_id, products_image from products where left(products_image, 7) = 'images/'");
  while ($products = osc_db_fetch_array($products_query)) {
    osc_db_query("update products set products_image = substring('" . $products['products_image'] . "', 8) where products_id = '" . $products['products_id'] . "'");
  }
?>

<script language="javascript"><!--
changeStyle('images', 'normal');
changeText('imagesMarker', '*');
changeText('statusText', 'Updating Images .. done!');

changeStyle('languages', 'bold');
changeText('languagesMarker', '?');
changeText('statusText', 'Updating Languages');
//--></script>

<?php
  flush();

  osc_db_query("update languages set image = 'icon.gif'");
?>

<script language="javascript"><!--
changeStyle('languages', 'normal');
changeText('languagesMarker', '*');
changeText('statusText', 'Updating Languages .. done!');

changeStyle('manufacturers', 'bold');
changeText('manufacturersMarker', '?');
changeText('statusText', 'Updating Manufacturers');
//--></script>

<?php
  flush();

  osc_db_query("alter table manufacturers add date_added datetime null after manufacturers_image, add last_modified datetime null after date_added");
  osc_db_query("create table manufacturers_info (manufacturers_id int(5) not null, languages_id int(5) not null, manufacturers_url varchar(255) not null, url_clicked int(5) not null default '0', date_last_click datetime, primary key (manufacturers_id, languages_id))");
?>

<script language="javascript"><!--
changeStyle('manufacturers', 'normal');
changeText('manufacturersMarker', '*');
changeText('statusText', 'Updating Manufacturers .. done!');

changeStyle('orders', 'bold');
changeText('ordersMarker', '?');
changeText('statusText', 'Updating Orders');
//--></script>

<?php
  flush();

  osc_db_query("alter table orders add customers_company varchar(32) after customers_name");
  osc_db_query("alter table orders add delivery_company varchar(32) after delivery_name");
  osc_db_query("alter table orders add billing_name varchar(64) not null after delivery_address_format_id");
  osc_db_query("alter table orders add billing_company varchar(32) after billing_name");
  osc_db_query("alter table orders add billing_street_address varchar(64) not null after billing_company");
  osc_db_query("alter table orders add billing_suburb varchar(32) after billing_street_address");
  osc_db_query("alter table orders add billing_city varchar(32) not null after billing_suburb");
  osc_db_query("alter table orders add billing_postcode varchar(10) not null after billing_city");
  osc_db_query("alter table orders add billing_state varchar(32) after billing_postcode");
  osc_db_query("alter table orders add billing_country varchar(32) not null after billing_state");
  osc_db_query("alter table orders add billing_address_format_id int(5) not null after billing_country");
  osc_db_query("alter table orders change payment_method payment_method varchar(32) not null");
  osc_db_query("alter table orders change date_purchased date_purchased datetime");
  osc_db_query("alter table orders change last_modified last_modified datetime");
  osc_db_query("alter table orders change orders_date_finished orders_date_finished datetime");
  osc_db_query("alter table orders_products add column products_model varchar(12)");
  osc_db_query("alter table orders_products change products_price products_price decimal(15,4) not null");
  osc_db_query("alter table orders_products change final_price final_price decimal(15,4) not null");
  osc_db_query("alter table orders_products_attributes change options_values_price options_values_price decimal(15,4) not null");

  osc_db_query("create table orders_status ( orders_status_id int(5) default '0' not null, language_id int(5) default '1' not null, orders_status_name varchar(32) not null, primary key (orders_status_id, language_id), key idx_orders_status_name (orders_status_name))");

  for ($i=0; $i<sizeof($languages); $i++) {
    osc_db_query("insert into orders_status values ('1', '" . $languages[$i]['id'] . "', 'Pending')");
    osc_db_query("insert into orders_status values ('2', '" . $languages[$i]['id'] . "', 'Processing')");
    osc_db_query("insert into orders_status values ('3', '" . $languages[$i]['id'] . "', 'Delivered')");
  }

  osc_db_query("update orders set orders_status = '1' where orders_status = 'Pending'");
  osc_db_query("update orders set orders_status = '2' where orders_status = 'Processing'");
  osc_db_query("update orders set orders_status = '3' where orders_status = 'Delivered'");

  $status = array();
  $orders_status_query = osc_db_query("select distinct orders_status from orders where orders_status not in ('1', '2', '3')");
  while ($orders_status = osc_db_fetch_array($orders_status_query)) {
    $status[] = array('text' => $orders_status['orders_status']);
  }

  $orders_status_id = 4;
  for ($i=0; $i<sizeof($status); $i++) {
    for ($j=0; $j<sizeof($languages); $j++) {
      osc_db_query("insert into orders_status values ('" . $orders_status_id . "', '" . $languages[$j]['id'] . "', '" . $status[$i]['text'] . "')");
    }
    osc_db_query("update orders set orders_status = '" . $orders_status_id . "' where orders_status = '" . $status[$i]['text'] . "'");
    $orders_status_id++;
  }

  osc_db_query("alter table orders change orders_status orders_status int(5) not null");

  osc_db_query("create table orders_status_history ( orders_status_history_id int(5) not null auto_increment, orders_id int(5) not null, orders_status_id int(5) not null, date_added datetime not null, customer_notified int(1) default '0', comments text, primary key (orders_status_history_id))");

  $orders_query = osc_db_query("select orders_id, date_purchased, comments from orders where comments <> ''");
  while ($order = osc_db_fetch_array($orders_query)) {
    osc_db_query("insert into orders_status_history (orders_id, orders_status_id, date_added, comments) values ('" . $order['orders_id'] . "', '1', '" . $order['date_purchased'] . "', '" . $order['comments'] . "')");
  }
  osc_db_query("alter table orders drop comments");

  $orders_products_query = osc_db_query("select op.orders_products_id, opa.orders_products_attributes_id, op.products_id from orders_products op, orders_products_attributes opa where op.orders_id = opa.orders_id");
  while ($orders_products = osc_db_fetch_array($orders_products_query)) {
    osc_db_query("update orders_products_attributes set orders_products_id = '" . $orders_products['orders_products_id'] . "' where orders_products_attributes_id = '" . $orders_products['orders_products_attributes_id'] . "' and orders_products_id = '" . $orders_products['products_id'] . "'");
  }

  osc_db_query("create table orders_products_download ( orders_products_download_id int(5) not null auto_increment, orders_id int(5) not null default '0', orders_products_id int(5) not null default '0', orders_products_filename varchar(255) not null, download_maxdays int(2) not null default '0', download_count int(2) not null default '0', primary key (orders_products_download_id))");

  osc_db_query("create table orders_total ( orders_total_id int unsigned not null auto_increment, orders_id int not null, title varchar(255) not null, text varchar(255) not null, value decimal(15,4) not null, class varchar(32) not null, sort_order int not null, primary key (orders_total_id), key idx_orders_total_orders_id (orders_id))");

  $i = 0;
  $orders_query = osc_db_query("select orders_id, shipping_method, shipping_cost, currency, currency_value from orders");
  while ($orders = osc_db_fetch_array($orders_query)) {
    $o = array();
    $total_cost = 0;

    $o['id'] = $orders['orders_id'];
    $o['shipping_method'] = $orders['shipping_method'];
    $o['shipping_cost'] = $orders['shipping_cost'];
    $o['currency'] = $orders['currency'];
    $o['currency_value'] = $orders['currency_value'];
    $o['tax'] = 0;

    $orders_products_query = osc_db_query("select final_price, products_tax, products_quantity from orders_products where orders_id = '" . $orders['orders_id'] . "'");
    while ($orders_products = osc_db_fetch_array($orders_products_query)) {
      $o['products'][$i]['final_price'] = $orders_products['final_price'];
      $o['products'][$i]['qty'] = $orders_products['products_quantity'];

      $o['products'][$i]['tax_groups']["{$orders_products['products_tax']}"] += $orders_products['products_tax']/100 * ($orders_products['final_price'] * $orders_products['products_quantity']);
      $o['tax'] += $orders_products['products_tax']/100 * ($orders_products['final_price'] * $orders_products['products_quantity']);

      $total_cost += ($o['products'][$i]['final_price'] * $o['products'][$i]['qty']);
    }

    $subtotal_text = osc_currency_format($total_cost, true, $o['currency'], $o['currency_value']);
    $subtotal_value = $total_cost;

    osc_db_query("insert into orders_total (orders_total_id, orders_id, title, text, value, class, sort_order) values ('', '" . $o['id'] . "', 'Sub-Total:', '" . $subtotal_text . "', '" . $subtotal_value . "', 'ot_subtotal', '1')");

    $tax_text = osc_currency_format($o['tax'], true, $o['currency'], $o['currency_value']);
    $tax_value = $o['tax'];
    osc_db_query("insert into orders_total (orders_total_id, orders_id, title, text, value, class, sort_order) values ('', '" . $o['id'] . "', 'Tax:', '" . $tax_text . "', '" . $tax_value . "', 'ot_tax', '2')");

    if (strlen($o['shipping_method']) < 1) {
      $o['shipping_method'] = 'Shipping:';
    } else {
      $o['shipping_method'] .= ':';
    }

    if ($o['shipping_cost'] > 0) {
      $shipping_text = osc_currency_format($o['shipping_cost'], true, $o['currency'], $o['currency_value']);
      $shipping_value = $o['shipping_cost'];
      osc_db_query("insert into orders_total (orders_total_id, orders_id, title, text, value, class, sort_order) values ('', '" . $o['id'] . "', '" . $o['shipping_method'] . "', '" . $shipping_text . "', '" . $shipping_value . "', 'ot_shipping', '3')");
    }

    $total_text = osc_currency_format($total_cost + $o['tax'] + $o['shipping_cost'], true, $o['currency'], $o['currency_value']);
    $total_value = $total_cost + $o['tax'] + $o['shipping_cost'];
    osc_db_query("insert into orders_total (orders_total_id, orders_id, title, text, value, class, sort_order) values ('', '" . $o['id'] . "', 'Total:', '" . $total_text . "', '" . $total_value . "', 'ot_total', '4')");

    $i++;
  }

  osc_db_query("alter table orders drop shipping_method");
  osc_db_query("alter table orders drop shipping_cost");
?>

<script language="javascript"><!--
changeStyle('orders', 'normal');
changeText('ordersMarker', '*');
changeText('statusText', 'Updating Orders .. done!');

changeStyle('products', 'bold');
changeText('productsMarker', '?');
changeText('statusText', 'Updating Products');
//--></script>

<?php
  flush();

  osc_db_query("create table products_description ( products_id int(5) not null auto_increment, language_id int(5) not null default '1', products_name varchar(64) not null default '',  products_description text, products_url varchar(255), products_viewed int(5) default '0', primary key (products_id, language_id), key products_name (products_name))");

  $products_query = osc_db_query("select products_id, products_name, products_description, products_url, products_viewed from products order by products_id");
  while ($products = osc_db_fetch_array($products_query)) {
    for ($i=0; $i<sizeof($languages); $i++) {
      osc_db_query("insert into products_description (products_id, language_id, products_name, products_description, products_url, products_viewed) values ('" . $products['products_id'] . "', '" . $languages[$i]['id'] . "', '" . addslashes($products['products_name']) . "', '" . addslashes($products['products_description']) . "', '" . addslashes($products['products_url']) . "', '" . $products['products_viewed'] . "')");
    }
  }

  osc_db_query("update products set products_date_added = now() where products_date_added is null");
  osc_db_query("alter table products change products_date_added products_date_added datetime not null");
  osc_db_query("alter table products change products_price products_price decimal(15,4) not null");
  osc_db_query("alter table products add index idx_products_date_added (products_date_added)");

  osc_db_query("alter table products drop index products_name");

  osc_db_query("alter table products drop products_url");
  osc_db_query("alter table products drop products_name");
  osc_db_query("alter table products drop products_description");
  osc_db_query("alter table products drop products_viewed");

  osc_db_query("alter table products add products_date_available datetime");
  osc_db_query("alter table products add products_last_modified datetime");

  osc_db_query("alter table products add products_ordered int default '0' not null");

  $products_query = osc_db_query("select products_id, sum(products_quantity) as products_ordered from orders_products group by products_id");
  while ($products = osc_db_fetch_array($products_query)) {
    osc_db_query("update products set products_ordered = '" . $products['products_ordered'] . "' where products_id = '" . $products['products_id'] . "'");
  }

  osc_db_query("drop table products_expected");

  osc_db_query("alter table products_attributes change options_values_price options_values_price decimal(15,4) not null");

  osc_db_query("alter table products_options change products_options_id products_options_id int(5) not null default '0'");
  osc_db_query("alter table products_options add language_id int(5) not null default '1' after products_options_id");
  osc_db_query("alter table products_options drop primary key");
  osc_db_query("alter table products_options add primary key (products_options_id, language_id)");

  $products_query = osc_db_query("select products_options_id, language_id, products_options_name from products_options order by products_options_id");
  while ($products = osc_db_fetch_array($products_query)) {
    for ($i=0; $i<sizeof($languages); $i++) {
      osc_db_query("replace into products_options (products_options_id, language_id, products_options_name) values ('" . $products['products_options_id'] . "', '" . $languages[$i]['id'] . "', '" . addslashes($products['products_options_name']) . "')");
    }
  }

  osc_db_query("alter table products_options_values change products_options_values_id products_options_values_id int(5) not null default '0'");
  osc_db_query("alter table products_options_values add language_id int(5) not null default '1' after products_options_values_id");
  osc_db_query("alter table products_options_values drop primary key");
  osc_db_query("alter table products_options_values add primary key (products_options_values_id, language_id)");

  $products_query = osc_db_query("select products_options_values_id, language_id, products_options_values_name from products_options_values order by products_options_values_id");
  while ($products = osc_db_fetch_array($products_query)) {
    for ($i=0; $i<sizeof($languages); $i++) {
      osc_db_query("replace into products_options_values (products_options_values_id, language_id, products_options_values_name) values ('" . $products['products_options_values_id'] . "', '" . $languages[$i]['id'] . "', '" . addslashes($products['products_options_values_name']) . "')");
    }
  }

  osc_db_query("alter table products_to_categories change products_id products_id int(5) not null");

  osc_db_query("create table products_attributes_download ( products_attributes_id int(5) not null, products_attributes_filename varchar(255) not null, products_attributes_maxdays int(2) default '0', products_attributes_maxcount int(2) default '0', primary key (products_attributes_id))");

  osc_db_query("create table products_notifications ( products_id int(5) not null, customers_id int(5) not null, date_added datetime not null, primary key (products_id, customers_id))");
?>

<script language="javascript"><!--
changeStyle('products', 'normal');
changeText('productsMarker', '*');
changeText('statusText', 'Updating Products .. done!');

changeStyle('reviews', 'bold');
changeText('reviewsMarker', '?');
changeText('statusText', 'Updating Reviews');
//--></script>

<?php
  flush();

  osc_db_query("create table reviews_description ( reviews_id int(5) not null, languages_id int(5) not null, reviews_text text not null, primary key (reviews_id, languages_id))");

  osc_db_query("alter table reviews add products_id int(5) not null default '0' after reviews_id");
  osc_db_query("alter table reviews add customers_id int(5) after products_id");
  osc_db_query("alter table reviews add customers_name varchar(64) not null default '' after customers_id");
  osc_db_query("alter table reviews add date_added datetime after reviews_rating");
  osc_db_query("alter table reviews add last_modified datetime after date_added");
  osc_db_query("alter table reviews add reviews_read int(5) not null default '0'");

  $reviews_query = osc_db_query("select r.reviews_id, re.products_id, re.customers_id, r.reviews_rating, re.date_added, re.reviews_read, r.reviews_text from reviews r, reviews_extra re where r.reviews_id = re.reviews_id order by r.reviews_id");
  while ($reviews = osc_db_fetch_array($reviews_query)) {
    $customer_query = osc_db_query("select customers_firstname, customers_lastname from customers where customers_id = '" . $reviews['customers_id'] . "'");
    if (osc_db_num_rows($customer_query)) {
      $customer = osc_db_fetch_array($customer_query);
      $customers_name = $customer['customers_firstname'] . ' ' . $customer['customers_lastname'];
    } else {
      $customers_name = '';
    }

    osc_db_query("update reviews set products_id = '" . $reviews['products_id'] . "', customers_id = '" . $reviews['customers_id'] . "', customers_name = '" . addslashes($customers_name) . "', date_added = '" . $reviews['date_added'] . "', last_modified = '', reviews_read = '" . $reviews['reviews_read'] . "' where reviews_id = '" . $reviews['reviews_id'] . "'");
    osc_db_query("insert into reviews_description (reviews_id, languages_id, reviews_text) values ('" . $reviews['reviews_id'] . "', '" . $languages[0]['id'] . "', '" . addslashes($reviews['reviews_text']) . "')");
  }

  osc_db_query("alter table reviews drop reviews_text");

  osc_db_query("drop table reviews_extra");
?>

<script language="javascript"><!--
changeStyle('reviews', 'normal');
changeText('reviewsMarker', '*');
changeText('statusText', 'Updating Reviews .. done!');

changeStyle('sessions', 'bold');
changeText('sessionsMarker', '?');
changeText('statusText', 'Updating Sessions');
//--></script>

<?php
  flush();

  osc_db_query("create table sessions (sesskey varchar(32) not null, expiry int(11) unsigned not null, value text not null, primary key (sesskey))");
?>

<script language="javascript"><!--
changeStyle('sessions', 'normal');
changeText('sessionsMarker', '*');
changeText('statusText', 'Updating Sessions .. done!');

changeStyle('specials', 'bold');
changeText('specialsMarker', '?');
changeText('statusText', 'Updating Specials');
//--></script>

<?php
  flush();

  osc_db_query("alter table specials change specials_date_added specials_date_added datetime");
  osc_db_query("alter table specials change specials_new_products_price specials_new_products_price decimal(15,4) not null");

  osc_db_query("alter table specials add specials_last_modified datetime");
  osc_db_query("alter table specials add expires_date datetime");
  osc_db_query("alter table specials add date_status_change datetime");
  osc_db_query("alter table specials add status int(1) NOT NuLL default '1'");
?>

<script language="javascript"><!--
changeStyle('specials', 'normal');
changeText('specialsMarker', '*');
changeText('statusText', 'Updating Specials .. done!');

changeStyle('taxes', 'bold');
changeText('taxesMarker', '?');
changeText('statusText', 'Updating Taxes');
//--></script>

<?php
  flush();

  osc_db_query("alter table tax_class change date_added date_added datetime not null");
  osc_db_query("alter table tax_class change last_modified last_modified datetime");

  osc_db_query("alter table tax_rates change date_added date_added datetime not null");
  osc_db_query("alter table tax_rates change last_modified last_modified datetime");

  osc_db_query("alter table tax_rates add tax_priority int(5) default '1' after tax_class_id");

  osc_db_query("create table geo_zones (geo_zone_id int(5) not null auto_increment, geo_zone_name varchar(32) not null, geo_zone_description varchar(255) not null, last_modified datetime, date_added datetime not null, primary key (geo_zone_id))");
  osc_db_query("create table zones_to_geo_zones (association_id int(5) not null auto_increment, zone_country_id int(5) not null, zone_id int(5), geo_zone_id int(5), last_modified datetime, date_added datetime not null, primary key (association_id))");

  osc_db_query("alter table zones change zone_code zone_code varchar(32) not null");

  osc_db_query("INSERT INTO geo_zones (geo_zone_id,geo_zone_name,geo_zone_description,last_modified,date_added) SELECT tr.tax_zone_id,zone_name,zone_name,NULL,now() from tax_rates tr,zones z,countries c WHERE tr.tax_zone_id=z.zone_id AND c.countries_id=z.zone_country_id GROUP BY tr.tax_zone_id");

  osc_db_query("INSERT INTO zones_to_geo_zones (zone_country_id,zone_id,geo_zone_id,date_added) SELECT z.zone_country_id, z.zone_id,tr.tax_zone_id,now() FROM tax_rates tr, zones z WHERE z.zone_id=tr.tax_zone_id GROUP BY tr.tax_zone_id");
?>

<script language="javascript"><!--
changeStyle('taxes', 'normal');
changeText('taxesMarker', '*');
changeText('statusText', 'Updating Taxes .. done!');

changeStyle('whosOnline', 'bold');
changeText('whosOnlineMarker', '?');
changeText('statusText', 'Updating Whos Online');
//--></script>

<?php
  flush();

  osc_db_query("create table whos_online (customer_id int(5),  full_name varchar(64) not null, session_id varchar(128) not null, ip_address varchar(15) not null, time_entry varchar(14) not null, time_last_click varchar(14) not null, last_page_url varchar(64) not null)");
?>

<script language="javascript"><!--
changeStyle('whosOnline', 'normal');
changeText('whosOnlineMarker', '*');
changeText('statusText', 'Updating Whos Online .. done!');

changeStyle('statusText', 'bold');
changeText('statusText', 'Update Complete!');
//--></script>

<?php flush(); ?>

<p>The database upgrade procedure was successful!</p>
