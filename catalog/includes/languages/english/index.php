<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

define('HEADING_TITLE', 'Welcome to ' . STORE_NAME);

define('TABLE_HEADING_NEW_PRODUCTS', 'New Products For %s');

define('TEXT_NO_PRODUCTS', 'There are no products available in this category.');
define('TEXT_NUMBER_OF_PRODUCTS', 'Number of Products: ');
define('TEXT_SHOW', '<strong>Show:</strong>');
define('TEXT_BUY', 'Buy 1 \'');
define('TEXT_NOW', '\' now');
define('TEXT_ALL_CATEGORIES', 'All Categories');
define('TEXT_ALL_MANUFACTURERS', 'All Manufacturers');

// seo
if ( ($GLOBALS['category_depth'] == 'top') && (!isset($_GET['manufacturers_id'])) ) {
  define('META_SEO_TITLE', 'Index Page Title');
  define('META_SEO_DESCRIPTION', 'This is the description of your site to be used in the META Description Element');
  /*
  keywords are USELESS unless you are selling into China and want to be listed in Baidu Search Engine
  */
  define('META_SEO_KEYWORDS', 'these, are, the, comma, separated, keywords, used in the META keywords Element');
}
