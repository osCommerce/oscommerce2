# $Id: oscommerce.sql,v 1.84 2003/05/27 17:32:16 hpdl Exp $
#
# osCommerce, Open Source E-Commerce Solutions
# http://www.oscommerce.com
#
# Copyright (c) 2003 osCommerce
#
# Released under the GNU General Public License
#
# NOTE: * Please make any modifications to this file by hand!
#       * DO NOT use a mysqldump created file for new changes!
#       * Please take note of the table structure, and use this
#         structure as a standard for future modifications!
#       * Any tables you add here should be added in admin/backup.php
#         and in catalog/install/includes/functions/database.php
#       * To see the 'diff'erence between MySQL databases, use
#         the mysqldiff perl script located in the extras
#         directory of the 'catalog' module.
#       * Comments should be like these, full line comments.
#         (don't use inline comments)

DROP TABLE IF EXISTS address_book;
CREATE TABLE address_book (
   address_book_id int NOT NULL auto_increment,
   customers_id int NOT NULL,
   entry_gender char(1) NOT NULL,
   entry_company varchar(32),
   entry_firstname varchar(32) NOT NULL,
   entry_lastname varchar(32) NOT NULL,
   entry_street_address varchar(64) NOT NULL,
   entry_suburb varchar(32),
   entry_postcode varchar(10) NOT NULL,
   entry_city varchar(32) NOT NULL,
   entry_state varchar(32),
   entry_country_id int DEFAULT '0' NOT NULL,
   entry_zone_id int DEFAULT '0' NOT NULL,
   PRIMARY KEY (address_book_id),
   KEY idx_address_book_customers_id (customers_id)
);

DROP TABLE IF EXISTS address_format;
CREATE TABLE address_format (
  address_format_id int NOT NULL auto_increment,
  address_format varchar(128) NOT NULL,
  address_summary varchar(48) NOT NULL,
  PRIMARY KEY (address_format_id)
);

DROP TABLE IF EXISTS banners;
CREATE TABLE banners (
  banners_id int NOT NULL auto_increment,
  banners_title varchar(64) NOT NULL,
  banners_url varchar(255) NOT NULL,
  banners_image varchar(64) NOT NULL,
  banners_group varchar(10) NOT NULL,
  banners_html_text text,
  expires_impressions int(7) DEFAULT '0',
  expires_date datetime DEFAULT NULL,
  date_scheduled datetime DEFAULT NULL,
  date_added datetime NOT NULL,
  date_status_change datetime DEFAULT NULL,
  status int(1) DEFAULT '1' NOT NULL,
  PRIMARY KEY  (banners_id)
);

DROP TABLE IF EXISTS banners_history;
CREATE TABLE banners_history (
  banners_history_id int NOT NULL auto_increment,
  banners_id int NOT NULL,
  banners_shown int(5) NOT NULL DEFAULT '0',
  banners_clicked int(5) NOT NULL DEFAULT '0',
  banners_history_date datetime NOT NULL,
  PRIMARY KEY  (banners_history_id)
);

DROP TABLE IF EXISTS categories;
CREATE TABLE categories (
   categories_id int NOT NULL auto_increment,
   categories_image varchar(64),
   parent_id int DEFAULT '0' NOT NULL,
   sort_order int(3),
   date_added datetime,
   last_modified datetime,
   PRIMARY KEY (categories_id),
   KEY idx_categories_parent_id (parent_id)
);

DROP TABLE IF EXISTS categories_description;
CREATE TABLE categories_description (
   categories_id int DEFAULT '0' NOT NULL,
   language_id int DEFAULT '1' NOT NULL,
   categories_name varchar(32) NOT NULL,
   PRIMARY KEY (categories_id, language_id),
   KEY idx_categories_name (categories_name)
);

DROP TABLE IF EXISTS configuration;
CREATE TABLE configuration (
  configuration_id int NOT NULL auto_increment,
  configuration_title varchar(64) NOT NULL,
  configuration_key varchar(64) NOT NULL,
  configuration_value varchar(255) NOT NULL,
  configuration_description varchar(255) NOT NULL,
  configuration_group_id int NOT NULL,
  sort_order int(5) NULL,
  last_modified datetime NULL,
  date_added datetime NOT NULL,
  use_function varchar(255) NULL,
  set_function varchar(255) NULL,
  PRIMARY KEY (configuration_id)
);

DROP TABLE IF EXISTS configuration_group;
CREATE TABLE configuration_group (
  configuration_group_id int NOT NULL auto_increment,
  configuration_group_title varchar(64) NOT NULL,
  configuration_group_description varchar(255) NOT NULL,
  sort_order int(5) NULL,
  visible int(1) DEFAULT '1' NULL,
  PRIMARY KEY (configuration_group_id)
);

DROP TABLE IF EXISTS counter;
CREATE TABLE counter (
  startdate char(8),
  counter int(12)
);

DROP TABLE IF EXISTS counter_history;
CREATE TABLE counter_history (
  month char(8),
  counter int(12)
);

DROP TABLE IF EXISTS countries;
CREATE TABLE countries (
  countries_id int NOT NULL auto_increment,
  countries_name varchar(64) NOT NULL,
  countries_iso_code_2 char(2) NOT NULL,
  countries_iso_code_3 char(3) NOT NULL,
  address_format_id int NOT NULL,
  PRIMARY KEY (countries_id),
  KEY IDX_COUNTRIES_NAME (countries_name)
);

DROP TABLE IF EXISTS currencies;
CREATE TABLE currencies (
  currencies_id int NOT NULL auto_increment,
  title varchar(32) NOT NULL,
  code char(3) NOT NULL,
  symbol_left varchar(12),
  symbol_right varchar(12),
  decimal_point char(1),
  thousands_point char(1),
  decimal_places char(1),
  value float(13,8),
  last_updated datetime NULL,
  PRIMARY KEY (currencies_id)
);

DROP TABLE IF EXISTS customers;
CREATE TABLE customers (
   customers_id int NOT NULL auto_increment,
   customers_gender char(1) NOT NULL,
   customers_firstname varchar(32) NOT NULL,
   customers_lastname varchar(32) NOT NULL,
   customers_dob datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
   customers_email_address varchar(96) NOT NULL,
   customers_default_address_id int,
   customers_telephone varchar(32) NOT NULL,
   customers_fax varchar(32),
   customers_password varchar(40) NOT NULL,
   customers_newsletter char(1),
   PRIMARY KEY (customers_id)
);

DROP TABLE IF EXISTS customers_basket;
CREATE TABLE customers_basket (
  customers_basket_id int NOT NULL auto_increment,
  customers_id int NOT NULL,
  products_id tinytext NOT NULL,
  customers_basket_quantity int(2) NOT NULL,
  final_price decimal(15,4),
  customers_basket_date_added char(8),
  PRIMARY KEY (customers_basket_id)
);

DROP TABLE IF EXISTS customers_basket_attributes;
CREATE TABLE customers_basket_attributes (
  customers_basket_attributes_id int NOT NULL auto_increment,
  customers_id int NOT NULL,
  products_id tinytext NOT NULL,
  products_options_id int NOT NULL,
  products_options_value_id int NOT NULL,
  PRIMARY KEY (customers_basket_attributes_id)
);

DROP TABLE IF EXISTS customers_info;
CREATE TABLE customers_info (
  customers_info_id int NOT NULL,
  customers_info_date_of_last_logon datetime,
  customers_info_number_of_logons int(5),
  customers_info_date_account_created datetime,
  customers_info_date_account_last_modified datetime,
  global_product_notifications int(1) DEFAULT '0',
  PRIMARY KEY (customers_info_id)
);

DROP TABLE IF EXISTS languages;
CREATE TABLE languages (
  languages_id int NOT NULL auto_increment,
  name varchar(32)  NOT NULL,
  code char(2) NOT NULL,
  image varchar(64),
  directory varchar(32),
  sort_order int(3),
  PRIMARY KEY (languages_id),
  KEY IDX_LANGUAGES_NAME (name)
);


DROP TABLE IF EXISTS manufacturers;
CREATE TABLE manufacturers (
  manufacturers_id int NOT NULL auto_increment,
  manufacturers_name varchar(32) NOT NULL,
  manufacturers_image varchar(64),
  date_added datetime NULL,
  last_modified datetime NULL,
  PRIMARY KEY (manufacturers_id),
  KEY IDX_MANUFACTURERS_NAME (manufacturers_name)
);

DROP TABLE IF EXISTS manufacturers_info;
CREATE TABLE manufacturers_info (
  manufacturers_id int NOT NULL,
  languages_id int NOT NULL,
  manufacturers_url varchar(255) NOT NULL,
  url_clicked int(5) NOT NULL default '0',
  date_last_click datetime NULL,
  PRIMARY KEY (manufacturers_id, languages_id)
);

DROP TABLE IF EXISTS newsletters;
CREATE TABLE newsletters (
  newsletters_id int NOT NULL auto_increment,
  title varchar(255) NOT NULL,
  content text NOT NULL,
  module varchar(255) NOT NULL,
  date_added datetime NOT NULL,
  date_sent datetime,
  status int(1),
  locked int(1) DEFAULT '0',
  PRIMARY KEY (newsletters_id)
);

DROP TABLE IF EXISTS orders;
CREATE TABLE orders (
  orders_id int NOT NULL auto_increment,
  customers_id int NOT NULL,
  customers_name varchar(64) NOT NULL,
  customers_company varchar(32),
  customers_street_address varchar(64) NOT NULL,
  customers_suburb varchar(32),
  customers_city varchar(32) NOT NULL,
  customers_postcode varchar(10) NOT NULL,
  customers_state varchar(32),
  customers_country varchar(32) NOT NULL,
  customers_telephone varchar(32) NOT NULL,
  customers_email_address varchar(96) NOT NULL,
  customers_address_format_id int(5) NOT NULL,
  delivery_name varchar(64) NOT NULL,
  delivery_company varchar(32),
  delivery_street_address varchar(64) NOT NULL,
  delivery_suburb varchar(32),
  delivery_city varchar(32) NOT NULL,
  delivery_postcode varchar(10) NOT NULL,
  delivery_state varchar(32),
  delivery_country varchar(32) NOT NULL,
  delivery_address_format_id int(5) NOT NULL,
  billing_name varchar(64) NOT NULL,
  billing_company varchar(32),
  billing_street_address varchar(64) NOT NULL,
  billing_suburb varchar(32),
  billing_city varchar(32) NOT NULL,
  billing_postcode varchar(10) NOT NULL,
  billing_state varchar(32),
  billing_country varchar(32) NOT NULL,
  billing_address_format_id int(5) NOT NULL,
  payment_method varchar(32) NOT NULL,
  cc_type varchar(20),
  cc_owner varchar(64),
  cc_number varchar(32),
  cc_expires varchar(4),
  last_modified datetime,
  date_purchased datetime,
  orders_status int(5) NOT NULL,
  orders_date_finished datetime,
  currency char(3),
  currency_value decimal(14,6),
  PRIMARY KEY (orders_id)
);

DROP TABLE IF EXISTS orders_products;
CREATE TABLE orders_products (
  orders_products_id int NOT NULL auto_increment,
  orders_id int NOT NULL,
  products_id int NOT NULL,
  products_model varchar(12),
  products_name varchar(64) NOT NULL,
  products_price decimal(15,4) NOT NULL,
  final_price decimal(15,4) NOT NULL,
  products_tax decimal(7,4) NOT NULL,
  products_quantity int(2) NOT NULL,
  PRIMARY KEY (orders_products_id)
);

DROP TABLE IF EXISTS orders_status;
CREATE TABLE orders_status (
   orders_status_id int DEFAULT '0' NOT NULL,
   language_id int DEFAULT '1' NOT NULL,
   orders_status_name varchar(32) NOT NULL,
   PRIMARY KEY (orders_status_id, language_id),
   KEY idx_orders_status_name (orders_status_name)
);

DROP TABLE IF EXISTS orders_status_history;
CREATE TABLE orders_status_history (
   orders_status_history_id int NOT NULL auto_increment,
   orders_id int NOT NULL,
   orders_status_id int(5) NOT NULL,
   date_added datetime NOT NULL,
   customer_notified int(1) DEFAULT '0',
   comments text,
   PRIMARY KEY (orders_status_history_id)
);

DROP TABLE IF EXISTS orders_products_attributes;
CREATE TABLE orders_products_attributes (
  orders_products_attributes_id int NOT NULL auto_increment,
  orders_id int NOT NULL,
  orders_products_id int NOT NULL,
  products_options varchar(32) NOT NULL,
  products_options_values varchar(32) NOT NULL,
  options_values_price decimal(15,4) NOT NULL,
  price_prefix char(1) NOT NULL,
  PRIMARY KEY (orders_products_attributes_id)
);

DROP TABLE IF EXISTS orders_products_download;
CREATE TABLE orders_products_download (
  orders_products_download_id int NOT NULL auto_increment,
  orders_id int NOT NULL default '0',
  orders_products_id int NOT NULL default '0',
  orders_products_filename varchar(255) NOT NULL default '',
  download_maxdays int(2) NOT NULL default '0',
  download_count int(2) NOT NULL default '0',
  PRIMARY KEY  (orders_products_download_id)
);

DROP TABLE IF EXISTS orders_total;
CREATE TABLE orders_total (
  orders_total_id int unsigned NOT NULL auto_increment,
  orders_id int NOT NULL,
  title varchar(255) NOT NULL,
  text varchar(255) NOT NULL,
  value decimal(15,4) NOT NULL,
  class varchar(32) NOT NULL,
  sort_order int NOT NULL,
  PRIMARY KEY (orders_total_id),
  KEY idx_orders_total_orders_id (orders_id)
);

DROP TABLE IF EXISTS products;
CREATE TABLE products (
  products_id int NOT NULL auto_increment,
  products_quantity int(4) NOT NULL,
  products_model varchar(12),
  products_image varchar(64),
  products_price decimal(15,4) NOT NULL,
  products_date_added datetime NOT NULL,
  products_last_modified datetime,
  products_date_available datetime,
  products_weight decimal(5,2) NOT NULL,
  products_status tinyint(1) NOT NULL,
  products_tax_class_id int NOT NULL,
  manufacturers_id int NULL,
  products_ordered int NOT NULL default '0',
  PRIMARY KEY (products_id),
  KEY idx_products_date_added (products_date_added)
);

DROP TABLE IF EXISTS products_attributes;
CREATE TABLE products_attributes (
  products_attributes_id int NOT NULL auto_increment,
  products_id int NOT NULL,
  options_id int NOT NULL,
  options_values_id int NOT NULL,
  options_values_price decimal(15,4) NOT NULL,
  price_prefix char(1) NOT NULL,
  PRIMARY KEY (products_attributes_id)
);

DROP TABLE IF EXISTS products_attributes_download;
CREATE TABLE products_attributes_download (
  products_attributes_id int NOT NULL,
  products_attributes_filename varchar(255) NOT NULL default '',
  products_attributes_maxdays int(2) default '0',
  products_attributes_maxcount int(2) default '0',
  PRIMARY KEY  (products_attributes_id)
);

DROP TABLE IF EXISTS products_description;
CREATE TABLE products_description (
  products_id int NOT NULL auto_increment,
  language_id int NOT NULL default '1',
  products_name varchar(64) NOT NULL default '',
  products_description text,
  products_url varchar(255) default NULL,
  products_viewed int(5) default '0',
  PRIMARY KEY  (products_id,language_id),
  KEY products_name (products_name)
);

DROP TABLE IF EXISTS products_notifications;
CREATE TABLE products_notifications (
  products_id int NOT NULL,
  customers_id int NOT NULL,
  date_added datetime NOT NULL,
  PRIMARY KEY (products_id, customers_id)
);

DROP TABLE IF EXISTS products_options;
CREATE TABLE products_options (
  products_options_id int NOT NULL default '0',
  language_id int NOT NULL default '1',
  products_options_name varchar(32) NOT NULL default '',
  PRIMARY KEY  (products_options_id,language_id)
);

DROP TABLE IF EXISTS products_options_values;
CREATE TABLE products_options_values (
  products_options_values_id int NOT NULL default '0',
  language_id int NOT NULL default '1',
  products_options_values_name varchar(64) NOT NULL default '',
  PRIMARY KEY  (products_options_values_id,language_id)
);

DROP TABLE IF EXISTS products_options_values_to_products_options;
CREATE TABLE products_options_values_to_products_options (
  products_options_values_to_products_options_id int NOT NULL auto_increment,
  products_options_id int NOT NULL,
  products_options_values_id int NOT NULL,
  PRIMARY KEY (products_options_values_to_products_options_id)
);

DROP TABLE IF EXISTS products_to_categories;
CREATE TABLE products_to_categories (
  products_id int NOT NULL,
  categories_id int NOT NULL,
  PRIMARY KEY (products_id,categories_id)
);

DROP TABLE IF EXISTS reviews;
CREATE TABLE reviews (
  reviews_id int NOT NULL auto_increment,
  products_id int NOT NULL,
  customers_id int,
  customers_name varchar(64) NOT NULL,
  reviews_rating int(1),
  date_added datetime,
  last_modified datetime,
  reviews_read int(5) NOT NULL default '0',
  PRIMARY KEY (reviews_id)
);

DROP TABLE IF EXISTS reviews_description;
CREATE TABLE reviews_description (
  reviews_id int NOT NULL,
  languages_id int NOT NULL,
  reviews_text text NOT NULL,
  PRIMARY KEY (reviews_id, languages_id)
);

DROP TABLE IF EXISTS sessions;
CREATE TABLE sessions (
  sesskey varchar(32) NOT NULL,
  expiry int(11) unsigned NOT NULL,
  value text NOT NULL,
  PRIMARY KEY (sesskey)
);

DROP TABLE IF EXISTS specials;
CREATE TABLE specials (
  specials_id int NOT NULL auto_increment,
  products_id int NOT NULL,
  specials_new_products_price decimal(15,4) NOT NULL,
  specials_date_added datetime,
  specials_last_modified datetime,
  expires_date datetime,
  date_status_change datetime,
  status int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (specials_id)
);

DROP TABLE IF EXISTS tax_class;
CREATE TABLE tax_class (
  tax_class_id int NOT NULL auto_increment,
  tax_class_title varchar(32) NOT NULL,
  tax_class_description varchar(255) NOT NULL,
  last_modified datetime NULL,
  date_added datetime NOT NULL,
  PRIMARY KEY (tax_class_id)
);

DROP TABLE IF EXISTS tax_rates;
CREATE TABLE tax_rates (
  tax_rates_id int NOT NULL auto_increment,
  tax_zone_id int NOT NULL,
  tax_class_id int NOT NULL,
  tax_priority int(5) DEFAULT 1,
  tax_rate decimal(7,4) NOT NULL,
  tax_description varchar(255) NOT NULL,
  last_modified datetime NULL,
  date_added datetime NOT NULL,
  PRIMARY KEY (tax_rates_id)
);

DROP TABLE IF EXISTS geo_zones;
CREATE TABLE geo_zones (
  geo_zone_id int NOT NULL auto_increment,
  geo_zone_name varchar(32) NOT NULL,
  geo_zone_description varchar(255) NOT NULL,
  last_modified datetime NULL,
  date_added datetime NOT NULL,
  PRIMARY KEY (geo_zone_id)
);

DROP TABLE IF EXISTS whos_online;
CREATE TABLE whos_online (
  customer_id int,
  full_name varchar(64) NOT NULL,
  session_id varchar(128) NOT NULL,
  ip_address varchar(15) NOT NULL,
  time_entry varchar(14) NOT NULL,
  time_last_click varchar(14) NOT NULL,
  last_page_url varchar(255) NOT NULL
);

DROP TABLE IF EXISTS zones;
CREATE TABLE zones (
  zone_id int NOT NULL auto_increment,
  zone_country_id int NOT NULL,
  zone_code varchar(32) NOT NULL,
  zone_name varchar(32) NOT NULL,
  PRIMARY KEY (zone_id)
);

DROP TABLE IF EXISTS zones_to_geo_zones;
CREATE TABLE zones_to_geo_zones (
   association_id int NOT NULL auto_increment,
   zone_country_id int NOT NULL,
   zone_id int NULL,
   geo_zone_id int NULL,
   last_modified datetime NULL,
   date_added datetime NOT NULL,
   PRIMARY KEY (association_id)
);


# data

INSERT INTO address_book VALUES ( '1', '1', 'm', 'ACME Inc.', 'John', 'Doe', '1 Way Street', '', '12345', 'NeverNever', '', '223', '12');

# 1 - Default, 2 - USA, 3 - Spain, 4 - Singapore, 5 - Germany
INSERT INTO address_format VALUES (1, '$firstname $lastname$cr$streets$cr$city, $postcode$cr$statecomma$country','$city / $country');
INSERT INTO address_format VALUES (2, '$firstname $lastname$cr$streets$cr$city, $state    $postcode$cr$country','$city, $state / $country');
INSERT INTO address_format VALUES (3, '$firstname $lastname$cr$streets$cr$city$cr$postcode - $statecomma$country','$state / $country');
INSERT INTO address_format VALUES (4, '$firstname $lastname$cr$streets$cr$city ($postcode)$cr$country', '$postcode / $country');
INSERT INTO address_format VALUES (5, '$firstname $lastname$cr$streets$cr$postcode $city$cr$country','$city / $country');

INSERT INTO banners VALUES (1, 'osCommerce', 'http://www.oscommerce.com', 'banners/oscommerce.gif', '468x50', '', 0, null, null, now(), null, 1);

INSERT INTO categories VALUES ('1', 'category_hardware.gif', '0', '1', now(), null);
INSERT INTO categories VALUES ('2', 'category_software.gif', '0', '2', now(), null);
INSERT INTO categories VALUES ('3', 'category_dvd_movies.gif', '0', '3', now(), null);
INSERT INTO categories VALUES ('4', 'subcategory_graphic_cards.gif', '1', '0', now(), null);
INSERT INTO categories VALUES ('5', 'subcategory_printers.gif', '1', '0', now(), null);
INSERT INTO categories VALUES ('6', 'subcategory_monitors.gif', '1', '0', now(), null);
INSERT INTO categories VALUES ('7', 'subcategory_speakers.gif', '1', '0', now(), null);
INSERT INTO categories VALUES ('8', 'subcategory_keyboards.gif', '1', '0', now(), null);
INSERT INTO categories VALUES ('9', 'subcategory_mice.gif', '1', '0', now(), null);
INSERT INTO categories VALUES ('10', 'subcategory_action.gif', '3', '0', now(), null);
INSERT INTO categories VALUES ('11', 'subcategory_science_fiction.gif', '3', '0', now(), null);
INSERT INTO categories VALUES ('12', 'subcategory_comedy.gif', '3', '0', now(), null);
INSERT INTO categories VALUES ('13', 'subcategory_cartoons.gif', '3', '0', now(), null);
INSERT INTO categories VALUES ('14', 'subcategory_thriller.gif', '3', '0', now(), null);
INSERT INTO categories VALUES ('15', 'subcategory_drama.gif', '3', '0', now(), null);
INSERT INTO categories VALUES ('16', 'subcategory_memory.gif', '1', '0', now(), null);
INSERT INTO categories VALUES ('17', 'subcategory_cdrom_drives.gif', '1', '0', now(), null);
INSERT INTO categories VALUES ('18', 'subcategory_simulation.gif', '2', '0', now(), null);
INSERT INTO categories VALUES ('19', 'subcategory_action_games.gif', '2', '0', now(), null);
INSERT INTO categories VALUES ('20', 'subcategory_strategy.gif', '2', '0', now(), null);

INSERT INTO categories_description VALUES ( '1', '1', 'Hardware');
INSERT INTO categories_description VALUES ( '2', '1', 'Software');
INSERT INTO categories_description VALUES ( '3', '1', 'DVD Movies');
INSERT INTO categories_description VALUES ( '4', '1', 'Graphics Cards');
INSERT INTO categories_description VALUES ( '5', '1', 'Printers');
INSERT INTO categories_description VALUES ( '6', '1', 'Monitors');
INSERT INTO categories_description VALUES ( '7', '1', 'Speakers');
INSERT INTO categories_description VALUES ( '8', '1', 'Keyboards');
INSERT INTO categories_description VALUES ( '9', '1', 'Mice');
INSERT INTO categories_description VALUES ( '10', '1', 'Action');
INSERT INTO categories_description VALUES ( '11', '1', 'Science Fiction');
INSERT INTO categories_description VALUES ( '12', '1', 'Comedy');
INSERT INTO categories_description VALUES ( '13', '1', 'Cartoons');
INSERT INTO categories_description VALUES ( '14', '1', 'Thriller');
INSERT INTO categories_description VALUES ( '15', '1', 'Drama');
INSERT INTO categories_description VALUES ( '16', '1', 'Memory');
INSERT INTO categories_description VALUES ( '17', '1', 'CDROM Drives');
INSERT INTO categories_description VALUES ( '18', '1', 'Simulation');
INSERT INTO categories_description VALUES ( '19', '1', 'Action');
INSERT INTO categories_description VALUES ( '20', '1', 'Strategy');
INSERT INTO categories_description VALUES ( '1', '2', 'Hardware');
INSERT INTO categories_description VALUES ( '2', '2', 'Software');
INSERT INTO categories_description VALUES ( '3', '2', 'DVD Filme');
INSERT INTO categories_description VALUES ( '4', '2', 'Grafikkarten');
INSERT INTO categories_description VALUES ( '5', '2', 'Drucker');
INSERT INTO categories_description VALUES ( '6', '2', 'Bildschirme');
INSERT INTO categories_description VALUES ( '7', '2', 'Lautsprecher');
INSERT INTO categories_description VALUES ( '8', '2', 'Tastaturen');
INSERT INTO categories_description VALUES ( '9', '2', 'Mäuse');
INSERT INTO categories_description VALUES ( '10', '2', 'Action');
INSERT INTO categories_description VALUES ( '11', '2', 'Science Fiction');
INSERT INTO categories_description VALUES ( '12', '2', 'Komödie');
INSERT INTO categories_description VALUES ( '13', '2', 'Zeichentrick');
INSERT INTO categories_description VALUES ( '14', '2', 'Thriller');
INSERT INTO categories_description VALUES ( '15', '2', 'Drama');
INSERT INTO categories_description VALUES ( '16', '2', 'Speicher');
INSERT INTO categories_description VALUES ( '17', '2', 'CDROM Laufwerke');
INSERT INTO categories_description VALUES ( '18', '2', 'Simulation');
INSERT INTO categories_description VALUES ( '19', '2', 'Action');
INSERT INTO categories_description VALUES ( '20', '2', 'Strategie');
INSERT INTO categories_description VALUES ( '1', '3', 'Hardware');
INSERT INTO categories_description VALUES ( '2', '3', 'Software');
INSERT INTO categories_description VALUES ( '3', '3', 'Peliculas DVD');
INSERT INTO categories_description VALUES ( '4', '3', 'Tarjetas Graficas');
INSERT INTO categories_description VALUES ( '5', '3', 'Impresoras');
INSERT INTO categories_description VALUES ( '6', '3', 'Monitores');
INSERT INTO categories_description VALUES ( '7', '3', 'Altavoces');
INSERT INTO categories_description VALUES ( '8', '3', 'Teclados');
INSERT INTO categories_description VALUES ( '9', '3', 'Ratones');
INSERT INTO categories_description VALUES ( '10', '3', 'Accion');
INSERT INTO categories_description VALUES ( '11', '3', 'Ciencia Ficcion');
INSERT INTO categories_description VALUES ( '12', '3', 'Comedia');
INSERT INTO categories_description VALUES ( '13', '3', 'Dibujos Animados');
INSERT INTO categories_description VALUES ( '14', '3', 'Suspense');
INSERT INTO categories_description VALUES ( '15', '3', 'Drama');
INSERT INTO categories_description VALUES ( '16', '3', 'Memoria');
INSERT INTO categories_description VALUES ( '17', '3', 'Unidades CDROM');
INSERT INTO categories_description VALUES ( '18', '3', 'Simulacion');
INSERT INTO categories_description VALUES ( '19', '3', 'Accion');
INSERT INTO categories_description VALUES ( '20', '3', 'Estrategia');

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Store Name', 'STORE_NAME', 'osCommerce', 'The name of my store', '1', '1', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Store Owner', 'STORE_OWNER', 'Harald Ponce de Leon', 'The name of my store owner', '1', '2', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('E-Mail Address', 'STORE_OWNER_EMAIL_ADDRESS', 'root@localhost', 'The e-mail address of my store owner', '1', '3', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('E-Mail From', 'EMAIL_FROM', 'osCommerce <root@localhost>', 'The e-mail address used in (sent) e-mails', '1', '4', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Country', 'STORE_COUNTRY', '223', 'The country my store is located in <br><br><b>Note: Please remember to update the store zone.</b>', '1', '6', 'tep_get_country_name', 'tep_cfg_pull_down_country_list(', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Zone', 'STORE_ZONE', '18', 'The zone my store is located in', '1', '7', 'tep_cfg_get_zone_name', 'tep_cfg_pull_down_zone_list(', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Expected Sort Order', 'EXPECTED_PRODUCTS_SORT', 'desc', 'This is the sort order used in the expected products box.', '1', '8', 'tep_cfg_select_option(array(\'asc\', \'desc\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Expected Sort Field', 'EXPECTED_PRODUCTS_FIELD', 'date_expected', 'The column to sort by in the expected products box.', '1', '9', 'tep_cfg_select_option(array(\'products_name\', \'date_expected\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Switch To Default Language Currency', 'USE_DEFAULT_LANGUAGE_CURRENCY', 'false', 'Automatically switch to the language\'s currency when it is changed', '1', '10', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Send Extra Order Emails To', 'SEND_EXTRA_ORDER_EMAILS_TO', '', 'Send extra order emails to the following email addresses, in this format: Name 1 &lt;email@address1&gt;, Name 2 &lt;email@address2&gt;', '1', '11', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Use Search-Engine Safe URLs (still in development)', 'SEARCH_ENGINE_FRIENDLY_URLS', 'false', 'Use search-engine safe urls for all site links', '1', '12', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Cart After Adding Product', 'DISPLAY_CART', 'true', 'Display the shopping cart after adding a product (or return back to their origin)', '1', '14', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Allow Guest To Tell A Friend', 'ALLOW_GUEST_TO_TELL_A_FRIEND', 'false', 'Allow guests to tell a friend about a product', '1', '15', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Default Search Operator', 'ADVANCED_SEARCH_DEFAULT_OPERATOR', 'and', 'Default search operators', '1', '17', 'tep_cfg_select_option(array(\'and\', \'or\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Store Address and Phone', 'STORE_NAME_ADDRESS', 'Store Name\nAddress\nCountry\nPhone', 'This is the Store Name, Address and Phone used on printable documents and displayed online', '1', '18', 'tep_cfg_textarea(', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Category Counts', 'SHOW_COUNTS', 'true', 'Count recursively how many products are in each category', '1', '19', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Tax Decimal Places', 'TAX_DECIMAL_PLACES', '0', 'Pad the tax value this amount of decimal places', '1', '20', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Prices with Tax', 'DISPLAY_PRICE_WITH_TAX', 'false', 'Display prices with tax included (true) or add the tax at the end (false)', '1', '21', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('First Name', 'ENTRY_FIRST_NAME_MIN_LENGTH', '2', 'Minimum length of first name', '2', '1', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Last Name', 'ENTRY_LAST_NAME_MIN_LENGTH', '2', 'Minimum length of last name', '2', '2', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Date of Birth', 'ENTRY_DOB_MIN_LENGTH', '10', 'Minimum length of date of birth', '2', '3', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('E-Mail Address', 'ENTRY_EMAIL_ADDRESS_MIN_LENGTH', '6', 'Minimum length of e-mail address', '2', '4', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Street Address', 'ENTRY_STREET_ADDRESS_MIN_LENGTH', '5', 'Minimum length of street address', '2', '5', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Company', 'ENTRY_COMPANY_MIN_LENGTH', '2', 'Minimum length of company name', '2', '6', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Post Code', 'ENTRY_POSTCODE_MIN_LENGTH', '4', 'Minimum length of post code', '2', '7', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('City', 'ENTRY_CITY_MIN_LENGTH', '3', 'Minimum length of city', '2', '8', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('State', 'ENTRY_STATE_MIN_LENGTH', '2', 'Minimum length of state', '2', '9', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Telephone Number', 'ENTRY_TELEPHONE_MIN_LENGTH', '3', 'Minimum length of telephone number', '2', '10', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Password', 'ENTRY_PASSWORD_MIN_LENGTH', '5', 'Minimum length of password', '2', '11', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Credit Card Owner Name', 'CC_OWNER_MIN_LENGTH', '3', 'Minimum length of credit card owner name', '2', '12', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Credit Card Number', 'CC_NUMBER_MIN_LENGTH', '10', 'Minimum length of credit card number', '2', '13', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Review Text', 'REVIEW_TEXT_MIN_LENGTH', '50', 'Minimum length of review text', '2', '14', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Best Sellers', 'MIN_DISPLAY_BESTSELLERS', '1', 'Minimum number of best sellers to display', '2', '15', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Also Purchased', 'MIN_DISPLAY_ALSO_PURCHASED', '1', 'Minimum number of products to display in the \'This Customer Also Purchased\' box', '2', '16', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Address Book Entries', 'MAX_ADDRESS_BOOK_ENTRIES', '5', 'Maximum address book entries a customer is allowed to have', '3', '1', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Search Results', 'MAX_DISPLAY_SEARCH_RESULTS', '20', 'Amount of products to list', '3', '2', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Page Links', 'MAX_DISPLAY_PAGE_LINKS', '5', 'Number of \'number\' links use for page-sets', '3', '3', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Special Products', 'MAX_DISPLAY_SPECIAL_PRODUCTS', '9', 'Maximum number of products on special to display', '3', '4', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('New Products Module', 'MAX_DISPLAY_NEW_PRODUCTS', '9', 'Maximum number of new products to display in a category', '3', '5', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Products Expected', 'MAX_DISPLAY_UPCOMING_PRODUCTS', '10', 'Maximum number of products expected to display', '3', '6', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Manufacturers List', 'MAX_DISPLAY_MANUFACTURERS_IN_A_LIST', '0', 'Used in manufacturers box; when the number of manufacturers exceeds this number, a drop-down list will be displayed instead of the default list', '3', '7', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Manufacturers Select Size', 'MAX_MANUFACTURERS_LIST', '1', 'Used in manufacturers box; when this value is \'1\' the classic drop-down list will be used for the manufacturers box. Otherwise, a list-box with the specified number of rows will be displayed.', '3', '7', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Length of Manufacturers Name', 'MAX_DISPLAY_MANUFACTURER_NAME_LEN', '15', 'Used in manufacturers box; maximum length of manufacturers name to display', '3', '8', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('New Reviews', 'MAX_DISPLAY_NEW_REVIEWS', '6', 'Maximum number of new reviews to display', '3', '9', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Selection of Random Reviews', 'MAX_RANDOM_SELECT_REVIEWS', '10', 'How many records to select from to choose one random product review', '3', '10', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Selection of Random New Products', 'MAX_RANDOM_SELECT_NEW', '10', 'How many records to select from to choose one random new product to display', '3', '11', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Selection of Products on Special', 'MAX_RANDOM_SELECT_SPECIALS', '10', 'How many records to select from to choose one random product special to display', '3', '12', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Categories To List Per Row', 'MAX_DISPLAY_CATEGORIES_PER_ROW', '3', 'How many categories to list per row', '3', '13', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('New Products Listing', 'MAX_DISPLAY_PRODUCTS_NEW', '10', 'Maximum number of new products to display in new products page', '3', '14', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Best Sellers', 'MAX_DISPLAY_BESTSELLERS', '10', 'Maximum number of best sellers to display', '3', '15', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Also Purchased', 'MAX_DISPLAY_ALSO_PURCHASED', '6', 'Maximum number of products to display in the \'This Customer Also Purchased\' box', '3', '16', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Customer Order History Box', 'MAX_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX', '6', 'Maximum number of products to display in the customer order history box', '3', '17', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Order History', 'MAX_DISPLAY_ORDER_HISTORY', '10', 'Maximum number of orders to display in the order history page', '3', '18', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Small Image Width', 'SMALL_IMAGE_WIDTH', '100', 'The pixel width of small images', '4', '1', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Small Image Height', 'SMALL_IMAGE_HEIGHT', '80', 'The pixel height of small images', '4', '2', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Heading Image Width', 'HEADING_IMAGE_WIDTH', '57', 'The pixel width of heading images', '4', '3', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Heading Image Height', 'HEADING_IMAGE_HEIGHT', '40', 'The pixel height of heading images', '4', '4', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Subcategory Image Width', 'SUBCATEGORY_IMAGE_WIDTH', '100', 'The pixel width of subcategory images', '4', '5', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Subcategory Image Height', 'SUBCATEGORY_IMAGE_HEIGHT', '57', 'The pixel height of subcategory images', '4', '6', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Calculate Image Size', 'CONFIG_CALCULATE_IMAGE_SIZE', 'true', 'Calculate the size of images?', '4', '7', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Image Required', 'IMAGE_REQUIRED', 'true', 'Enable to display broken images. Good for development.', '4', '8', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Gender', 'ACCOUNT_GENDER', 'true', 'Display gender in the customers account', '5', '1', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Date of Birth', 'ACCOUNT_DOB', 'true', 'Display date of birth in the customers account', '5', '2', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Company', 'ACCOUNT_COMPANY', 'true', 'Display company in the customers account', '5', '3', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Suburb', 'ACCOUNT_SUBURB', 'true', 'Display suburb in the customers account', '5', '4', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('State', 'ACCOUNT_STATE', 'true', 'Display state in the customers account', '5', '5', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Installed Modules', 'MODULE_PAYMENT_INSTALLED', 'cc.php;cod.php', 'List of payment module filenames separated by a semi-colon. This is automatically updated. No need to edit. (Example: cc.php;cod.php;paypal.php)', '6', '0', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Installed Modules', 'MODULE_ORDER_TOTAL_INSTALLED', 'ot_subtotal.php;ot_tax.php;ot_shipping.php;ot_total.php', 'List of order_total module filenames separated by a semi-colon. This is automatically updated. No need to edit. (Example: ot_subtotal.php;ot_tax.php;ot_shipping.php;ot_total.php)', '6', '0', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Installed Modules', 'MODULE_SHIPPING_INSTALLED', 'flat.php', 'List of shipping module filenames separated by a semi-colon. This is automatically updated. No need to edit. (Example: ups.php;flat.php;item.php)', '6', '0', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Cash On Delivery Module', 'MODULE_PAYMENT_COD_STATUS', 'True', 'Do you want to accept Cash On Delevery payments?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Payment Zone', 'MODULE_PAYMENT_COD_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort order of display.', 'MODULE_PAYMENT_COD_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Order Status', 'MODULE_PAYMENT_COD_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Credit Card Module', 'MODULE_PAYMENT_CC_STATUS', 'True', 'Do you want to accept credit card payments?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Split Credit Card E-Mail Address', 'MODULE_PAYMENT_CC_EMAIL', '', 'If an e-mail address is entered, the middle digits of the credit card number will be sent to the e-mail address (the outside digits are stored in the database with the middle digits censored)', '6', '0', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort order of display.', 'MODULE_PAYMENT_CC_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0' , now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Payment Zone', 'MODULE_PAYMENT_CC_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Order Status', 'MODULE_PAYMENT_CC_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Flat Shipping', 'MODULE_SHIPPING_FLAT_STATUS', 'True', 'Do you want to offer flat rate shipping?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Shipping Cost', 'MODULE_SHIPPING_FLAT_COST', '5.00', 'The shipping cost for all orders using this shipping method.', '6', '0', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Tax Class', 'MODULE_SHIPPING_FLAT_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'tep_get_tax_class_title', 'tep_cfg_pull_down_tax_classes(', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Shipping Zone', 'MODULE_SHIPPING_FLAT_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '0', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort Order', 'MODULE_SHIPPING_FLAT_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Default Currency', 'DEFAULT_CURRENCY', 'USD', 'Default Currency', '6', '0', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Default Language', 'DEFAULT_LANGUAGE', 'en', 'Default Language', '6', '0', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Default Order Status For New Orders', 'DEFAULT_ORDERS_STATUS_ID', '1', 'When a new order is created, this order status will be assigned to it.', '6', '0', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Shipping', 'MODULE_ORDER_TOTAL_SHIPPING_STATUS', 'true', 'Do you want to display the order shipping cost?', '6', '1','tep_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort Order', 'MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER', '2', 'Sort order of display.', '6', '2', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Allow Free Shipping', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING', 'false', 'Do you want to allow free shipping?', '6', '3', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added) VALUES ('Free Shipping For Orders Over', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER', '50', 'Provide free shipping for orders over the set amount.', '6', '4', 'currencies->format', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Provide Free Shipping For Orders Made', 'MODULE_ORDER_TOTAL_SHIPPING_DESTINATION', 'national', 'Provide free shipping for orders sent to the set destination.', '6', '5', 'tep_cfg_select_option(array(\'national\', \'international\', \'both\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Sub-Total', 'MODULE_ORDER_TOTAL_SUBTOTAL_STATUS', 'true', 'Do you want to display the order sub-total cost?', '6', '1','tep_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort Order', 'MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER', '1', 'Sort order of display.', '6', '2', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Tax', 'MODULE_ORDER_TOTAL_TAX_STATUS', 'true', 'Do you want to display the order tax value?', '6', '1','tep_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort Order', 'MODULE_ORDER_TOTAL_TAX_SORT_ORDER', '3', 'Sort order of display.', '6', '2', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Total', 'MODULE_ORDER_TOTAL_TOTAL_STATUS', 'true', 'Do you want to display the total order value?', '6', '1','tep_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort Order', 'MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER', '4', 'Sort order of display.', '6', '2', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Country of Origin', 'SHIPPING_ORIGIN_COUNTRY', '223', 'Select the country of origin to be used in shipping quotes.', '7', '1', 'tep_get_country_name', 'tep_cfg_pull_down_country_list(', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Postal Code', 'SHIPPING_ORIGIN_ZIP', 'NONE', 'Enter the Postal Code (ZIP) of the Store to be used in shipping quotes.', '7', '2', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Enter the Maximum Package Weight you will ship', 'SHIPPING_MAX_WEIGHT', '50', 'Carriers have a max weight limit for a single package. This is a common one for all.', '7', '3', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Package Tare weight.', 'SHIPPING_BOX_WEIGHT', '3', 'What is the weight of typical packaging of small to medium packages?', '7', '4', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Larger packages - percentage increase.', 'SHIPPING_BOX_PADDING', '10', 'For 10% enter 10', '7', '5', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Image', 'PRODUCT_LIST_IMAGE', '1', 'Do you want to display the Product Image?', '8', '1', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Manufaturer Name','PRODUCT_LIST_MANUFACTURER', '0', 'Do you want to display the Product Manufacturer Name?', '8', '2', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Model', 'PRODUCT_LIST_MODEL', '0', 'Do you want to display the Product Model?', '8', '3', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Name', 'PRODUCT_LIST_NAME', '2', 'Do you want to display the Product Name?', '8', '4', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Price', 'PRODUCT_LIST_PRICE', '3', 'Do you want to display the Product Price', '8', '5', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Quantity', 'PRODUCT_LIST_QUANTITY', '0', 'Do you want to display the Product Quantity?', '8', '6', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Weight', 'PRODUCT_LIST_WEIGHT', '0', 'Do you want to display the Product Weight?', '8', '7', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Buy Now column', 'PRODUCT_LIST_BUY_NOW', '4', 'Do you want to display the Buy Now column?', '8', '8', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Category/Manufacturer Filter (0=disable; 1=enable)', 'PRODUCT_LIST_FILTER', '1', 'Do you want to display the Category/Manufacturer Filter?', '8', '9', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Location of Prev/Next Navigation Bar (1-top, 2-bottom, 3-both)', 'PREV_NEXT_BAR_LOCATION', '2', 'Sets the location of the Prev/Next Navigation Bar (1-top, 2-bottom, 3-both)', '8', '10', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Check stock level', 'STOCK_CHECK', 'true', 'Check to see if sufficent stock is available', '9', '1', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Subtract stock', 'STOCK_LIMITED', 'true', 'Subtract product in stock by product orders', '9', '2', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Allow Checkout', 'STOCK_ALLOW_CHECKOUT', 'true', 'Allow customer to checkout even if there is insufficient stock', '9', '3', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Mark product out of stock', 'STOCK_MARK_PRODUCT_OUT_OF_STOCK', '***', 'Display something on screen so customer can see which product has insufficient stock', '9', '4', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Stock Re-order level', 'STOCK_REORDER_LEVEL', '5', 'Define when stock needs to be re-ordered', '9', '5', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Store Page Parse Time', 'STORE_PAGE_PARSE_TIME', 'false', 'Store the time it takes to parse a page', '10', '1', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Log Destination', 'STORE_PAGE_PARSE_TIME_LOG', '/var/log/www/tep/page_parse_time.log', 'Directory and filename of the page parse time log', '10', '2', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Log Date Format', 'STORE_PARSE_DATE_TIME_FORMAT', '%d/%m/%Y %H:%M:%S', 'The date format', '10', '3', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display The Page Parse Time', 'DISPLAY_PAGE_PARSE_TIME', 'true', 'Display the page parse time (store page parse time must be enabled)', '10', '4', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Store Database Queries', 'STORE_DB_TRANSACTIONS', 'false', 'Store the database queries in the page parse time log (PHP4 only)', '10', '5', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Use Cache', 'USE_CACHE', 'false', 'Use caching features', '11', '1', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Cache Directory', 'DIR_FS_CACHE', '/tmp/', 'The directory where the cached files are saved', '11', '2', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('E-Mail Transport Method', 'EMAIL_TRANSPORT', 'sendmail', 'Defines if this server uses a local connection to sendmail or uses an SMTP connection via TCP/IP. Servers running on Windows and MacOS should change this setting to SMTP.', '12', '1', 'tep_cfg_select_option(array(\'sendmail\', \'smtp\'),', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('E-Mail Linefeeds', 'EMAIL_LINEFEED', 'LF', 'Defines the character sequence used to separate mail headers.', '12', '2', 'tep_cfg_select_option(array(\'LF\', \'CRLF\'),', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Use MIME HTML When Sending Emails', 'EMAIL_USE_HTML', 'false', 'Send e-mails in HTML format', '12', '3', 'tep_cfg_select_option(array(\'true\', \'false\'),', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Verify E-Mail Addresses Through DNS', 'ENTRY_EMAIL_ADDRESS_CHECK', 'false', 'Verify e-mail address through a DNS server', '12', '4', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Send E-Mails', 'SEND_EMAILS', 'true', 'Send out e-mails', '12', '5', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable download', 'DOWNLOAD_ENABLED', 'false', 'Enable the products download functions.', '13', '1', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Download by redirect', 'DOWNLOAD_BY_REDIRECT', 'false', 'Use browser redirection for download. Disable on non-Unix systems.', '13', '2', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Expiry delay (days)' ,'DOWNLOAD_MAX_DAYS', '7', 'Set number of days before the download link expires. 0 means no limit.', '13', '3', '', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Maximum number of downloads' ,'DOWNLOAD_MAX_COUNT', '5', 'Set the maximum number of downloads. 0 means no download authorized.', '13', '4', '', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable GZip Compression', 'GZIP_COMPRESSION', 'false', 'Enable HTTP GZip compression.', '14', '1', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Compression Level', 'GZIP_LEVEL', '5', 'Use this compression level 0-9 (0 = minimum, 9 = maximum).', '14', '2', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Session Directory', 'SESSION_WRITE_DIRECTORY', '/tmp', 'If sessions are file based, store them in this directory.', '15', '1', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Force Cookie Use', 'SESSION_FORCE_COOKIE_USE', 'False', 'Force the use of sessions when cookies are only enabled.', '15', '2', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Check SSL Session ID', 'SESSION_CHECK_SSL_SESSION_ID', 'False', 'Validate the SSL_SESSION_ID on every secure HTTPS page request.', '15', '3', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Check User Agent', 'SESSION_CHECK_USER_AGENT', 'False', 'Validate the clients browser user agent on every page request.', '15', '4', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Check IP Address', 'SESSION_CHECK_IP_ADDRESS', 'False', 'Validate the clients IP address on every page request.', '15', '5', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Prevent Spider Sessions', 'SESSION_BLOCK_SPIDERS', 'False', 'Prevent known spiders from starting a session.', '15', '6', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Recreate Session', 'SESSION_RECREATE', 'False', 'Recreate the session to generate a new session ID when the customer logs on or creates an account (PHP >=4.1 needed).', '15', '7', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now());

INSERT INTO configuration_group VALUES ('1', 'My Store', 'General information about my store', '1', '1');
INSERT INTO configuration_group VALUES ('2', 'Minimum Values', 'The minimum values for functions / data', '2', '1');
INSERT INTO configuration_group VALUES ('3', 'Maximum Values', 'The maximum values for functions / data', '3', '1');
INSERT INTO configuration_group VALUES ('4', 'Images', 'Image parameters', '4', '1');
INSERT INTO configuration_group VALUES ('5', 'Customer Details', 'Customer account configuration', '5', '1');
INSERT INTO configuration_group VALUES ('6', 'Module Options', 'Hidden from configuration', '6', '0');
INSERT INTO configuration_group VALUES ('7', 'Shipping/Packaging', 'Shipping options available at my store', '7', '1');
INSERT INTO configuration_group VALUES ('8', 'Product Listing', 'Product Listing    configuration options', '8', '1');
INSERT INTO configuration_group VALUES ('9', 'Stock', 'Stock configuration options', '9', '1');
INSERT INTO configuration_group VALUES ('10', 'Logging', 'Logging configuration options', '10', '1');
INSERT INTO configuration_group VALUES ('11', 'Cache', 'Caching configuration options', '11', '1');
INSERT INTO configuration_group VALUES ('12', 'E-Mail Options', 'General setting for E-Mail transport and HTML E-Mails', '12', '1');
INSERT INTO configuration_group VALUES ('13', 'Download', 'Downloadable products options', '13', '1');
INSERT INTO configuration_group VALUES ('14', 'GZip Compression', 'GZip compression options', '14', '1');
INSERT INTO configuration_group VALUES ('15', 'Sessions', 'Session options', '15', '1');

INSERT INTO countries VALUES (1,'Afghanistan','AF','AFG','1');
INSERT INTO countries VALUES (2,'Albania','AL','ALB','1');
INSERT INTO countries VALUES (3,'Algeria','DZ','DZA','1');
INSERT INTO countries VALUES (4,'American Samoa','AS','ASM','1');
INSERT INTO countries VALUES (5,'Andorra','AD','AND','1');
INSERT INTO countries VALUES (6,'Angola','AO','AGO','1');
INSERT INTO countries VALUES (7,'Anguilla','AI','AIA','1');
INSERT INTO countries VALUES (8,'Antarctica','AQ','ATA','1');
INSERT INTO countries VALUES (9,'Antigua and Barbuda','AG','ATG','1');
INSERT INTO countries VALUES (10,'Argentina','AR','ARG','1');
INSERT INTO countries VALUES (11,'Armenia','AM','ARM','1');
INSERT INTO countries VALUES (12,'Aruba','AW','ABW','1');
INSERT INTO countries VALUES (13,'Australia','AU','AUS','1');
INSERT INTO countries VALUES (14,'Austria','AT','AUT','5');
INSERT INTO countries VALUES (15,'Azerbaijan','AZ','AZE','1');
INSERT INTO countries VALUES (16,'Bahamas','BS','BHS','1');
INSERT INTO countries VALUES (17,'Bahrain','BH','BHR','1');
INSERT INTO countries VALUES (18,'Bangladesh','BD','BGD','1');
INSERT INTO countries VALUES (19,'Barbados','BB','BRB','1');
INSERT INTO countries VALUES (20,'Belarus','BY','BLR','1');
INSERT INTO countries VALUES (21,'Belgium','BE','BEL','1');
INSERT INTO countries VALUES (22,'Belize','BZ','BLZ','1');
INSERT INTO countries VALUES (23,'Benin','BJ','BEN','1');
INSERT INTO countries VALUES (24,'Bermuda','BM','BMU','1');
INSERT INTO countries VALUES (25,'Bhutan','BT','BTN','1');
INSERT INTO countries VALUES (26,'Bolivia','BO','BOL','1');
INSERT INTO countries VALUES (27,'Bosnia and Herzegowina','BA','BIH','1');
INSERT INTO countries VALUES (28,'Botswana','BW','BWA','1');
INSERT INTO countries VALUES (29,'Bouvet Island','BV','BVT','1');
INSERT INTO countries VALUES (30,'Brazil','BR','BRA','1');
INSERT INTO countries VALUES (31,'British Indian Ocean Territory','IO','IOT','1');
INSERT INTO countries VALUES (32,'Brunei Darussalam','BN','BRN','1');
INSERT INTO countries VALUES (33,'Bulgaria','BG','BGR','1');
INSERT INTO countries VALUES (34,'Burkina Faso','BF','BFA','1');
INSERT INTO countries VALUES (35,'Burundi','BI','BDI','1');
INSERT INTO countries VALUES (36,'Cambodia','KH','KHM','1');
INSERT INTO countries VALUES (37,'Cameroon','CM','CMR','1');
INSERT INTO countries VALUES (38,'Canada','CA','CAN','1');
INSERT INTO countries VALUES (39,'Cape Verde','CV','CPV','1');
INSERT INTO countries VALUES (40,'Cayman Islands','KY','CYM','1');
INSERT INTO countries VALUES (41,'Central African Republic','CF','CAF','1');
INSERT INTO countries VALUES (42,'Chad','TD','TCD','1');
INSERT INTO countries VALUES (43,'Chile','CL','CHL','1');
INSERT INTO countries VALUES (44,'China','CN','CHN','1');
INSERT INTO countries VALUES (45,'Christmas Island','CX','CXR','1');
INSERT INTO countries VALUES (46,'Cocos (Keeling) Islands','CC','CCK','1');
INSERT INTO countries VALUES (47,'Colombia','CO','COL','1');
INSERT INTO countries VALUES (48,'Comoros','KM','COM','1');
INSERT INTO countries VALUES (49,'Congo','CG','COG','1');
INSERT INTO countries VALUES (50,'Cook Islands','CK','COK','1');
INSERT INTO countries VALUES (51,'Costa Rica','CR','CRI','1');
INSERT INTO countries VALUES (52,'Cote D\'Ivoire','CI','CIV','1');
INSERT INTO countries VALUES (53,'Croatia','HR','HRV','1');
INSERT INTO countries VALUES (54,'Cuba','CU','CUB','1');
INSERT INTO countries VALUES (55,'Cyprus','CY','CYP','1');
INSERT INTO countries VALUES (56,'Czech Republic','CZ','CZE','1');
INSERT INTO countries VALUES (57,'Denmark','DK','DNK','1');
INSERT INTO countries VALUES (58,'Djibouti','DJ','DJI','1');
INSERT INTO countries VALUES (59,'Dominica','DM','DMA','1');
INSERT INTO countries VALUES (60,'Dominican Republic','DO','DOM','1');
INSERT INTO countries VALUES (61,'East Timor','TP','TMP','1');
INSERT INTO countries VALUES (62,'Ecuador','EC','ECU','1');
INSERT INTO countries VALUES (63,'Egypt','EG','EGY','1');
INSERT INTO countries VALUES (64,'El Salvador','SV','SLV','1');
INSERT INTO countries VALUES (65,'Equatorial Guinea','GQ','GNQ','1');
INSERT INTO countries VALUES (66,'Eritrea','ER','ERI','1');
INSERT INTO countries VALUES (67,'Estonia','EE','EST','1');
INSERT INTO countries VALUES (68,'Ethiopia','ET','ETH','1');
INSERT INTO countries VALUES (69,'Falkland Islands (Malvinas)','FK','FLK','1');
INSERT INTO countries VALUES (70,'Faroe Islands','FO','FRO','1');
INSERT INTO countries VALUES (71,'Fiji','FJ','FJI','1');
INSERT INTO countries VALUES (72,'Finland','FI','FIN','1');
INSERT INTO countries VALUES (73,'France','FR','FRA','1');
INSERT INTO countries VALUES (74,'France, Metropolitan','FX','FXX','1');
INSERT INTO countries VALUES (75,'French Guiana','GF','GUF','1');
INSERT INTO countries VALUES (76,'French Polynesia','PF','PYF','1');
INSERT INTO countries VALUES (77,'French Southern Territories','TF','ATF','1');
INSERT INTO countries VALUES (78,'Gabon','GA','GAB','1');
INSERT INTO countries VALUES (79,'Gambia','GM','GMB','1');
INSERT INTO countries VALUES (80,'Georgia','GE','GEO','1');
INSERT INTO countries VALUES (81,'Germany','DE','DEU','5');
INSERT INTO countries VALUES (82,'Ghana','GH','GHA','1');
INSERT INTO countries VALUES (83,'Gibraltar','GI','GIB','1');
INSERT INTO countries VALUES (84,'Greece','GR','GRC','1');
INSERT INTO countries VALUES (85,'Greenland','GL','GRL','1');
INSERT INTO countries VALUES (86,'Grenada','GD','GRD','1');
INSERT INTO countries VALUES (87,'Guadeloupe','GP','GLP','1');
INSERT INTO countries VALUES (88,'Guam','GU','GUM','1');
INSERT INTO countries VALUES (89,'Guatemala','GT','GTM','1');
INSERT INTO countries VALUES (90,'Guinea','GN','GIN','1');
INSERT INTO countries VALUES (91,'Guinea-bissau','GW','GNB','1');
INSERT INTO countries VALUES (92,'Guyana','GY','GUY','1');
INSERT INTO countries VALUES (93,'Haiti','HT','HTI','1');
INSERT INTO countries VALUES (94,'Heard and Mc Donald Islands','HM','HMD','1');
INSERT INTO countries VALUES (95,'Honduras','HN','HND','1');
INSERT INTO countries VALUES (96,'Hong Kong','HK','HKG','1');
INSERT INTO countries VALUES (97,'Hungary','HU','HUN','1');
INSERT INTO countries VALUES (98,'Iceland','IS','ISL','1');
INSERT INTO countries VALUES (99,'India','IN','IND','1');
INSERT INTO countries VALUES (100,'Indonesia','ID','IDN','1');
INSERT INTO countries VALUES (101,'Iran (Islamic Republic of)','IR','IRN','1');
INSERT INTO countries VALUES (102,'Iraq','IQ','IRQ','1');
INSERT INTO countries VALUES (103,'Ireland','IE','IRL','1');
INSERT INTO countries VALUES (104,'Israel','IL','ISR','1');
INSERT INTO countries VALUES (105,'Italy','IT','ITA','1');
INSERT INTO countries VALUES (106,'Jamaica','JM','JAM','1');
INSERT INTO countries VALUES (107,'Japan','JP','JPN','1');
INSERT INTO countries VALUES (108,'Jordan','JO','JOR','1');
INSERT INTO countries VALUES (109,'Kazakhstan','KZ','KAZ','1');
INSERT INTO countries VALUES (110,'Kenya','KE','KEN','1');
INSERT INTO countries VALUES (111,'Kiribati','KI','KIR','1');
INSERT INTO countries VALUES (112,'Korea, Democratic People\'s Republic of','KP','PRK','1');
INSERT INTO countries VALUES (113,'Korea, Republic of','KR','KOR','1');
INSERT INTO countries VALUES (114,'Kuwait','KW','KWT','1');
INSERT INTO countries VALUES (115,'Kyrgyzstan','KG','KGZ','1');
INSERT INTO countries VALUES (116,'Lao People\'s Democratic Republic','LA','LAO','1');
INSERT INTO countries VALUES (117,'Latvia','LV','LVA','1');
INSERT INTO countries VALUES (118,'Lebanon','LB','LBN','1');
INSERT INTO countries VALUES (119,'Lesotho','LS','LSO','1');
INSERT INTO countries VALUES (120,'Liberia','LR','LBR','1');
INSERT INTO countries VALUES (121,'Libyan Arab Jamahiriya','LY','LBY','1');
INSERT INTO countries VALUES (122,'Liechtenstein','LI','LIE','1');
INSERT INTO countries VALUES (123,'Lithuania','LT','LTU','1');
INSERT INTO countries VALUES (124,'Luxembourg','LU','LUX','1');
INSERT INTO countries VALUES (125,'Macau','MO','MAC','1');
INSERT INTO countries VALUES (126,'Macedonia, The Former Yugoslav Republic of','MK','MKD','1');
INSERT INTO countries VALUES (127,'Madagascar','MG','MDG','1');
INSERT INTO countries VALUES (128,'Malawi','MW','MWI','1');
INSERT INTO countries VALUES (129,'Malaysia','MY','MYS','1');
INSERT INTO countries VALUES (130,'Maldives','MV','MDV','1');
INSERT INTO countries VALUES (131,'Mali','ML','MLI','1');
INSERT INTO countries VALUES (132,'Malta','MT','MLT','1');
INSERT INTO countries VALUES (133,'Marshall Islands','MH','MHL','1');
INSERT INTO countries VALUES (134,'Martinique','MQ','MTQ','1');
INSERT INTO countries VALUES (135,'Mauritania','MR','MRT','1');
INSERT INTO countries VALUES (136,'Mauritius','MU','MUS','1');
INSERT INTO countries VALUES (137,'Mayotte','YT','MYT','1');
INSERT INTO countries VALUES (138,'Mexico','MX','MEX','1');
INSERT INTO countries VALUES (139,'Micronesia, Federated States of','FM','FSM','1');
INSERT INTO countries VALUES (140,'Moldova, Republic of','MD','MDA','1');
INSERT INTO countries VALUES (141,'Monaco','MC','MCO','1');
INSERT INTO countries VALUES (142,'Mongolia','MN','MNG','1');
INSERT INTO countries VALUES (143,'Montserrat','MS','MSR','1');
INSERT INTO countries VALUES (144,'Morocco','MA','MAR','1');
INSERT INTO countries VALUES (145,'Mozambique','MZ','MOZ','1');
INSERT INTO countries VALUES (146,'Myanmar','MM','MMR','1');
INSERT INTO countries VALUES (147,'Namibia','NA','NAM','1');
INSERT INTO countries VALUES (148,'Nauru','NR','NRU','1');
INSERT INTO countries VALUES (149,'Nepal','NP','NPL','1');
INSERT INTO countries VALUES (150,'Netherlands','NL','NLD','1');
INSERT INTO countries VALUES (151,'Netherlands Antilles','AN','ANT','1');
INSERT INTO countries VALUES (152,'New Caledonia','NC','NCL','1');
INSERT INTO countries VALUES (153,'New Zealand','NZ','NZL','1');
INSERT INTO countries VALUES (154,'Nicaragua','NI','NIC','1');
INSERT INTO countries VALUES (155,'Niger','NE','NER','1');
INSERT INTO countries VALUES (156,'Nigeria','NG','NGA','1');
INSERT INTO countries VALUES (157,'Niue','NU','NIU','1');
INSERT INTO countries VALUES (158,'Norfolk Island','NF','NFK','1');
INSERT INTO countries VALUES (159,'Northern Mariana Islands','MP','MNP','1');
INSERT INTO countries VALUES (160,'Norway','NO','NOR','1');
INSERT INTO countries VALUES (161,'Oman','OM','OMN','1');
INSERT INTO countries VALUES (162,'Pakistan','PK','PAK','1');
INSERT INTO countries VALUES (163,'Palau','PW','PLW','1');
INSERT INTO countries VALUES (164,'Panama','PA','PAN','1');
INSERT INTO countries VALUES (165,'Papua New Guinea','PG','PNG','1');
INSERT INTO countries VALUES (166,'Paraguay','PY','PRY','1');
INSERT INTO countries VALUES (167,'Peru','PE','PER','1');
INSERT INTO countries VALUES (168,'Philippines','PH','PHL','1');
INSERT INTO countries VALUES (169,'Pitcairn','PN','PCN','1');
INSERT INTO countries VALUES (170,'Poland','PL','POL','1');
INSERT INTO countries VALUES (171,'Portugal','PT','PRT','1');
INSERT INTO countries VALUES (172,'Puerto Rico','PR','PRI','1');
INSERT INTO countries VALUES (173,'Qatar','QA','QAT','1');
INSERT INTO countries VALUES (174,'Reunion','RE','REU','1');
INSERT INTO countries VALUES (175,'Romania','RO','ROM','1');
INSERT INTO countries VALUES (176,'Russian Federation','RU','RUS','1');
INSERT INTO countries VALUES (177,'Rwanda','RW','RWA','1');
INSERT INTO countries VALUES (178,'Saint Kitts and Nevis','KN','KNA','1');
INSERT INTO countries VALUES (179,'Saint Lucia','LC','LCA','1');
INSERT INTO countries VALUES (180,'Saint Vincent and the Grenadines','VC','VCT','1');
INSERT INTO countries VALUES (181,'Samoa','WS','WSM','1');
INSERT INTO countries VALUES (182,'San Marino','SM','SMR','1');
INSERT INTO countries VALUES (183,'Sao Tome and Principe','ST','STP','1');
INSERT INTO countries VALUES (184,'Saudi Arabia','SA','SAU','1');
INSERT INTO countries VALUES (185,'Senegal','SN','SEN','1');
INSERT INTO countries VALUES (186,'Seychelles','SC','SYC','1');
INSERT INTO countries VALUES (187,'Sierra Leone','SL','SLE','1');
INSERT INTO countries VALUES (188,'Singapore','SG','SGP', '4');
INSERT INTO countries VALUES (189,'Slovakia (Slovak Republic)','SK','SVK','1');
INSERT INTO countries VALUES (190,'Slovenia','SI','SVN','1');
INSERT INTO countries VALUES (191,'Solomon Islands','SB','SLB','1');
INSERT INTO countries VALUES (192,'Somalia','SO','SOM','1');
INSERT INTO countries VALUES (193,'South Africa','ZA','ZAF','1');
INSERT INTO countries VALUES (194,'South Georgia and the South Sandwich Islands','GS','SGS','1');
INSERT INTO countries VALUES (195,'Spain','ES','ESP','3');
INSERT INTO countries VALUES (196,'Sri Lanka','LK','LKA','1');
INSERT INTO countries VALUES (197,'St. Helena','SH','SHN','1');
INSERT INTO countries VALUES (198,'St. Pierre and Miquelon','PM','SPM','1');
INSERT INTO countries VALUES (199,'Sudan','SD','SDN','1');
INSERT INTO countries VALUES (200,'Suriname','SR','SUR','1');
INSERT INTO countries VALUES (201,'Svalbard and Jan Mayen Islands','SJ','SJM','1');
INSERT INTO countries VALUES (202,'Swaziland','SZ','SWZ','1');
INSERT INTO countries VALUES (203,'Sweden','SE','SWE','1');
INSERT INTO countries VALUES (204,'Switzerland','CH','CHE','1');
INSERT INTO countries VALUES (205,'Syrian Arab Republic','SY','SYR','1');
INSERT INTO countries VALUES (206,'Taiwan','TW','TWN','1');
INSERT INTO countries VALUES (207,'Tajikistan','TJ','TJK','1');
INSERT INTO countries VALUES (208,'Tanzania, United Republic of','TZ','TZA','1');
INSERT INTO countries VALUES (209,'Thailand','TH','THA','1');
INSERT INTO countries VALUES (210,'Togo','TG','TGO','1');
INSERT INTO countries VALUES (211,'Tokelau','TK','TKL','1');
INSERT INTO countries VALUES (212,'Tonga','TO','TON','1');
INSERT INTO countries VALUES (213,'Trinidad and Tobago','TT','TTO','1');
INSERT INTO countries VALUES (214,'Tunisia','TN','TUN','1');
INSERT INTO countries VALUES (215,'Turkey','TR','TUR','1');
INSERT INTO countries VALUES (216,'Turkmenistan','TM','TKM','1');
INSERT INTO countries VALUES (217,'Turks and Caicos Islands','TC','TCA','1');
INSERT INTO countries VALUES (218,'Tuvalu','TV','TUV','1');
INSERT INTO countries VALUES (219,'Uganda','UG','UGA','1');
INSERT INTO countries VALUES (220,'Ukraine','UA','UKR','1');
INSERT INTO countries VALUES (221,'United Arab Emirates','AE','ARE','1');
INSERT INTO countries VALUES (222,'United Kingdom','GB','GBR','1');
INSERT INTO countries VALUES (223,'United States','US','USA', '2');
INSERT INTO countries VALUES (224,'United States Minor Outlying Islands','UM','UMI','1');
INSERT INTO countries VALUES (225,'Uruguay','UY','URY','1');
INSERT INTO countries VALUES (226,'Uzbekistan','UZ','UZB','1');
INSERT INTO countries VALUES (227,'Vanuatu','VU','VUT','1');
INSERT INTO countries VALUES (228,'Vatican City State (Holy See)','VA','VAT','1');
INSERT INTO countries VALUES (229,'Venezuela','VE','VEN','1');
INSERT INTO countries VALUES (230,'Viet Nam','VN','VNM','1');
INSERT INTO countries VALUES (231,'Virgin Islands (British)','VG','VGB','1');
INSERT INTO countries VALUES (232,'Virgin Islands (U.S.)','VI','VIR','1');
INSERT INTO countries VALUES (233,'Wallis and Futuna Islands','WF','WLF','1');
INSERT INTO countries VALUES (234,'Western Sahara','EH','ESH','1');
INSERT INTO countries VALUES (235,'Yemen','YE','YEM','1');
INSERT INTO countries VALUES (236,'Yugoslavia','YU','YUG','1');
INSERT INTO countries VALUES (237,'Zaire','ZR','ZAR','1');
INSERT INTO countries VALUES (238,'Zambia','ZM','ZMB','1');
INSERT INTO countries VALUES (239,'Zimbabwe','ZW','ZWE','1');

INSERT INTO currencies VALUES (1,'US Dollar','USD','$','','.',',','2','1.0000', now());
INSERT INTO currencies VALUES (2,'Euro','EUR','','EUR','.',',','2','1.1036', now());

INSERT INTO customers VALUES ( '1', 'm', 'John', 'doe', '2001-01-01 00:00:00', 'root@localhost', '1', '12345', '', 'd95e8fa7f20a009372eb3477473fcd34:1c', '0');

INSERT INTO customers_info VALUES('1', null, '0', now(), null, '0');

INSERT INTO languages VALUES (1,'English','en','icon.gif','english',1);
INSERT INTO languages VALUES (2,'Deutsch','de','icon.gif','german',2);
INSERT INTO languages VALUES (3,'Español','es','icon.gif','espanol',3);

INSERT INTO manufacturers VALUES (1,'Matrox','manufacturer_matrox.gif', now(), null);
INSERT INTO manufacturers VALUES (2,'Microsoft','manufacturer_microsoft.gif', now(), null);
INSERT INTO manufacturers VALUES (3,'Warner','manufacturer_warner.gif', now(), null);
INSERT INTO manufacturers VALUES (4,'Fox','manufacturer_fox.gif', now(), null);
INSERT INTO manufacturers VALUES (5,'Logitech','manufacturer_logitech.gif', now(), null);
INSERT INTO manufacturers VALUES (6,'Canon','manufacturer_canon.gif', now(), null);
INSERT INTO manufacturers VALUES (7,'Sierra','manufacturer_sierra.gif', now(), null);
INSERT INTO manufacturers VALUES (8,'GT Interactive','manufacturer_gt_interactive.gif', now(), null);
INSERT INTO manufacturers VALUES (9,'Hewlett Packard','manufacturer_hewlett_packard.gif', now(), null);

INSERT INTO manufacturers_info VALUES (1, 1, 'http://www.matrox.com', 0, null);
INSERT INTO manufacturers_info VALUES (1, 2, 'http://www.matrox.de', 0, null);
INSERT INTO manufacturers_info VALUES (1, 3, 'http://www.matrox.com', 0, null);
INSERT INTO manufacturers_info VALUES (2, 1, 'http://www.microsoft.com', 0, null);
INSERT INTO manufacturers_info VALUES (2, 2, 'http://www.microsoft.de', 0, null);
INSERT INTO manufacturers_info VALUES (2, 3, 'http://www.microsoft.es', 0, null);
INSERT INTO manufacturers_info VALUES (3, 1, 'http://www.warner.com', 0, null);
INSERT INTO manufacturers_info VALUES (3, 2, 'http://www.warner.de', 0, null);
INSERT INTO manufacturers_info VALUES (3, 3, 'http://www.warner.com', 0, null);
INSERT INTO manufacturers_info VALUES (4, 1, 'http://www.fox.com', 0, null);
INSERT INTO manufacturers_info VALUES (4, 2, 'http://www.fox.de', 0, null);
INSERT INTO manufacturers_info VALUES (4, 3, 'http://www.fox.com', 0, null);
INSERT INTO manufacturers_info VALUES (5, 1, 'http://www.logitech.com', 0, null);
INSERT INTO manufacturers_info VALUES (5, 2, 'http://www.logitech.com', 0, null);
INSERT INTO manufacturers_info VALUES (5, 3, 'http://www.logitech.com', 0, null);
INSERT INTO manufacturers_info VALUES (6, 1, 'http://www.canon.com', 0, null);
INSERT INTO manufacturers_info VALUES (6, 2, 'http://www.canon.de', 0, null);
INSERT INTO manufacturers_info VALUES (6, 3, 'http://www.canon.es', 0, null);
INSERT INTO manufacturers_info VALUES (7, 1, 'http://www.sierra.com', 0, null);
INSERT INTO manufacturers_info VALUES (7, 2, 'http://www.sierra.de', 0, null);
INSERT INTO manufacturers_info VALUES (7, 3, 'http://www.sierra.com', 0, null);
INSERT INTO manufacturers_info VALUES (8, 1, 'http://www.infogrames.com', 0, null);
INSERT INTO manufacturers_info VALUES (8, 2, 'http://www.infogrames.de', 0, null);
INSERT INTO manufacturers_info VALUES (8, 3, 'http://www.infogrames.com', 0, null);
INSERT INTO manufacturers_info VALUES (9, 1, 'http://www.hewlettpackard.com', 0, null);
INSERT INTO manufacturers_info VALUES (9, 2, 'http://www.hewlettpackard.de', 0, null);
INSERT INTO manufacturers_info VALUES (9, 3, 'http://welcome.hp.com/country/es/spa/welcome.htm', 0, null);

INSERT INTO orders_status VALUES ( '1', '1', 'Pending');
INSERT INTO orders_status VALUES ( '1', '2', 'Offen');
INSERT INTO orders_status VALUES ( '1', '3', 'Pendiente');
INSERT INTO orders_status VALUES ( '2', '1', 'Processing');
INSERT INTO orders_status VALUES ( '2', '2', 'In Bearbeitung');
INSERT INTO orders_status VALUES ( '2', '3', 'Proceso');
INSERT INTO orders_status VALUES ( '3', '1', 'Delivered');
INSERT INTO orders_status VALUES ( '3', '2', 'Versendet');
INSERT INTO orders_status VALUES ( '3', '3', 'Entregado');

INSERT INTO products VALUES (1,32,'MG200MMS','matrox/mg200mms.gif',299.99, now(),null,null,23.00,1,1,1,0);
INSERT INTO products VALUES (2,32,'MG400-32MB','matrox/mg400-32mb.gif',499.99, now(),null,null,23.00,1,1,1,0);
INSERT INTO products VALUES (3,2,'MSIMPRO','microsoft/msimpro.gif',49.99, now(),null,null,7.00,1,1,3,0);
INSERT INTO products VALUES (4,13,'DVD-RPMK','dvd/replacement_killers.gif',42.00, now(),null,null,23.00,1,1,2,0);
INSERT INTO products VALUES (5,17,'DVD-BLDRNDC','dvd/blade_runner.gif',35.99, now(),null,null,7.00,1,1,3,0);
INSERT INTO products VALUES (6,10,'DVD-MATR','dvd/the_matrix.gif',39.99, now(),null,null,7.00,1,1,3,0);
INSERT INTO products VALUES (7,10,'DVD-YGEM','dvd/youve_got_mail.gif',34.99, now(),null,null,7.00,1,1,3,0);
INSERT INTO products VALUES (8,10,'DVD-ABUG','dvd/a_bugs_life.gif',35.99, now(),null,null,7.00,1,1,3,0);
INSERT INTO products VALUES (9,10,'DVD-UNSG','dvd/under_siege.gif',29.99, now(),null,null,7.00,1,1,3,0);
INSERT INTO products VALUES (10,10,'DVD-UNSG2','dvd/under_siege2.gif',29.99, now(),null,null,7.00,1,1,3,0);
INSERT INTO products VALUES (11,10,'DVD-FDBL','dvd/fire_down_below.gif',29.99, now(),null,null,7.00,1,1,3,0);
INSERT INTO products VALUES (12,10,'DVD-DHWV','dvd/die_hard_3.gif',39.99, now(),null,null,7.00,1,1,4,0);
INSERT INTO products VALUES (13,10,'DVD-LTWP','dvd/lethal_weapon.gif',34.99, now(),null,null,7.00,1,1,3,0);
INSERT INTO products VALUES (14,10,'DVD-REDC','dvd/red_corner.gif',32.00, now(),null,null,7.00,1,1,3,0);
INSERT INTO products VALUES (15,10,'DVD-FRAN','dvd/frantic.gif',35.00, now(),null,null,7.00,1,1,3,0);
INSERT INTO products VALUES (16,10,'DVD-CUFI','dvd/courage_under_fire.gif',38.99, now(),null,null,7.00,1,1,4,0);
INSERT INTO products VALUES (17,10,'DVD-SPEED','dvd/speed.gif',39.99, now(),null,null,7.00,1,1,4,0);
INSERT INTO products VALUES (18,10,'DVD-SPEED2','dvd/speed_2.gif',42.00, now(),null,null,7.00,1,1,4,0);
INSERT INTO products VALUES (19,10,'DVD-TSAB','dvd/theres_something_about_mary.gif',49.99, now(),null,null,7.00,1,1,4,0);
INSERT INTO products VALUES (20,10,'DVD-BELOVED','dvd/beloved.gif',54.99, now(),null,null,7.00,1,1,3,0);
INSERT INTO products VALUES (21,16,'PC-SWAT3','sierra/swat_3.gif',79.99, now(),null,null,7.00,1,1,7,0);
INSERT INTO products VALUES (22,13,'PC-UNTM','gt_interactive/unreal_tournament.gif',89.99, now(),null,null,7.00,1,1,8,0);
INSERT INTO products VALUES (23,16,'PC-TWOF','gt_interactive/wheel_of_time.gif',99.99, now(),null,null,10.00,1,1,8,0);
INSERT INTO products VALUES (24,17,'PC-DISC','gt_interactive/disciples.gif',90.00, now(),null,null,8.00,1,1,8,0);
INSERT INTO products VALUES (25,16,'MSINTKB','microsoft/intkeyboardps2.gif',69.99, now(),null,null,8.00,1,1,2,0);
INSERT INTO products VALUES (26,10,'MSIMEXP','microsoft/imexplorer.gif',64.95, now(),null,null,8.00,1,1,2,0);
INSERT INTO products VALUES (27,8,'HPLJ1100XI','hewlett_packard/lj1100xi.gif',499.99, now(),null,null,45.00,1,1,9,0);

INSERT INTO products_description VALUES (1,1,'Matrox G200 MMS','Reinforcing its position as a multi-monitor trailblazer, Matrox Graphics Inc. has once again developed the most flexible and highly advanced solution in the industry. Introducing the new Matrox G200 Multi-Monitor Series; the first graphics card ever to support up to four DVI digital flat panel displays on a single 8&quot; PCI board.<br><br>With continuing demand for digital flat panels in the financial workplace, the Matrox G200 MMS is the ultimate in flexible solutions. The Matrox G200 MMS also supports the new digital video interface (DVI) created by the Digital Display Working Group (DDWG) designed to ease the adoption of digital flat panels. Other configurations include composite video capture ability and onboard TV tuner, making the Matrox G200 MMS the complete solution for business needs.<br><br>Based on the award-winning MGA-G200 graphics chip, the Matrox G200 Multi-Monitor Series provides superior 2D/3D graphics acceleration to meet the demanding needs of business applications such as real-time stock quotes (Versus), live video feeds (Reuters & Bloombergs), multiple windows applications, word processing, spreadsheets and CAD.','www.matrox.com/mga/products/g200_mms/home.cfm',0);
INSERT INTO products_description VALUES (2,1,'Matrox G400 32MB','<b>Dramatically Different High Performance Graphics</b><br><br>Introducing the Millennium G400 Series - a dramatically different, high performance graphics experience. Armed with the industry\'s fastest graphics chip, the Millennium G400 Series takes explosive acceleration two steps further by adding unprecedented image quality, along with the most versatile display options for all your 3D, 2D and DVD applications. As the most powerful and innovative tools in your PC\'s arsenal, the Millennium G400 Series will not only change the way you see graphics, but will revolutionize the way you use your computer.<br><br><b>Key features:</b><ul><li>New Matrox G400 256-bit DualBus graphics chip</li><li>Explosive 3D, 2D and DVD performance</li><li>DualHead Display</li><li>Superior DVD and TV output</li><li>3D Environment-Mapped Bump Mapping</li><li>Vibrant Color Quality rendering </li><li>UltraSharp DAC of up to 360 MHz</li><li>3D Rendering Array Processor</li><li>Support for 16 or 32 MB of memory</li></ul>','www.matrox.com/mga/products/mill_g400/home.htm',0);
INSERT INTO products_description VALUES (3,1,'Microsoft IntelliMouse Pro','Every element of IntelliMouse Pro - from its unique arched shape to the texture of the rubber grip around its base - is the product of extensive customer and ergonomic research. Microsoft\'s popular wheel control, which now allows zooming and universal scrolling functions, gives IntelliMouse Pro outstanding comfort and efficiency.','www.microsoft.com/hardware/mouse/intellimouse.asp',0);
INSERT INTO products_description VALUES (4,1,'The Replacement Killers','Regional Code: 2 (Japan, Europe, Middle East, South Africa).<br>Languages: English, Deutsch.<br>Subtitles: English, Deutsch, Spanish.<br>Audio: Dolby Surround 5.1.<br>Picture Format: 16:9 Wide-Screen.<br>Length: (approx) 80 minutes.<br>Other: Interactive Menus, Chapter Selection, Subtitles (more languages).','www.replacement-killers.com',0);
INSERT INTO products_description VALUES (5,1,'Blade Runner - Director\'s Cut','Regional Code: 2 (Japan, Europe, Middle East, South Africa).<br>Languages: English, Deutsch.<br>Subtitles: English, Deutsch, Spanish.<br>Audio: Dolby Surround 5.1.<br>Picture Format: 16:9 Wide-Screen.<br>Length: (approx) 112 minutes.<br>Other: Interactive Menus, Chapter Selection, Subtitles (more languages).','www.bladerunner.com',0);
INSERT INTO products_description VALUES (6,1,'The Matrix','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch.\r<br>\nAudio: Dolby Surround.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 131 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Making Of.','www.thematrix.com',0);
INSERT INTO products_description VALUES (7,1,'You\'ve Got Mail','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch, Spanish.\r<br>\nSubtitles: English, Deutsch, Spanish, French, Nordic, Polish.\r<br>\nAudio: Dolby Digital 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 115 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','www.youvegotmail.com',0);
INSERT INTO products_description VALUES (8,1,'A Bug\'s Life','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Digital 5.1 / Dobly Surround Stereo.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 91 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','www.abugslife.com',0);
INSERT INTO products_description VALUES (9,1,'Under Siege','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 98 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','',0);
INSERT INTO products_description VALUES (10,1,'Under Siege 2 - Dark Territory','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 98 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','',0);
INSERT INTO products_description VALUES (11,1,'Fire Down Below','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 100 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','',0);
INSERT INTO products_description VALUES (12,1,'Die Hard With A Vengeance','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 122 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','',0);
INSERT INTO products_description VALUES (13,1,'Lethal Weapon','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 100 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','',0);
INSERT INTO products_description VALUES (14,1,'Red Corner','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 117 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','',0);
INSERT INTO products_description VALUES (15,1,'Frantic','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 115 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','',0);
INSERT INTO products_description VALUES (16,1,'Courage Under Fire','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 112 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','',0);
INSERT INTO products_description VALUES (17,1,'Speed','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 112 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','',0);
INSERT INTO products_description VALUES (18,1,'Speed 2: Cruise Control','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 120 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','',0);
INSERT INTO products_description VALUES (19,1,'There\'s Something About Mary','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 114 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','',0);
INSERT INTO products_description VALUES (20,1,'Beloved','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 164 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','',0);
INSERT INTO products_description VALUES (21,1,'SWAT 3: Close Quarters Battle','<b>Windows 95/98</b><br><br>211 in progress with shots fired. Officer down. Armed suspects with hostages. Respond Code 3! Los Angles, 2005, In the next seven days, representatives from every nation around the world will converge on Las Angles to witness the signing of the United Nations Nuclear Abolishment Treaty. The protection of these dignitaries falls on the shoulders of one organization, LAPD SWAT. As part of this elite tactical organization, you and your team have the weapons and all the training necessary to protect, to serve, and \"When needed\" to use deadly force to keep the peace. It takes more than weapons to make it through each mission. Your arsenal includes C2 charges, flashbangs, tactical grenades. opti-Wand mini-video cameras, and other devices critical to meeting your objectives and keeping your men free of injury. Uncompromised Duty, Honor and Valor!','www.swat3.com',0);
INSERT INTO products_description VALUES (22,1,'Unreal Tournament','From the creators of the best-selling Unreal, comes Unreal Tournament. A new kind of single player experience. A ruthless multiplayer revolution.<br><br>This stand-alone game showcases completely new team-based gameplay, groundbreaking multi-faceted single player action or dynamic multi-player mayhem. It\'s a fight to the finish for the title of Unreal Grand Master in the gladiatorial arena. A single player experience like no other! Guide your team of \'bots\' (virtual teamates) against the hardest criminals in the galaxy for the ultimate title - the Unreal Grand Master.','www.unrealtournament.net',0);
INSERT INTO products_description VALUES (23,1,'The Wheel Of Time','The world in which The Wheel of Time takes place is lifted directly out of Jordan\'s pages; it\'s huge and consists of many different environments. How you navigate the world will depend largely on which game - single player or multipayer - you\'re playing. The single player experience, with a few exceptions, will see Elayna traversing the world mainly by foot (with a couple notable exceptions). In the multiplayer experience, your character will have more access to travel via Ter\'angreal, Portal Stones, and the Ways. However you move around, though, you\'ll quickly discover that means of locomotion can easily become the least of the your worries...<br><br>During your travels, you quickly discover that four locations are crucial to your success in the game. Not surprisingly, these locations are the homes of The Wheel of Time\'s main characters. Some of these places are ripped directly from the pages of Jordan\'s books, made flesh with Legend\'s unparalleled pixel-pushing ways. Other places are specific to the game, conceived and executed with the intent of expanding this game world even further. Either way, they provide a backdrop for some of the most intense first person action and strategy you\'ll have this year.','www.wheeloftime.com',0);
INSERT INTO products_description VALUES (24,1,'Disciples: Sacred Lands','A new age is dawning...<br><br>Enter the realm of the Sacred Lands, where the dawn of a New Age has set in motion the most momentous of wars. As the prophecies long foretold, four races now clash with swords and sorcery in a desperate bid to control the destiny of their gods. Take on the quest as a champion of the Empire, the Mountain Clans, the Legions of the Damned, or the Undead Hordes and test your faith in battles of brute force, spellbinding magic and acts of guile. Slay demons, vanquish giants and combat merciless forces of the dead and undead. But to ensure the salvation of your god, the hero within must evolve.<br><br>The day of reckoning has come... and only the chosen will survive.','',0);
INSERT INTO products_description VALUES (25,1,'Microsoft Internet Keyboard PS/2','The Internet Keyboard has 10 Hot Keys on a comfortable standard keyboard design that also includes a detachable palm rest. The Hot Keys allow you to browse the web, or check e-mail directly from your keyboard. The IntelliType Pro software also allows you to customize your hot keys - make the Internet Keyboard work the way you want it to!','',0);
INSERT INTO products_description VALUES (26,1,'Microsoft IntelliMouse Explorer','Microsoft introduces its most advanced mouse, the IntelliMouse Explorer! IntelliMouse Explorer features a sleek design, an industrial-silver finish, a glowing red underside and taillight, creating a style and look unlike any other mouse. IntelliMouse Explorer combines the accuracy and reliability of Microsoft IntelliEye optical tracking technology, the convenience of two new customizable function buttons, the efficiency of the scrolling wheel and the comfort of expert ergonomic design. All these great features make this the best mouse for the PC!','www.microsoft.com/hardware/mouse/explorer.asp',0);
INSERT INTO products_description VALUES (27,1,'Hewlett Packard LaserJet 1100Xi','HP has always set the pace in laser printing technology. The new generation HP LaserJet 1100 series sets another impressive pace, delivering a stunning 8 pages per minute print speed. The 600 dpi print resolution with HP\'s Resolution Enhancement technology (REt) makes every document more professional.<br><br>Enhanced print speed and laser quality results are just the beginning. With 2MB standard memory, HP LaserJet 1100xi users will be able to print increasingly complex pages. Memory can be increased to 18MB to tackle even more complex documents with ease. The HP LaserJet 1100xi supports key operating systems including Windows 3.1, 3.11, 95, 98, NT 4.0, OS/2 and DOS. Network compatibility available via the optional HP JetDirect External Print Servers.<br><br>HP LaserJet 1100xi also features The Document Builder for the Web Era from Trellix Corp. (featuring software to create Web documents).','www.pandi.hp.com/pandi-db/prodinfo.main?product=laserjet1100',0);
INSERT INTO products_description VALUES (1,2,'Matrox G200 MMS','<b>Unterstützung für zwei bzw. vier analoge oder digitale Monitore</b><br><br>\r\nDie Matrox G200 Multi-Monitor-Serie führt die bewährte Matrox Tradition im Multi-Monitor- Bereich fort und bietet flexible und fortschrittliche Lösungen.Matrox stellt als erstes Unternehmen einen vierfachen digitalen PanelLink® DVI Flachbildschirm Ausgang zur Verfügung. Mit den optional erhältlichen TV Tuner und Video-Capture Möglichkeiten stellt die Matrox G200 MMS eine alles umfassende Mehrschirm-Lösung dar.<br><br>\r\n<b>Leistungsmerkmale:</b>\r\n<ul>\r\n<li>Preisgekrönter Matrox G200 128-Bit Grafikchip</li>\r\n<li>Schneller 8 MB SGRAM-Speicher pro Kanal</li>\r\n<li>Integrierter, leistungsstarker 250 MHz RAMDAC</li>\r\n<li>Unterstützung für bis zu 16 Bildschirme über 4 Quad-Karten (unter Win NT,ab Treiber 4.40)</li>\r\n<li>Unterstützung von 9 Monitoren unter Win 98</li>\r\n<li>2 bzw. 4 analoge oder digitale Ausgabekanäle pro PCI-Karte</li>\r\n<li>Desktop-Darstellung von 1800 x 1440 oder 1920 x 1200 pro Chip</li>\r\n<li>Anschlußmöglichkeit an einen 15-poligen VGA-Monitor oder an einen 30-poligen digitalen DVI-Flachbildschirm plus integriertem Composite-Video-Eingang (bei der TV-Version)</li>\r\n<li>PCI Grafikkarte, kurze Baulänge</li>\r\n<li>Treiberunterstützung: Windows® 2000, Windows NT® und Windows® 98</li>\r\n<li>Anwendungsbereiche: Börsenumgebung mit zeitgleich großem Visualisierungsbedarf, Videoüberwachung, Video-Conferencing</li>\r\n</ul>','www.matrox.com/mga/deutsch/products/g200_mms/home.cfm',0);
INSERT INTO products_description VALUES (2,2,'Matrox G400 32 MB','<b>Neu! Matrox G400 &quot;all inclusive&quot; und vieles mehr...</b><br><br>\r\nDie neue Millennium G400-Serie - Hochleistungsgrafik mit dem sensationellen Unterschied. Ausgestattet mit dem neu eingeführten Matrox G400 Grafikchip, bietet die Millennium G400-Serie eine überragende Beschleunigung inklusive bisher nie dagewesener Bildqualitat und enorm flexibler Darstellungsoptionen bei allen Ihren 3D-, 2D- und DVD-Anwendungen.<br><br>\r\n<ul>\r\n<li>DualHead Display und TV-Ausgang</li>\r\n<li>Environment Mapped Bump Mapping</li>\r\n<li>Matrox G400 256-Bit DualBus</li>\r\n<li>3D Rendering Array Prozessor</li>\r\n<li>Vibrant Color Quality² (VCQ²)</li>\r\n<li>UltraSharp DAC</li>\r\n</ul>\r\n<i>&quot;Bleibt abschließend festzustellen, daß die Matrox Millennium G400 Max als Allroundkarte für den Highend-PC derzeit unerreicht ist. Das ergibt den Testsieg und unsere wärmste Empfehlung.&quot;</i><br>\r\n<b>GameStar 8/99 (S.184)</b>','www.matrox.com/mga/deutsch/products/mill_g400/home.cfm',0);
INSERT INTO products_description VALUES (3,2,'Microsoft IntelliMouse Pro','Die IntelliMouse Pro hat mit der IntelliRad-Technologie einen neuen Standard gesetzt. Anwenderfreundliche Handhabung und produktiveres Arbeiten am PC zeichnen die IntelliMouse aus. Die gewölbte Oberseite paßt sich natürlich in die Handfläche ein, die geschwungene Form erleichtert das Bedienen der Maus. Sie ist sowohl für Rechts- wie auch für Linkshänder geeignet. Mit dem Rad der IntelliMouse kann der Anwender einfach und komfortabel durch Dokumente navigieren.<br><br>\r\n<b>Eigenschaften:</b>\r\n<ul>\r\n<li><b>ANSCHLUSS:</b> PS/2</li>\r\n<li><b>FARBE:</b> weiß</li>\r\n<li><b>TECHNIK:</b> Mauskugel</li>\r\n<li><b>TASTEN:</b> 3 (incl. Scrollrad)</li>\r\n<li><b>SCROLLRAD:</b> Ja</li>\r\n</ul>','',0);
INSERT INTO products_description VALUES (4,2,'Die Ersatzkiller','Originaltitel: &quot;The Replacement Killers&quot;<br><br>\r\nRegional Code: 2 (Japan, Europe, Middle East, South Africa).<br>\r\nSprachen: English, Deutsch.<br>\r\nUntertitel: English, Deutsch, Spanish.<br>\r\nAudio: Dolby Surround 5.1.<br>\r\nBildformat: 16:9 Wide-Screen.<br>\r\nDauer: (approx) 80 minutes.<br>\r\nAußerdem: Interaktive Menus, Kapitelauswahl, Untertitel.<br><br>\r\n(USA 1998). Til Schweiger schießt auf Hongkong-Star Chow Yun-Fat (&quot;Anna und der König&quot;) ­ ein Fehler ...','www.replacement-killers.com',0);
INSERT INTO products_description VALUES (5,2,'Blade Runner - Director\'s Cut','Regional Code: 2 (Japan, Europe, Middle East, South Africa).<br>\r\nSprachen: English, Deutsch.<br>\r\nUntertitel: English, Deutsch, Spanish.<br>\r\nAudio: Dolby Surround 5.1.<br>\r\nBildformat: 16:9 Wide-Screen.<br>\r\nDauer: (approx) 112 minutes.<br>\r\nAußerdem: Interaktive Menus, Kapitelauswahl, Untertitel.<br><br>\r\n<b>Sci-Fi-Klassiker, USA 1983, 112 Min.</b><br><br>\r\nLos Angeles ist im Jahr 2019 ein Hexenkessel. Dauerregen und Smog tauchen den überbevölkerten Moloch in ewige Dämmerung. Polizeigleiter schwirren durch die Luft und überwachen das grelle Ethnogemisch, das sich am Fuße 400stöckiger Stahlbeton-Pyramiden tummelt. Der abgehalfterte Ex-Cop und \"Blade Runner\" Rick Deckard ist Spezialist für die Beseitigung von Replikanten, künstlichen Menschen, geschaffen für harte Arbeit auf fremden Planeten. Nur ihm kann es gelingen, vier flüchtige, hochintelligente \"Nexus 6\"-Spezialmodelle zu stellen. Die sind mit ihrem starken und brandgefährlichen Anführer Batty auf der Suche nach ihrem Schöpfer. Er soll ihnen eine längere Lebenszeit schenken. Das muß Rick Deckard verhindern.  Als sich der eiskalte Jäger in Rachel, die Sekretärin seines Auftraggebers, verliebt, gerät sein Weltbild jedoch ins Wanken. Er entdeckt, daß sie - vielleicht wie er selbst - ein Replikant ist ...<br><br>\r\nDie Konfrontation des Menschen mit \"Realität\" und \"Virtualität\" und das verstrickte Spiel mit getürkten Erinnerungen zieht sich als roter Faden durch das Werk von Autor Philip K. Dick (\"Die totale Erinnerung\"). Sein Roman \"Träumen Roboter von elektrischen Schafen?\" liefert die Vorlage für diesen doppelbödigen Thriller, der den Zuschauer mit faszinierenden\r\nZukunftsvisionen und der gigantischen Kulisse des Großstadtmolochs gefangen nimmt.','www.bladerunner.com',0);
INSERT INTO products_description VALUES (6,2,'Matrix','Originaltitel: &quot;The Matrix&quot;<br><br>\r\nRegional Code: 2 (Japan, Europe, Middle East, South Africa).<br>\r\nSprachen: English, Deutsch.<br>\r\nUntertitel: English, Deutsch, Spanish.<br>\r\nAudio: Dolby Surround 5.1.<br>\r\nBildformat: 16:9 Wide-Screen.<br>\r\nDauer: (approx) 136 minuten.<br>\r\nAußerdem: Interaktive Menus, Kapitelauswahl, Untertitel.<br><br>\r\n(USA 1999) Der Geniestreich der Wachowski-Brüder. In dieser technisch perfekten Utopie kämpft Hacker Keanu Reeves gegen die Diktatur der Maschinen...','www.whatisthematrix.com',0);
INSERT INTO products_description VALUES (7,2,'e-m@il für Dich','Original: &quot;You\'ve got mail&quot;<br>\r\nRegional Code: 2 (Japan, Europe, Middle East, South Africa).<br>\r\nSprachen: English, Deutsch.<br>\r\nUntertitel: English, Deutsch, Spanish.<br>\r\nAudio: Dolby Surround 5.1.<br>\r\nBildformat: 16:9 Wide-Screen.<br>\r\nDauer: (approx) 112 minutes.<br>\r\nAußerdem: Interaktive Menus, Kapitelauswahl, Untertitel.<br><br>\r\n(USA 1998) von Nora Ephron (&qout;Schlaflos in Seattle&quot;). Meg Ryan und Tom Hanks knüpfen per E-Mail zarte Bande. Dass sie sich schon kennen, ahnen sie nicht ...','www.youvegotmail.com',0);
INSERT INTO products_description VALUES (8,2,'Das Große Krabbeln','Originaltitel: &quot;A Bug\'s Life&quot;<br><br>\r\nRegional Code: 2 (Japan, Europe, Middle East, South Africa).<br>\r\nSprachen: English, Deutsch.<br>\r\nUntertitel: English, Deutsch, Spanish.<br>\r\nAudio: Dolby Surround 5.1.<br>\r\nBildformat: 16:9 Wide-Screen.<br>\r\nDauer: (approx) 96 minuten.<br>\r\nAußerdem: Interaktive Menus, Kapitelauswahl, Untertitel.<br><br>\r\n(USA 1998). Ameise Flik zettelt einen Aufstand gegen gefräßige Grashüpfer an ... Eine fantastische Computeranimation des \"Toy Story\"-Teams. ','www.abugslife.com',0);
INSERT INTO products_description VALUES (9,2,'Alarmstufe: Rot','Originaltitel: &quot;Under Siege&quot;<br><br>\r\nRegional Code: 2 (Japan, Europe, Middle East, South Africa).<br>\r\nSprachen: English, Deutsch.<br>\r\nUntertitel: English, Deutsch, Spanish.<br>\r\nAudio: Dolby Surround 5.1.<br>\r\nBildformat: 16:9 Wide-Screen.<br>\r\nDauer: (approx) 96 minuten.<br>\r\nAußerdem: Interaktive Menus, Kapitelauswahl, Untertitel.<br><br>\r\n<b>Actionthriller. Smutje Steven Seagal versalzt Schurke Tommy Lee Jones die Suppe</b><br><br>\r\nKatastrophe ahoi: Kurz vor Ausmusterung der \"U.S.S. Missouri\" kapert die High-tech-Bande von Ex-CIA-Agent Strannix (Tommy Lee Jones) das Schlachtschiff. Strannix will die Nuklearraketen des Kreuzers klauen und verscherbeln. Mit Hilfe des irren Ersten Offiziers Krill (lustig: Gary Busey) killen die Gangster den Käpt’n und sperren seine Crew unter Deck. Blöd, dass sie dabei Schiffskoch Rybak (Steven Seagal) vergessen. Der Ex-Elitekämpfer knipst einen Schurken nach dem anderen aus. Eine Verbündete findet er in Stripperin Jordan (Ex-\"Baywatch\"-Biene Erika Eleniak). Die sollte eigentlich aus Käpt’ns Geburtstagstorte hüpfen ... Klar: Seagal ist kein Edelmime. Dafür ist Regisseur Andrew Davis (\"Auf der Flucht\") ein Könner: Er würzt die Action-Orgie mit Ironie und nutzt die imposante Schiffskulisse voll aus. Für Effekte und Ton gab es 1993 Oscar-Nominierungen. ','',0);
INSERT INTO products_description VALUES (10,2,'Alarmstufe: Rot 2','Originaltitel: &quot;Under Siege 2: Dark Territory&quot;<br><br>\r\nRegional Code: 2 (Japan, Europe, Middle East, South Africa).<br>\r\nSprachen: English, Deutsch.<br>\r\nUntertitel: English, Deutsch, Spanish.<br>\r\nAudio: Dolby Surround 5.1.<br>\r\nBildformat: 16:9 Wide-Screen.<br>\r\nDauer: (approx) 96 minuten.<br>\r\nAußerdem: Interaktive Menus, Kapitelauswahl, Untertitel.<br><br>\r\n(USA ’95). Von einem gekaperten Zug aus übernimmt Computerspezi Dane die Kontrolle über einen Kampfsatelliten und erpresst das Pentagon. Aber auch Ex-Offizier Ryback (Steven Seagal) ist im Zug ...\r\n','',0);
INSERT INTO products_description VALUES (11,2,'Fire Down Below','Regional Code: 2 (Japan, Europe, Middle East, South Africa).<br>\r\nSprachen: English, Deutsch.<br>\r\nUntertitel: English, Deutsch, Spanish.<br>\r\nAudio: Dolby Surround 5.1.<br>\r\nBildformat: 16:9 Wide-Screen.<br>\r\nDauer: (approx) 96 minuten.<br>\r\nAußerdem: Interaktive Menus, Kapitelauswahl, Untertitel.<br><br>\r\nEin mysteriöser Mordfall führt den Bundesmarschall Jack Taggert in eine Kleinstadt im US-Staat Kentucky. Doch bei seinen Ermittlungen stößt er auf eine Mauer des Schweigens. Angst beherrscht die Stadt, und alle Spuren führen zu dem undurchsichtigen Minen-Tycoon Orin Hanner. Offenbar werden in der friedlichen Berglandschaft gigantische Mengen Giftmülls verschoben, mit unkalkulierbaren Risiken. Um eine Katastrophe zu verhindern, räumt Taggert gnadenlos auf ...','',0);
INSERT INTO products_description VALUES (12,2,'Stirb Langsam - Jetzt Erst Recht','Originaltitel: &quot;Die Hard With A Vengeance&quot;<br><br>\r\nRegional Code: 2 (Japan, Europe, Middle East, South Africa).<br>\r\nSprachen: English, Deutsch.<br>\r\nUntertitel: English, Deutsch, Spanish.<br>\r\nAudio: Dolby Surround 5.1.<br>\r\nBildformat: 16:9 Wide-Screen.<br>\r\nDauer: (approx) 96 minuten.<br>\r\nAußerdem: Interaktive Menus, Kapitelauswahl, Untertitel.<br><br>\r\nSo explosiv, so spannend, so rasant wie nie zuvor: Bruce Willis als Detectiv John McClane in einem Action-Thriller der Superlative! Das ist heute nicht McClanes Tag. Seine Frau hat ihn verlassen, sein Boß hat ihn vom Dienst suspendiert und irgendein Verrückter hat ihn gerade zum Gegenspieler in einem teuflischen Spiel erkoren - und der Einsatz ist New York selbst. Ein Kaufhaus ist explodiert, doch das ist nur der Auftakt. Der geniale Superverbrecher Simon droht, die ganze Stadt Stück für Stück in die Luft zu sprengen, wenn McClane und sein Partner wider Willen seine explosiven\" Rätsel nicht lösen. Eine mörderische Hetzjagd quer durch New York beginnt - bis McClane merkt, daß der Bombenterror eigentlich nur ein brillantes Ablenkungsmanöver ist...!<br><i>\"Perfekt gemacht und stark besetzt. Das Action-Highlight des Jahres!\"</i> <b>(Bild)</b>','',0);
INSERT INTO products_description VALUES (13,2,'Zwei stahlharte Profis','Originaltitel: &quot;Lethal Weapon&quot;<br><br>\r\nRegional Code: 2 (Japan, Europe, Middle East, South Africa).<br>\r\nSprachen: English, Deutsch.<br>\r\nUntertitel: English, Deutsch, Spanish.<br>\r\nAudio: Dolby Surround 5.1.<br>\r\nBildformat: 16:9 Wide-Screen.<br>\r\nDauer: (approx) 96 minuten.<br>\r\nAußerdem: Interaktive Menus, Kapitelauswahl, Untertitel.<br><br>\r\nSie sind beide Cops in L.A.. Sie haben beide in Vietnam für eine Spezialeinheit gekämpft. Und sie hassen es beide, mit einem Partner arbeiten zu müssen. Aber sie sind Partner: Martin Riggs, der Mann mit dem Todeswunsch, für wen auch immer. Und Roger Murtaugh, der besonnene Polizist. Gemeinsam enttarnen sie einen gigantischen Heroinschmuggel, hinter dem sich eine Gruppe ehemaliger CIA-Söldner verbirgt. Eine Killerbande gegen zwei Profis ...','',0);
INSERT INTO products_description VALUES (14,2,'Labyrinth ohne Ausweg','Originaltitel: &quot;Red Corner&quot;<br><br>\r\nRegional Code: 2 (Japan, Europe, Middle East, South Africa).<br>\r\nSprachen: English, Deutsch.<br>\r\nUntertitel: English, Deutsch, Spanish.<br>\r\nAudio: Dolby Surround 5.1.<br>\r\nBildformat: 16:9 Wide-Screen.<br>\r\nDauer: (approx) 96 minuten.<br>\r\nAußerdem: Interaktive Menus, Kapitelauswahl, Untertitel.<br><br>\r\nDem Amerikaner Jack Moore wird in China der bestialische Mord an einem Fotomodel angehängt. Brutale Gefängnisschergen versuchen, ihn durch Folter zu einem Geständnis zu zwingen. Vor Gericht fordert die Anklage die Todesstrafe - Moore\'s Schicksal scheint besiegelt. Durch einen Zufall gelingt es ihm, aus der Haft zu fliehen, doch aus der feindseligen chinesischen Hauptstadt gibt es praktisch kein Entkommen ...','',0);
INSERT INTO products_description VALUES (15,2,'Frantic','Originaltitel: &quot;Frantic&quot;<br><br>\r\nRegional Code: 2 (Japan, Europe, Middle East, South Africa).<br>\r\nSprachen: English, Deutsch.<br>\r\nUntertitel: English, Deutsch, Spanish.<br>\r\nAudio: Dolby Surround 5.1.<br>\r\nBildformat: 16:9 Wide-Screen.<br>\r\nDauer: (approx) 96 minuten.<br>\r\nAußerdem: Interaktive Menus, Kapitelauswahl, Untertitel.<br><br>\r\nEin romantischer Urlaub in Paris, der sich in einen Alptraum verwandelt. Ein Mann auf der verzweifelten Suche nach seiner entführten Frau. Ein düster-bedrohliches Paris, in dem nur ein Mensch Licht in die tödliche Affäre bringen kann.','',0);
INSERT INTO products_description VALUES (16,2,'Mut Zur Wahrheit','Originaltitel: &quot;Courage Under Fire&quot;<br><br>\r\nRegional Code: 2 (Japan, Europe, Middle East, South Africa).<br>\r\nSprachen: English, Deutsch.<br>\r\nUntertitel: English, Deutsch, Spanish.<br>\r\nAudio: Dolby Surround 5.1.<br>\r\nBildformat: 16:9 Wide-Screen.<br>\r\nDauer: (approx) 96 minuten.<br>\r\nAußerdem: Interaktive Menus, Kapitelauswahl, Untertitel.<br><br>\r\nLieutenant Colonel Nathaniel Serling (Denzel Washington) lässt während einer Schlacht im Golfkrieg versehentlich auf einen US-Panzer schießen, dessen Mannschaft dabei ums Leben kommt. Ein Jahr nach diesem Vorfall wird Serling, der mittlerweile nach Washington D.C. versetzt wurde, die Aufgabe übertragen, sich um einen Kandidaten zu kümmern, der während des Krieges starb und dem der höchste militärische Orden zuteil werden soll. Allerdings sind sowohl der Fall und als auch der betreffende Soldat ein politisch heißes Eisen -- Captain Karen Walden (Meg Ryan) ist Amerikas erster weiblicher Soldat, der im Kampf getötet wurde.<br><br>\r\nSerling findet schnell heraus, dass es im Fall des im felsigen Gebiet von Kuwait abgestürzten Rettungshubschraubers Diskrepanzen gibt. In Flashbacks werden von unterschiedlichen Personen verschiedene Versionen von Waldens Taktik, die Soldaten zu retten und den Absturz zu überleben, dargestellt (à la Kurosawas Rashomon). Genau wie in Glory erweist sich Regisseur Edward Zwicks Zusammenstellung von bekannten und unbekannten Schauspielern als die richtige Mischung. Waldens Crew ist besonders überzeugend. Matt Damon als der Sanitäter kommt gut als der leichtfertige Typ rüber, als er Washington seine Geschichte erzählt. Im Kampf ist er ein mit Fehlern behafteter, humorvoller Soldat.<br><br>\r\nDie erstaunlichste Arbeit in Mut zur Wahrheit leistet Lou Diamond Phillips (als der Schütze der Gruppe), dessen Karriere sich auf dem Weg in die direkt für den Videomarkt produzierten Filme befand. Und dann ist da noch Ryan. Sie hat sich in dramatischen Filmen in der Vergangenheit gut behauptet (Eine fast perfekte Liebe, Ein blutiges Erbe), es aber nie geschafft, ihrem Image zu entfliehen, das sie in die Ecke der romantischen Komödie steckte. Mit gefärbtem Haar, einem leichten Akzent und der von ihr geforderten Darstellungskunst hat sie endlich einen langlebigen dramatischen Film. Obwohl sie nur halb so oft wie Washington im Film zu sehen ist, macht ihre mutige und beeindruckend nachhaltige Darstellung Mut zur Wahrheit zu einem speziellen Film bis hin zu dessen seltsamer, aber lohnender letzter Szene.','',0);
INSERT INTO products_description VALUES (17,2,'Speed','Originaltitel: &quot;Speed&quot;<br><br>\r\nRegional Code: 2 (Japan, Europe, Middle East, South Africa).<br>\r\nSprachen: English, Deutsch.<br>\r\nUntertitel: English, Deutsch, Spanish.<br>\r\nAudio: Dolby Surround 5.1.<br>\r\nBildformat: 16:9 Wide-Screen.<br>\r\nDauer: (approx) 96 minuten.<br>\r\nAußerdem: Interaktive Menus, Kapitelauswahl, Untertitel.<br><br>\r\nEr ist ein Cop aus der Anti-Terror-Einheit von Los Angeles. Und so ist der Alarm für Jack Traven nichts Ungewöhnliches: Ein Terrorist will drei Millionen Dollar erpressen, oder die zufälligen Geiseln in einem Aufzug fallen 35 Stockwerke in die Tiefe. Doch Jack schafft das Unmögliche - die Geiseln werden gerettet und der Terrorist stirbt an seiner eigenen Bombe. Scheinbar. Denn schon wenig später steht Jack (Keanu Reeves) dem Bombenexperten Payne erneut gegenüber. Diesmal hat sich der Erpresser eine ganz perfide Mordwaffe ausgedacht: Er plaziert eine Bombe in einem öffentlichen Bus. Der Mechanismus der Sprengladung schaltet sich automatisch ein, sobald der Bus schneller als 50 Meilen in der Stunde fährt und detoniert sofort, sobald die Geschwindigkeit sinkt. Plötzlich wird für eine Handvoll ahnungsloser Durchschnittsbürger der Weg zur Arbeit zum Höllentrip - und nur Jack hat ihr Leben in der Hand. Als der Busfahrer verletzt wird, übernimmt Fahrgast Annie (Sandra Bullock) das Steuer. Doch wohin mit einem Bus, der nicht bremsen kann in der Stadt der Staus? Doch es kommt noch schlimmer: Payne (Dennis Hopper) will jetzt nicht nur seine drei Millionen Dollar. Er will Jack. Um jeden Preis.','',0);
INSERT INTO products_description VALUES (18,2,'Speed 2: Cruise Control','Originaltitel: &quot;Speed 2 - Cruise Control&quot;<br><br>\r\nRegional Code: 2 (Japan, Europe, Middle East, South Africa).<br>\r\nSprachen: English, Deutsch.<br>\r\nUntertitel: English, Deutsch, Spanish.<br>\r\nAudio: Dolby Surround 5.1.<br>\r\nBildformat: 16:9 Wide-Screen.<br>\r\nDauer: (approx) 96 minuten.<br>\r\nAußerdem: Interaktive Menus, Kapitelauswahl, Untertitel.<br><br>\r\nHalten Sie ihre Schwimmwesten bereit, denn die actiongeladene Fortsetzung von Speed begibt sich auf Hochseekurs. Erleben Sie Sandra Bullock erneut in ihrer Star-Rolle als Annie Porter. Die Karibik-Kreuzfahrt mit ihrem Freund Alex entwickelt sich unaufhaltsam zur rasenden Todesfahrt, als ein wahnsinniger Computer-Spezialist den Luxusliner in seine Gewalt bringt und auf einen mörderischen Zerstörungskurs programmiert. Eine hochexplosive Reise, bei der kein geringerer als Action-Spezialist Jan De Bont das Ruder in die Hand nimmt. Speed 2: Cruise Controll läßt ihre Adrenalin-Wellen in rasender Geschwindigkeit ganz nach oben schnellen.','',0);
INSERT INTO products_description VALUES (19,2,'Verrückt nach Mary','Originaltitel: &quot;There\'s Something About Mary&quot;<br><br>\r\nRegional Code: 2 (Japan, Europe, Middle East, South Africa).<br>\r\nSprachen: English, Deutsch.<br>\r\nUntertitel: English, Deutsch, Spanish.<br>\r\nAudio: Dolby Surround 5.1.<br>\r\nBildformat: 16:9 Wide-Screen.<br>\r\nDauer: (approx) 96 minuten.<br>\r\nAußerdem: Interaktive Menus, Kapitelauswahl, Untertitel.<br><br>\r\n13 Jahre nachdem Teds Rendezvous mit seiner angebeteten Mary in einem peinlichen Fiasko endete, träumt er immer noch von ihr und engagiert den windigen Privatdetektiv Healy um sie aufzuspüren. Der findet Mary in Florida und verliebt sich auf den ersten Blick in die atemberaubende Traumfrau. Um Ted als Nebenbuhler auszuschalten, tischt er ihm dicke Lügen über Mary auf. Ted läßt sich jedoch nicht abschrecken, eilt nach Miami und versucht nun alles, um Healy die Balztour zu vermasseln. Doch nicht nur Healy ist verrückt nach Mary und Ted bekommt es mit einer ganzen Legion liebeskranker Konkurrenten zu tun ...','',0);
INSERT INTO products_description VALUES (20,2,'Menschenkind','Originaltitel: &quot;Beloved&quot;<br><br>\r\nRegional Code: 2 (Japan, Europe, Middle East, South Africa).<br>\r\nSprachen: English, Deutsch.<br>\r\nUntertitel: English, Deutsch, Spanish.<br>\r\nAudio: Dolby Surround 5.1.<br>\r\nBildformat: 16:9 Wide-Screen.<br>\r\nDauer: (approx) 96 minuten.<br>\r\nAußerdem: Interaktive Menus, Kapitelauswahl, Untertitel.<br><br>\r\nDieser vielschichtige Film ist eine Arbeit, die Regisseur Jonathan Demme und dem amerikanischen Talkshow-Star Oprah Winfrey sehr am Herzen lag. Der Film deckt im Verlauf seiner dreistündigen Spielzeit viele Bereiche ab. Menschenkind ist teils Sklavenepos, teils Mutter-Tochter-Drama und teils Geistergeschichte.<br><br>\r\nDer Film fordert vom Publikum höchste Aufmerksamkeit, angefangen bei seiner dramatischen und etwas verwirrenden Eingangssequenz, in der die Bewohner eines Hauses von einem niederträchtigen übersinnlichen Angriff heimgesucht werden. Aber Demme und seine talentierte Besetzung bereiten denen, die dabei bleiben ein unvergessliches Erlebnis. Der Film folgt den Spuren von Sethe (in ihren mittleren Jahren von Oprah Winfrey dargestellt), einer ehemaligen Sklavin, die sich scheinbar ein friedliches und produktives Leben in Ohio aufgebaut hat. Aber durch den erschreckenden und sparsamen Einsatz von Rückblenden deckt Demme, genau wie das literarische Meisterwerk von Toni Morrison, auf dem der Film basiert, langsam die Schrecken von Sethes früherem Leben auf und das schreckliche Ereignis, dass dazu führte, dass Sethes Haus von Geistern heimgesucht wird.<br><br>\r\nWährend die Gräuel der Sklaverei und das blutige Ereignis in Sethes Familie unleugbar tief beeindrucken, ist die Qualität des Film auch in kleineren, genauso befriedigenden Details sichtbar. Die geistlich beeinflusste Musik von Rachel Portman ist gleichzeitig befreiend und bedrückend, und der Einblick in die afro-amerikanische Gemeinschaft nach der Sklaverei -- am Beispiel eines Familienausflugs zu einem Jahrmarkt, oder dem gospelsingenden Nähkränzchen -- machen diesen Film zu einem speziellen Vergnügen sowohl für den Geist als auch für das Herz. Die Schauspieler, besonders Kimberley Elise als Sethes kämpfende Tochter und Thandie Newton als der mysteriöse Titelcharakter, sind sehr rührend. Achten Sie auch auf Danny Glover (Lethal Weapon) als Paul D.','',0);
INSERT INTO products_description VALUES (21,2,'SWAT 3: Elite Edition','<b>KEINE KOMPROMISSE!</b><br><i>Kämpfen Sie Seite an Seite mit Ihren LAPD SWAT-Kameraden gegen das organisierte Verbrechen!</i><br><br>\r\nEine der realistischsten 3D-Taktiksimulationen der letzten Zeit jetzt mit Multiplayer-Modus, 5 neuen Missionen und jede Menge nützliche Tools!<br><br>\r\nLos Angeles, 2005. In wenigen Tagen steht die Unterzeichnung des Abkommens der Vereinten Nationen zur Atom-Ächtung durch Vertreter aller Nationen der Welt an. Radikale terroristische Vereinigungen machen sich in der ganzen Stadt breit. Verantwortlich für die Sicherheit der Delegierten zeichnet sich eine Eliteeinheit der LAPD: das SWAT-Team. Das Schicksal der Stadt liegt in Ihren Händen.<br><br>\r\n<b>Neue Features:</b>\r\n<ul>\r\n<li>Multiplayer-Modus (Co op-Modus, Deathmatch in den bekannten Variationen)</li>\r\n<li>5 neue Missionen an original Örtlichkeiten Las (U-Bahn, Whitman Airport, etc.)</li>\r\n<li>neue Charakter</li>\r\n<li>neue Skins</li>\r\n<li>neue Waffen</li>\r\n<li>neue Sounds</li>\r\n<li>verbesserte KI</li>\r\n<li>Tools-Package</li>\r\n<li>Scenario-Editor</li>\r\n<li>Level-Editor</li>\r\n</ul>\r\nDie dritte Folge der Bestseller-Reihe im Bereich der 3D-Echtzeit-Simulationen präsentiert sich mit einer neuen Spielengine mit extrem ausgeprägtem Realismusgrad. Der Spieler übernimmt das Kommando über eine der besten Polizei-Spezialeinheiten oder einer der übelsten Terroristen-Gangs der Welt. Er durchläuft - als \"Guter\" oder \"Böser\" - eine der härtesten und elitärsten Kampfausbildungen, in der er lernt, mit jeder Art von Krisensituationen umzugehen. Bei diesem Action-Abenteuer geht es um das Leben prominenter Vertreter der Vereinten Nationen und bei 16 Missionen an Originalschauplätzen in LA gibt die \"lebensechte\" KI den Protagonisten jeder Seite so einige harte Nüsse zu knacken.','www.swat3.com',0);
INSERT INTO products_description VALUES (22,2,'Unreal Tournament','2341: Die Gewalt ist eine Lebensweise, die sich ihren Weg durch die dunklen Risse der Gesellschaft bahnt. Sie bedroht die Macht und den Einfluss der regierenden Firmen, die schnellstens ein Mittel finden müssen, die tobenden Massen zu besänftigen - ein profitables Mittel ... Gladiatorenkämpfe sind die Lösung. Sie sollen den Durst der Menschen nach Blut stillen und sind die perfekte Gelegenheit, die Aufständischen, Kriminellen und Aliens zu beseitigen, die die Firmenelite bedrohen.<br><br>\r\nDas Turnier war geboren - ein Kampf auf Leben und Tod. Galaxisweit live und in Farbe! Kämpfen Sie für Freiheit, Ruhm und Ehre. Sie müssen stark, schnell und geschickt sein ... oder Sie bleiben auf der Strecke.<br><br>\r\nKämpfen Sie allein oder kommandieren Sie ein Team gegen Armeen unbarmherziger Krieger, die alle nur ein Ziel vor Augen haben: Die Arenen lebend zu verlassen und sich dem Grand Champion zu stellen ... um ihn dann zu bezwingen!<br><br>\r\n<b>Features:</b>\r\n<ul>\r\n<li>Auf dem PC mehrfach als Spiel des Jahres ausgezeichnet!</li>\r\n<li>Mehr als 50 faszinierende Level - verlassene Raumstationen, gotische Kathedralen und graffitibedeckte Großstädte.</li>\r\n<li>Vier actionreiche Spielmodi - Deathmatch, Capture the Flag, Assault und Domination werden Ihren Adrenalinpegel in die Höhe schnellen lassen.</li>\r\n<li>Dramatische Mehrspieler-Kämpfe mit 2, 3 und 4 Spielern, auch über das Netzwerk</li>\r\n<li>Gnadenlos aggressive Computergegner verlangen Ihnen das Äußerste ab.</li>\r\n<li>Neuartiges Benutzerinterface und verbesserte Steuerung - auch mit USB-Maus und -Tastatur spielbar.</li>\r\n</ul>\r\nDer Nachfolger des Actionhits \"Unreal\" verspricht ein leichtes, intuitives Interface, um auch Einsteigern schnellen Zugang zu den Duellen gegen die Bots zu ermöglichen. Mit diesen KI-Kriegern kann man auch Teams bilden und im umfangreichen Multiplayermodus ohne Onlinekosten in den Kampf ziehen. 35 komplett neue Arenen und das erweiterte Waffenangebot bilden dazu den würdigen Rahmen.','www.unrealtournament.net',0);
INSERT INTO products_description VALUES (23,2,'The Wheel Of Time','<b><i>\"Wheel Of Time\"(Das Rad der Zeit)</i></b> basiert auf den Fantasy-Romanen von Kultautor Robert Jordan und stellt einen einzigartigen Mix aus Strategie-, Action- und Rollenspielelementen dar. Obwohl die Welt von \"Wheel of Time\" eng an die literarische Vorlage der Romane angelehnt ist, erzählt das Spiel keine lineare Geschichte. Die Story entwickelt sich abhängig von den Aktionen der Spieler, die jeweils die Rollen der Hauptcharaktere aus dem Roman übernehmen. Jede Figur hat den Oberbefehl über eine große Gefolgschaft, militärische Einheiten und Ländereien. Die Spieler können ihre eigenen Festungen konstruieren, individuell ausbauen, von dort aus das umliegende Land erforschen, magische Gegenstände sammeln oder die gegnerischen Zitadellen erstürmen. Selbstverständlich kann man sich auch über LAN oder Internet gegenseitig Truppen auf den Hals hetzen und die Festungen seiner Mitspieler in Schutt und Asche legen. Dreh- und Anlegepunkt von \"Wheel of Time\" ist der Kampf um die finstere Macht \"The Dark One\", die vor langer Zeit die Menschheit beinahe ins Verderben stürzte und nur mit Hilfe gewaltiger magischer Energie verbannt werden konnte. \"The Amyrlin Seat\" und \"The Children of the Night\" kämpfen nur gegen \"The Forsaken\" und \"The Hound\" um den Besitz des Schlüssels zu \"Shayol Ghul\" - dem magischen Siegel, mit dessen Hilfe \"The Dark One\" seinerzeit gebannt werden konnte.<br><br>\r\n<b>Features:</b> \r\n<ul>\r\n<li>Ego-Shooter mit Strategie-Elementen</li>\r\n<li>Spielumgebung in Echtzeit-3D</li>\r\n<li>Konstruktion aud Ausbau der eigenen Festung</li>\r\n<li>Einsatz von über 100 Artefakten und Zaubersprüchen</li>\r\n<li>Single- und Multiplayermodus</li>\r\n</ul>\r\nIm Mittelpunkt steht der Kampf gegen eine finstere Macht namens The Dark One. Deren Schergen müssen mit dem Einsatz von über 100 Artefakten und Zaubereien wie Blitzschlag oder Teleportation aus dem Weg geräumt werden. Die opulente 3D-Grafik verbindet Strategie- und Rollenspielelemente. \r\n\r\n<b>Voraussetzungen</b>\r\nmind. P200, 32MB RAM, 4x CD-Rom, Win95/98, DirectX 5.0 komp.Grafikkarte und Soundkarte. ','www.wheeloftime.com',0);
INSERT INTO products_description VALUES (24,2,'Disciples: Sacred Land','Rundenbasierende Fantasy/RTS-Strategie mit gutem Design (vor allem die Levels, 4 versch. Rassen, tolle Einheiten), fantastischer Atmosphäre und exzellentem Soundtrack. Grafisch leider auf das Niveau von 1990.','www.strategyfirst.com/disciples/welcome.html',0);
INSERT INTO products_description VALUES (25,2,'Microsoft Internet Tastatur PS/2','<i>Microsoft Internet Keyboard,Windows-Tastatur mit 10 zusätzl. Tasten,2 selbst programmierbar, abnehmbareHandgelenkauflage. Treiber im Lieferumfang.</i><br><br>\r\nEin-Klick-Zugriff auf das Internet und vieles mehr! Das Internet Keyboard verfügt über 10 zusätzliche Abkürzungstasten auf einer benutzerfreundlichen Standardtastatur, die darüber hinaus eine abnehmbare Handballenauflage aufweist. Über die Abkürzungstasten können Sie durch das Internet surfen oder direkt von der Tastatur aus auf E-Mails zugreifen. Die IntelliType Pro-Software ermöglicht außerdem das individuelle Belegen der Tasten.','',0);
INSERT INTO products_description VALUES (26,2,'Microsof IntelliMouse Explorer','Die IntelliMouse Explorer überzeugt durch ihr modernes Design mit silberfarbenem Gehäuse, sowie rot schimmernder Unter- und Rückseite. Die neuartige IntelliEye-Technologie sorgt für eine noch nie dagewesene Präzision, da statt der beweglichen Teile (zum Abtasten der Bewegungsänderungen an der Unterseite der Maus) ein optischer Sensor die Bewegungen der Maus erfaßt. Das Herzstück der Microsoft IntelliEye-Technologie ist ein kleiner Chip, der einen optischen Sensor und einen digitalen Signalprozessor (DSP) enthält.<br><br>\r\nDa auf bewegliche Teile, die Staub, Schmutz und Fett aufnehmen können, verzichtet wurde, muß die IntelliMouse Explorer nicht mehr gereinigt werden. Darüber hinaus arbeitet die IntelliMouse Explorer auf nahezu jeder Arbeitsoberfläche, so daß kein Mauspad mehr erforderlich ist. Mit dem Rad und zwei neuen zusätzlichen Maustasten ermöglicht sie effizientes und komfortables Arbeiten am PC.<br><br>\r\n<b>Eigenschaften:</b>\r\n<ul>\r\n<li><b>ANSCHLUSS:</b> USB (PS/2-Adapter enthalten)</li>\r\n<li><b>FARBE:</b> metallic-grau</li>\r\n<li><b>TECHNIK:</b> Optisch (Akt.: ca. 1500 Bilder/s)</li>\r\n<li><b>TASTEN:</b> 5 (incl. Scrollrad)</li>\r\n<li><b>SCROLLRAD:</b> Ja</li>\r\n</ul>\r\n<i><b>BEMERKUNG:</b><br>Keine Reinigung bewegter Teile mehr notwendig, da statt der Mauskugel ein Fotoempfänger benutzt wird.</i>','',0);
INSERT INTO products_description VALUES (27,2,'Hewlett-Packard LaserJet 1100Xi','<b>HP LaserJet für mehr Produktivität und Flexibilität am Arbeitsplatz</b><br><br>\r\nDer HP LaserJet 1100Xi Drucker verbindet exzellente Laserdruckqualität mit hoher Geschwindigkeit für mehr Effizienz.<br><br>\r\n<b>Zielkunden</b>\r\n<ul><li>Einzelanwender, die Wert auf professionelle Ausdrucke in Laserqualität legen und schnelle Ergebnisse auch bei komplexen Dokumenten erwarten.</li>\r\n<li>Der HP LaserJet 1100 sorgt mit gestochen scharfen Texten und Grafiken für ein professionelles Erscheinungsbild Ihrer Arbeit und Ihres Unternehmens. Selbst bei komplexen Dokumenten liefert er schnelle Ergebnisse. Andere Medien - wie z.B. Transparentfolien und Briefumschläge, etc. - lassen sich problemlos bedrucken. Somit ist der HP LaserJet 1100 ein Multifunktionstalent im Büroalltag.</li>\r\n</ul>\r\n<b>Eigenschaften</b>\r\n<ul>\r\n<li><b>Druckqualität</b> Schwarzweiß: 600 x 600 dpi</li>\r\n<li><b>Monatliche Druckleistung</b> Bis zu 7000 Seiten</li>\r\n<li><b>Speicher</b> 2 MB Standardspeicher, erweiterbar auf 18 MB</li>\r\n<li><b>Schnittstelle/gemeinsame Nutzung</b> Parallel, IEEE 1284-kompatibel</li>\r\n<li><b>Softwarekompatibilität</b> DOS/Win 3.1x/9x/NT 4.0</li>\r\n<li><b>Papierzuführung</b> 125-Blatt-Papierzuführung</li>\r\n<li><b>Druckmedien</b> Normalpapier, Briefumschläge, Transparentfolien, kartoniertes Papier, Postkarten und Etiketten</li>\r\n<li><b>Netzwerkfähig</b> Über HP JetDirect PrintServer</li>\r\n<li><b>Lieferumfang</b> HP LaserJet 1100Xi Drucker (Lieferumfang: Drucker, Tonerkassette, 2 m Parallelkabel, Netzkabel, Kurzbedienungsanleitung, Benutzerhandbuch, CD-ROM, 3,5\"-Disketten mit Windows® 3.1x, 9x, NT 4.0 Treibern und DOS-Dienstprogrammen)</li>\r\n<li><b>Gewährleistung</b> Ein Jahr</li>\r\n</ul>\r\n','www.hp.com',0);
INSERT INTO products_description VALUES (1,3,'Matrox G200 MMS','Reinforcing its position as a multi-monitor trailblazer, Matrox Graphics Inc. has once again developed the most flexible and highly advanced solution in the industry. Introducing the new Matrox G200 Multi-Monitor Series; the first graphics card ever to support up to four DVI digital flat panel displays on a single 8&quot; PCI board.<br><br>With continuing demand for digital flat panels in the financial workplace, the Matrox G200 MMS is the ultimate in flexible solutions. The Matrox G200 MMS also supports the new digital video interface (DVI) created by the Digital Display Working Group (DDWG) designed to ease the adoption of digital flat panels. Other configurations include composite video capture ability and onboard TV tuner, making the Matrox G200 MMS the complete solution for business needs.<br><br>Based on the award-winning MGA-G200 graphics chip, the Matrox G200 Multi-Monitor Series provides superior 2D/3D graphics acceleration to meet the demanding needs of business applications such as real-time stock quotes (Versus), live video feeds (Reuters & Bloombergs), multiple windows applications, word processing, spreadsheets and CAD.','www.matrox.com/mga/products/g200_mms/home.cfm',0);
INSERT INTO products_description VALUES (2,3,'Matrox G400 32MB','<b>Dramatically Different High Performance Graphics</b><br><br>Introducing the Millennium G400 Series - a dramatically different, high performance graphics experience. Armed with the industry\'s fastest graphics chip, the Millennium G400 Series takes explosive acceleration two steps further by adding unprecedented image quality, along with the most versatile display options for all your 3D, 2D and DVD applications. As the most powerful and innovative tools in your PC\'s arsenal, the Millennium G400 Series will not only change the way you see graphics, but will revolutionize the way you use your computer.<br><br><b>Key features:</b><ul><li>New Matrox G400 256-bit DualBus graphics chip</li><li>Explosive 3D, 2D and DVD performance</li><li>DualHead Display</li><li>Superior DVD and TV output</li><li>3D Environment-Mapped Bump Mapping</li><li>Vibrant Color Quality rendering </li><li>UltraSharp DAC of up to 360 MHz</li><li>3D Rendering Array Processor</li><li>Support for 16 or 32 MB of memory</li></ul>','www.matrox.com/mga/products/mill_g400/home.htm',0);
INSERT INTO products_description VALUES (3,3,'Microsoft IntelliMouse Pro','Every element of IntelliMouse Pro - from its unique arched shape to the texture of the rubber grip around its base - is the product of extensive customer and ergonomic research. Microsoft\'s popular wheel control, which now allows zooming and universal scrolling functions, gives IntelliMouse Pro outstanding comfort and efficiency.','www.microsoft.com/hardware/mouse/intellimouse.asp',0);
INSERT INTO products_description VALUES (4,3,'The Replacement Killers','Regional Code: 2 (Japan, Europe, Middle East, South Africa).<br>Languages: English, Deutsch.<br>Subtitles: English, Deutsch, Spanish.<br>Audio: Dolby Surround 5.1.<br>Picture Format: 16:9 Wide-Screen.<br>Length: (approx) 80 minutes.<br>Other: Interactive Menus, Chapter Selection, Subtitles (more languages).','www.replacement-killers.com',0);
INSERT INTO products_description VALUES (5,3,'Blade Runner - Director\'s Cut','Regional Code: 2 (Japan, Europe, Middle East, South Africa).<br>Languages: English, Deutsch.<br>Subtitles: English, Deutsch, Spanish.<br>Audio: Dolby Surround 5.1.<br>Picture Format: 16:9 Wide-Screen.<br>Length: (approx) 112 minutes.<br>Other: Interactive Menus, Chapter Selection, Subtitles (more languages).','www.bladerunner.com',0);
INSERT INTO products_description VALUES (6,3,'The Matrix','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch.\r<br>\nAudio: Dolby Surround.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 131 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Making Of.','www.thematrix.com',0);
INSERT INTO products_description VALUES (7,3,'You\'ve Got Mail','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch, Spanish.\r<br>\nSubtitles: English, Deutsch, Spanish, French, Nordic, Polish.\r<br>\nAudio: Dolby Digital 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 115 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','www.youvegotmail.com',0);
INSERT INTO products_description VALUES (8,3,'A Bug\'s Life','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Digital 5.1 / Dobly Surround Stereo.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 91 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','www.abugslife.com',0);
INSERT INTO products_description VALUES (9,3,'Under Siege','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 98 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','',0);
INSERT INTO products_description VALUES (10,3,'Under Siege 2 - Dark Territory','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 98 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','',0);
INSERT INTO products_description VALUES (11,3,'Fire Down Below','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 100 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','',0);
INSERT INTO products_description VALUES (12,3,'Die Hard With A Vengeance','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 122 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','',0);
INSERT INTO products_description VALUES (13,3,'Lethal Weapon','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 100 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','',0);
INSERT INTO products_description VALUES (14,3,'Red Corner','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 117 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','',0);
INSERT INTO products_description VALUES (15,3,'Frantic','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 115 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','',0);
INSERT INTO products_description VALUES (16,3,'Courage Under Fire','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 112 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','',0);
INSERT INTO products_description VALUES (17,3,'Speed','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 112 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','',0);
INSERT INTO products_description VALUES (18,3,'Speed 2: Cruise Control','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 120 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','',0);
INSERT INTO products_description VALUES (19,3,'There\'s Something About Mary','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 114 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','',0);
INSERT INTO products_description VALUES (20,3,'Beloved','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br>\nLanguages: English, Deutsch.\r<br>\nSubtitles: English, Deutsch, Spanish.\r<br>\nAudio: Dolby Surround 5.1.\r<br>\nPicture Format: 16:9 Wide-Screen.\r<br>\nLength: (approx) 164 minutes.\r<br>\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','',0);
INSERT INTO products_description VALUES (21,3,'SWAT 3: Close Quarters Battle','<b>Windows 95/98</b><br><br>211 in progress with shots fired. Officer down. Armed suspects with hostages. Respond Code 3! Los Angles, 2005, In the next seven days, representatives from every nation around the world will converge on Las Angles to witness the signing of the United Nations Nuclear Abolishment Treaty. The protection of these dignitaries falls on the shoulders of one organization, LAPD SWAT. As part of this elite tactical organization, you and your team have the weapons and all the training necessary to protect, to serve, and \"When needed\" to use deadly force to keep the peace. It takes more than weapons to make it through each mission. Your arsenal includes C2 charges, flashbangs, tactical grenades. opti-Wand mini-video cameras, and other devices critical to meeting your objectives and keeping your men free of injury. Uncompromised Duty, Honor and Valor!','www.swat3.com',0);
INSERT INTO products_description VALUES (22,3,'Unreal Tournament','From the creators of the best-selling Unreal, comes Unreal Tournament. A new kind of single player experience. A ruthless multiplayer revolution.<br><br>This stand-alone game showcases completely new team-based gameplay, groundbreaking multi-faceted single player action or dynamic multi-player mayhem. It\'s a fight to the finish for the title of Unreal Grand Master in the gladiatorial arena. A single player experience like no other! Guide your team of \'bots\' (virtual teamates) against the hardest criminals in the galaxy for the ultimate title - the Unreal Grand Master.','www.unrealtournament.net',0);
INSERT INTO products_description VALUES (23,3,'The Wheel Of Time','The world in which The Wheel of Time takes place is lifted directly out of Jordan\'s pages; it\'s huge and consists of many different environments. How you navigate the world will depend largely on which game - single player or multipayer - you\'re playing. The single player experience, with a few exceptions, will see Elayna traversing the world mainly by foot (with a couple notable exceptions). In the multiplayer experience, your character will have more access to travel via Ter\'angreal, Portal Stones, and the Ways. However you move around, though, you\'ll quickly discover that means of locomotion can easily become the least of the your worries...<br><br>During your travels, you quickly discover that four locations are crucial to your success in the game. Not surprisingly, these locations are the homes of The Wheel of Time\'s main characters. Some of these places are ripped directly from the pages of Jordan\'s books, made flesh with Legend\'s unparalleled pixel-pushing ways. Other places are specific to the game, conceived and executed with the intent of expanding this game world even further. Either way, they provide a backdrop for some of the most intense first person action and strategy you\'ll have this year.','www.wheeloftime.com',0);
INSERT INTO products_description VALUES (24,3,'Disciples: Sacred Lands','A new age is dawning...<br><br>Enter the realm of the Sacred Lands, where the dawn of a New Age has set in motion the most momentous of wars. As the prophecies long foretold, four races now clash with swords and sorcery in a desperate bid to control the destiny of their gods. Take on the quest as a champion of the Empire, the Mountain Clans, the Legions of the Damned, or the Undead Hordes and test your faith in battles of brute force, spellbinding magic and acts of guile. Slay demons, vanquish giants and combat merciless forces of the dead and undead. But to ensure the salvation of your god, the hero within must evolve.<br><br>The day of reckoning has come... and only the chosen will survive.','',0);
INSERT INTO products_description VALUES (25,3,'Microsoft Internet Keyboard PS/2','The Internet Keyboard has 10 Hot Keys on a comfortable standard keyboard design that also includes a detachable palm rest. The Hot Keys allow you to browse the web, or check e-mail directly from your keyboard. The IntelliType Pro software also allows you to customize your hot keys - make the Internet Keyboard work the way you want it to!','',0);
INSERT INTO products_description VALUES (26,3,'Microsoft IntelliMouse Explorer','Microsoft introduces its most advanced mouse, the IntelliMouse Explorer! IntelliMouse Explorer features a sleek design, an industrial-silver finish, a glowing red underside and taillight, creating a style and look unlike any other mouse. IntelliMouse Explorer combines the accuracy and reliability of Microsoft IntelliEye optical tracking technology, the convenience of two new customizable function buttons, the efficiency of the scrolling wheel and the comfort of expert ergonomic design. All these great features make this the best mouse for the PC!','www.microsoft.com/hardware/mouse/explorer.asp',0);
INSERT INTO products_description VALUES (27,3,'Hewlett Packard LaserJet 1100Xi','HP has always set the pace in laser printing technology. The new generation HP LaserJet 1100 series sets another impressive pace, delivering a stunning 8 pages per minute print speed. The 600 dpi print resolution with HP\'s Resolution Enhancement technology (REt) makes every document more professional.<br><br>Enhanced print speed and laser quality results are just the beginning. With 2MB standard memory, HP LaserJet 1100xi users will be able to print increasingly complex pages. Memory can be increased to 18MB to tackle even more complex documents with ease. The HP LaserJet 1100xi supports key operating systems including Windows 3.1, 3.11, 95, 98, NT 4.0, OS/2 and DOS. Network compatibility available via the optional HP JetDirect External Print Servers.<br><br>HP LaserJet 1100xi also features The Document Builder for the Web Era from Trellix Corp. (featuring software to create Web documents).','www.pandi.hp.com/pandi-db/prodinfo.main?product=laserjet1100',0);

INSERT INTO products_attributes VALUES (1,1,4,1,0.00,'+');
INSERT INTO products_attributes VALUES (2,1,4,2,50.00,'+');
INSERT INTO products_attributes VALUES (3,1,4,3,70.00,'+');
INSERT INTO products_attributes VALUES (4,1,3,5,0.00,'+');
INSERT INTO products_attributes VALUES (5,1,3,6,100.00,'+');
INSERT INTO products_attributes VALUES (6,2,4,3,10.00,'-');
INSERT INTO products_attributes VALUES (7,2,4,4,0.00,'+');
INSERT INTO products_attributes VALUES (8,2,3,6,0.00,'+');
INSERT INTO products_attributes VALUES (9,2,3,7,120.00,'+');
INSERT INTO products_attributes VALUES (10,26,3,8,0.00,'+');
INSERT INTO products_attributes VALUES (11,26,3,9,6.00,'+');
INSERT INTO products_attributes VALUES (26, 22, 5, 10, '0.00', '+');
INSERT INTO products_attributes VALUES (27, 22, 5, 13, '0.00', '+');

INSERT INTO products_attributes_download VALUES (26, 'unreal.zip', 7, 3);

INSERT INTO products_options VALUES (1,1,'Color');
INSERT INTO products_options VALUES (2,1,'Size');
INSERT INTO products_options VALUES (3,1,'Model');
INSERT INTO products_options VALUES (4,1,'Memory');
INSERT INTO products_options VALUES (1,2,'Farbe');
INSERT INTO products_options VALUES (2,2,'Größe');
INSERT INTO products_options VALUES (3,2,'Modell');
INSERT INTO products_options VALUES (4,2,'Speicher');
INSERT INTO products_options VALUES (1,3,'Color');
INSERT INTO products_options VALUES (2,3,'Talla');
INSERT INTO products_options VALUES (3,3,'Modelo');
INSERT INTO products_options VALUES (4,3,'Memoria');
INSERT INTO products_options VALUES (5, 3, 'Version');
INSERT INTO products_options VALUES (5, 2, 'Version');
INSERT INTO products_options VALUES (5, 1, 'Version');


INSERT INTO products_options_values VALUES (1,1,'4 mb');
INSERT INTO products_options_values VALUES (2,1,'8 mb');
INSERT INTO products_options_values VALUES (3,1,'16 mb');
INSERT INTO products_options_values VALUES (4,1,'32 mb');
INSERT INTO products_options_values VALUES (5,1,'Value');
INSERT INTO products_options_values VALUES (6,1,'Premium');
INSERT INTO products_options_values VALUES (7,1,'Deluxe');
INSERT INTO products_options_values VALUES (8,1,'PS/2');
INSERT INTO products_options_values VALUES (9,1,'USB');
INSERT INTO products_options_values VALUES (1,2,'4 MB');
INSERT INTO products_options_values VALUES (2,2,'8 MB');
INSERT INTO products_options_values VALUES (3,2,'16 MB');
INSERT INTO products_options_values VALUES (4,2,'32 MB');
INSERT INTO products_options_values VALUES (5,2,'Value Ausgabe');
INSERT INTO products_options_values VALUES (6,2,'Premium Ausgabe');
INSERT INTO products_options_values VALUES (7,2,'Deluxe Ausgabe');
INSERT INTO products_options_values VALUES (8,2,'PS/2 Anschluss');
INSERT INTO products_options_values VALUES (9,2,'USB Anschluss');
INSERT INTO products_options_values VALUES (1,3,'4 mb');
INSERT INTO products_options_values VALUES (2,3,'8 mb');
INSERT INTO products_options_values VALUES (3,3,'16 mb');
INSERT INTO products_options_values VALUES (4,3,'32 mb');
INSERT INTO products_options_values VALUES (5,3,'Value');
INSERT INTO products_options_values VALUES (6,3,'Premium');
INSERT INTO products_options_values VALUES (7,3,'Deluxe');
INSERT INTO products_options_values VALUES (8,3,'PS/2');
INSERT INTO products_options_values VALUES (9,3,'USB');
INSERT INTO products_options_values VALUES (10, 1, 'Download: Windows - English');
INSERT INTO products_options_values VALUES (10, 2, 'Download: Windows - Englisch');
INSERT INTO products_options_values VALUES (10, 3, 'Download: Windows - Inglese');
INSERT INTO products_options_values VALUES (13, 1, 'Box: Windows - English');
INSERT INTO products_options_values VALUES (13, 2, 'Box: Windows - Englisch');
INSERT INTO products_options_values VALUES (13, 3, 'Box: Windows - Inglese');

INSERT INTO products_options_values_to_products_options VALUES (1,4,1);
INSERT INTO products_options_values_to_products_options VALUES (2,4,2);
INSERT INTO products_options_values_to_products_options VALUES (3,4,3);
INSERT INTO products_options_values_to_products_options VALUES (4,4,4);
INSERT INTO products_options_values_to_products_options VALUES (5,3,5);
INSERT INTO products_options_values_to_products_options VALUES (6,3,6);
INSERT INTO products_options_values_to_products_options VALUES (7,3,7);
INSERT INTO products_options_values_to_products_options VALUES (8,3,8);
INSERT INTO products_options_values_to_products_options VALUES (9,3,9);
INSERT INTO products_options_values_to_products_options VALUES (10, 5, 10);
INSERT INTO products_options_values_to_products_options VALUES (13, 5, 13);

INSERT INTO products_to_categories VALUES (1,4);
INSERT INTO products_to_categories VALUES (2,4);
INSERT INTO products_to_categories VALUES (3,9);
INSERT INTO products_to_categories VALUES (4,10);
INSERT INTO products_to_categories VALUES (5,11);
INSERT INTO products_to_categories VALUES (6,10);
INSERT INTO products_to_categories VALUES (7,12);
INSERT INTO products_to_categories VALUES (8,13);
INSERT INTO products_to_categories VALUES (9,10);
INSERT INTO products_to_categories VALUES (10,10);
INSERT INTO products_to_categories VALUES (11,10);
INSERT INTO products_to_categories VALUES (12,10);
INSERT INTO products_to_categories VALUES (13,10);
INSERT INTO products_to_categories VALUES (14,15);
INSERT INTO products_to_categories VALUES (15,14);
INSERT INTO products_to_categories VALUES (16,15);
INSERT INTO products_to_categories VALUES (17,10);
INSERT INTO products_to_categories VALUES (18,10);
INSERT INTO products_to_categories VALUES (19,12);
INSERT INTO products_to_categories VALUES (20,15);
INSERT INTO products_to_categories VALUES (21,18);
INSERT INTO products_to_categories VALUES (22,19);
INSERT INTO products_to_categories VALUES (23,20);
INSERT INTO products_to_categories VALUES (24,20);
INSERT INTO products_to_categories VALUES (25,8);
INSERT INTO products_to_categories VALUES (26,9);
INSERT INTO products_to_categories VALUES (27,5);

INSERT INTO reviews VALUES (1,19,1,'John doe',5, now(),null,0);

INSERT INTO reviews_description VALUES (1,1, 'this has to be one of the funniest movies released for 1999!');

INSERT INTO specials VALUES (1,3, 39.99, now(), null, null, null, '1');
INSERT INTO specials VALUES (2,5, 30.00, now(), null, null, null, '1');
INSERT INTO specials VALUES (3,6, 30.00, now(), null, null, null, '1');
INSERT INTO specials VALUES (4,16, 29.99, now(), null, null, null, '1');

INSERT INTO tax_class VALUES (1, 'Taxable Goods', 'The following types of products are included non-food, services, etc', now(), now());

# USA/Florida
INSERT INTO tax_rates VALUES (1, 1, 1, 1, 7.0, 'FL TAX 7.0%', now(), now());
INSERT INTO geo_zones (geo_zone_id,geo_zone_name,geo_zone_description,date_added) VALUES (1,"Florida","Florida local sales tax zone",now());
INSERT INTO zones_to_geo_zones (association_id,zone_country_id,zone_id,geo_zone_id,date_added) VALUES (1,223,18,1,now());

# USA
INSERT INTO zones VALUES (1,223,'AL','Alabama');
INSERT INTO zones VALUES (2,223,'AK','Alaska');
INSERT INTO zones VALUES (3,223,'AS','American Samoa');
INSERT INTO zones VALUES (4,223,'AZ','Arizona');
INSERT INTO zones VALUES (5,223,'AR','Arkansas');
INSERT INTO zones VALUES (6,223,'AF','Armed Forces Africa');
INSERT INTO zones VALUES (7,223,'AA','Armed Forces Americas');
INSERT INTO zones VALUES (8,223,'AC','Armed Forces Canada');
INSERT INTO zones VALUES (9,223,'AE','Armed Forces Europe');
INSERT INTO zones VALUES (10,223,'AM','Armed Forces Middle East');
INSERT INTO zones VALUES (11,223,'AP','Armed Forces Pacific');
INSERT INTO zones VALUES (12,223,'CA','California');
INSERT INTO zones VALUES (13,223,'CO','Colorado');
INSERT INTO zones VALUES (14,223,'CT','Connecticut');
INSERT INTO zones VALUES (15,223,'DE','Delaware');
INSERT INTO zones VALUES (16,223,'DC','District of Columbia');
INSERT INTO zones VALUES (17,223,'FM','Federated States Of Micronesia');
INSERT INTO zones VALUES (18,223,'FL','Florida');
INSERT INTO zones VALUES (19,223,'GA','Georgia');
INSERT INTO zones VALUES (20,223,'GU','Guam');
INSERT INTO zones VALUES (21,223,'HI','Hawaii');
INSERT INTO zones VALUES (22,223,'ID','Idaho');
INSERT INTO zones VALUES (23,223,'IL','Illinois');
INSERT INTO zones VALUES (24,223,'IN','Indiana');
INSERT INTO zones VALUES (25,223,'IA','Iowa');
INSERT INTO zones VALUES (26,223,'KS','Kansas');
INSERT INTO zones VALUES (27,223,'KY','Kentucky');
INSERT INTO zones VALUES (28,223,'LA','Louisiana');
INSERT INTO zones VALUES (29,223,'ME','Maine');
INSERT INTO zones VALUES (30,223,'MH','Marshall Islands');
INSERT INTO zones VALUES (31,223,'MD','Maryland');
INSERT INTO zones VALUES (32,223,'MA','Massachusetts');
INSERT INTO zones VALUES (33,223,'MI','Michigan');
INSERT INTO zones VALUES (34,223,'MN','Minnesota');
INSERT INTO zones VALUES (35,223,'MS','Mississippi');
INSERT INTO zones VALUES (36,223,'MO','Missouri');
INSERT INTO zones VALUES (37,223,'MT','Montana');
INSERT INTO zones VALUES (38,223,'NE','Nebraska');
INSERT INTO zones VALUES (39,223,'NV','Nevada');
INSERT INTO zones VALUES (40,223,'NH','New Hampshire');
INSERT INTO zones VALUES (41,223,'NJ','New Jersey');
INSERT INTO zones VALUES (42,223,'NM','New Mexico');
INSERT INTO zones VALUES (43,223,'NY','New York');
INSERT INTO zones VALUES (44,223,'NC','North Carolina');
INSERT INTO zones VALUES (45,223,'ND','North Dakota');
INSERT INTO zones VALUES (46,223,'MP','Northern Mariana Islands');
INSERT INTO zones VALUES (47,223,'OH','Ohio');
INSERT INTO zones VALUES (48,223,'OK','Oklahoma');
INSERT INTO zones VALUES (49,223,'OR','Oregon');
INSERT INTO zones VALUES (50,223,'PW','Palau');
INSERT INTO zones VALUES (51,223,'PA','Pennsylvania');
INSERT INTO zones VALUES (52,223,'PR','Puerto Rico');
INSERT INTO zones VALUES (53,223,'RI','Rhode Island');
INSERT INTO zones VALUES (54,223,'SC','South Carolina');
INSERT INTO zones VALUES (55,223,'SD','South Dakota');
INSERT INTO zones VALUES (56,223,'TN','Tennessee');
INSERT INTO zones VALUES (57,223,'TX','Texas');
INSERT INTO zones VALUES (58,223,'UT','Utah');
INSERT INTO zones VALUES (59,223,'VT','Vermont');
INSERT INTO zones VALUES (60,223,'VI','Virgin Islands');
INSERT INTO zones VALUES (61,223,'VA','Virginia');
INSERT INTO zones VALUES (62,223,'WA','Washington');
INSERT INTO zones VALUES (63,223,'WV','West Virginia');
INSERT INTO zones VALUES (64,223,'WI','Wisconsin');
INSERT INTO zones VALUES (65,223,'WY','Wyoming');

# Canada
INSERT INTO zones VALUES (66,38,'AB','Alberta');
INSERT INTO zones VALUES (67,38,'BC','British Columbia');
INSERT INTO zones VALUES (68,38,'MB','Manitoba');
INSERT INTO zones VALUES (69,38,'NF','Newfoundland');
INSERT INTO zones VALUES (70,38,'NB','New Brunswick');
INSERT INTO zones VALUES (71,38,'NS','Nova Scotia');
INSERT INTO zones VALUES (72,38,'NT','Northwest Territories');
INSERT INTO zones VALUES (73,38,'NU','Nunavut');
INSERT INTO zones VALUES (74,38,'ON','Ontario');
INSERT INTO zones VALUES (75,38,'PE','Prince Edward Island');
INSERT INTO zones VALUES (76,38,'QC','Quebec');
INSERT INTO zones VALUES (77,38,'SK','Saskatchewan');
INSERT INTO zones VALUES (78,38,'YT','Yukon Territory');

# Germany
INSERT INTO zones VALUES (79,81,'NDS','Niedersachsen');
INSERT INTO zones VALUES (80,81,'BAW','Baden-Württemberg');
INSERT INTO zones VALUES (81,81,'BAY','Bayern');
INSERT INTO zones VALUES (82,81,'BER','Berlin');
INSERT INTO zones VALUES (83,81,'BRG','Brandenburg');
INSERT INTO zones VALUES (84,81,'BRE','Bremen');
INSERT INTO zones VALUES (85,81,'HAM','Hamburg');
INSERT INTO zones VALUES (86,81,'HES','Hessen');
INSERT INTO zones VALUES (87,81,'MEC','Mecklenburg-Vorpommern');
INSERT INTO zones VALUES (88,81,'NRW','Nordrhein-Westfalen');
INSERT INTO zones VALUES (89,81,'RHE','Rheinland-Pfalz');
INSERT INTO zones VALUES (90,81,'SAR','Saarland');
INSERT INTO zones VALUES (91,81,'SAS','Sachsen');
INSERT INTO zones VALUES (92,81,'SAC','Sachsen-Anhalt');
INSERT INTO zones VALUES (93,81,'SCN','Schleswig-Holstein');
INSERT INTO zones VALUES (94,81,'THE','Thüringen');

# Austria
INSERT INTO zones VALUES (95,14,'WI','Wien');
INSERT INTO zones VALUES (96,14,'NO','Niederösterreich');
INSERT INTO zones VALUES (97,14,'OO','Oberösterreich');
INSERT INTO zones VALUES (98,14,'SB','Salzburg');
INSERT INTO zones VALUES (99,14,'KN','Kärnten');
INSERT INTO zones VALUES (100,14,'ST','Steiermark');
INSERT INTO zones VALUES (101,14,'TI','Tirol');
INSERT INTO zones VALUES (102,14,'BL','Burgenland');
INSERT INTO zones VALUES (103,14,'VB','Voralberg');

# Swizterland
INSERT INTO zones VALUES (104,204,'AG','Aargau');
INSERT INTO zones VALUES (105,204,'AI','Appenzell Innerrhoden');
INSERT INTO zones VALUES (106,204,'AR','Appenzell Ausserrhoden');
INSERT INTO zones VALUES (107,204,'BE','Bern');
INSERT INTO zones VALUES (108,204,'BL','Basel-Landschaft');
INSERT INTO zones VALUES (109,204,'BS','Basel-Stadt');
INSERT INTO zones VALUES (110,204,'FR','Freiburg');
INSERT INTO zones VALUES (111,204,'GE','Genf');
INSERT INTO zones VALUES (112,204,'GL','Glarus');
INSERT INTO zones VALUES (113,204,'JU','Graubünden');
INSERT INTO zones VALUES (114,204,'JU','Jura');
INSERT INTO zones VALUES (115,204,'LU','Luzern');
INSERT INTO zones VALUES (116,204,'NE','Neuenburg');
INSERT INTO zones VALUES (117,204,'NW','Nidwalden');
INSERT INTO zones VALUES (118,204,'OW','Obwalden');
INSERT INTO zones VALUES (119,204,'SG','St. Gallen');
INSERT INTO zones VALUES (120,204,'SH','Schaffhausen');
INSERT INTO zones VALUES (121,204,'SO','Solothurn');
INSERT INTO zones VALUES (122,204,'SZ','Schwyz');
INSERT INTO zones VALUES (123,204,'TG','Thurgau');
INSERT INTO zones VALUES (124,204,'TI','Tessin');
INSERT INTO zones VALUES (125,204,'UR','Uri');
INSERT INTO zones VALUES (126,204,'VD','Waadt');
INSERT INTO zones VALUES (127,204,'VS','Wallis');
INSERT INTO zones VALUES (128,204,'ZG','Zug');
INSERT INTO zones VALUES (129,204,'ZH','Zürich');

# Spain
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'A Coruña','A Coruña');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Alava','Alava');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Albacete','Albacete');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Alicante','Alicante');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Almeria','Almeria');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Asturias','Asturias');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Avila','Avila');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Badajoz','Badajoz');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Baleares','Baleares');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Barcelona','Barcelona');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Burgos','Burgos');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Caceres','Caceres');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Cadiz','Cadiz');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Cantabria','Cantabria');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Castellon','Castellon');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Ceuta','Ceuta');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Ciudad Real','Ciudad Real');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Cordoba','Cordoba');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Cuenca','Cuenca');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Girona','Girona');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Granada','Granada');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Guadalajara','Guadalajara');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Guipuzcoa','Guipuzcoa');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Huelva','Huelva');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Huesca','Huesca');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Jaen','Jaen');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'La Rioja','La Rioja');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Las Palmas','Las Palmas');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Leon','Leon');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Lleida','Lleida');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Lugo','Lugo');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Madrid','Madrid');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Malaga','Malaga');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Melilla','Melilla');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Murcia','Murcia');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Navarra','Navarra');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Ourense','Ourense');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Palencia','Palencia');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Pontevedra','Pontevedra');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Salamanca','Salamanca');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Santa Cruz de Tenerife','Santa Cruz de Tenerife');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Segovia','Segovia');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Sevilla','Sevilla');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Soria','Soria');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Tarragona','Tarragona');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Teruel','Teruel');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Toledo','Toledo');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Valencia','Valencia');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Valladolid','Valladolid');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Vizcaya','Vizcaya');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Zamora','Zamora');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'Zaragoza','Zaragoza');
