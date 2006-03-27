<?php
/*
  $Id: index.php,v 1.1 2003/06/11 17:38:00 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

define('TEXT_MAIN', 'This is a default setup of the osCommerce project, products shown are for demonstrational purposes, <b>any products purchased will not be delivered nor will the customer be billed</b>. Any information seen on these products is to be treated as fictional.<br><br><table border="0" width="100%" cellspacing="5" cellpadding="2"><tr><td class="main" valign="top">' . tep_image(DIR_WS_IMAGES . 'default/1.gif') . '</td><td class="main" valign="top"><b>Error Messages</b><br><br>If there are any error or warning messages shown above, please correct them first before proceeding.<br><br>Error messages are displayed at the very top of the page with a complete <span class="messageStackError">background</span> color.<br><br>Several checks are performed to ensure a healthy setup of your online store - these checks can be disabled by editing the appropriate parameters at the bottom of the includes/application_top.php file.</td></tr><td class="main" valign="top">' . tep_image(DIR_WS_IMAGES . 'default/2.gif') . '</td><td class="main" valign="top"><b>Editing Page Texts</b><br><br>The text shown here can be modified in the following file, on each language basis:<br><br><nobr class="messageStackSuccess">[path to catalog]/includes/languages/' . $language . '/' . FILENAME_DEFAULT . '</nobr><br><br>That file can be edited manually, or via the Administration Tool with the <nobr class="messageStackSuccess">Languages->' . ucfirst($language) . '->Define</nobr> or <nobr class="messageStackSuccess">Tools->File Manager</nobr> modules.<br><br>The text is set in the following manner:<br><br><nobr>define(\'TEXT_MAIN\', \'<span class="messageStackSuccess">This is a default setup of the osCommerce project...</span>\');</nobr><br><br>The text highlighted in green may be modified - it is important to keep the define() of the TEXT_MAIN keyword. To remove the text for TEXT_MAIN completely, the following example is used where only two single quote characters exist:<br><br><nobr>define(\'TEXT_MAIN\', \'\');</nobr><br><br>More information concerning the PHP define() function can be read <a href="http://www.php.net/define" target="_blank"><u>here</u></a>.</td></tr><tr><td class="main" valign="top">' . tep_image(DIR_WS_IMAGES . 'default/3.gif') . '</td><td class="main" valign="top"><b>Securing The Administration Tool</b><br><br>It is important to secure the Administration Tool as there is currently no security implementation available.</td></tr><tr><td class="main" valign="top">' . tep_image(DIR_WS_IMAGES . 'default/4.gif') . '</td><td class="main" valign="top"><b>Online Documentation</b><br><br>Online documentation can be read at the <a href="http://www.oscommerce.info" target="_blank"><u>osCommerce Knowledge Base</u></a> site.<br><br>Community support is available at the <a href="http://forums.oscommerce.com" target="_blank"><u>osCommerce Community Support Forums</u></a> site.</td></tr></table><br>If you wish to download the solution powering this shop, or if you wish to contribute to the osCommerce project, please visit the <a href="http://www.oscommerce.com" target="_blank"><u>support site of osCommerce</u></a>. This shop is running on osCommerce version <font color="#f0000"><b>' . PROJECT_VERSION . '</b></font>.');
define('TABLE_HEADING_NEW_PRODUCTS', 'New Products For %s');
define('TABLE_HEADING_UPCOMING_PRODUCTS', 'Upcoming Products');
define('TABLE_HEADING_DATE_EXPECTED', 'Date Expected');

if ( ($category_depth == 'products') || (isset($HTTP_GET_VARS['manufacturers_id'])) ) {
  define('HEADING_TITLE', 'Let\'s See What We Have Here');
  define('TABLE_HEADING_IMAGE', '');
  define('TABLE_HEADING_MODEL', 'Model');
  define('TABLE_HEADING_PRODUCTS', 'Product Name');
  define('TABLE_HEADING_MANUFACTURER', 'Manufacturer');
  define('TABLE_HEADING_QUANTITY', 'Quantity');
  define('TABLE_HEADING_PRICE', 'Price');
  define('TABLE_HEADING_WEIGHT', 'Weight');
  define('TABLE_HEADING_BUY_NOW', 'Buy Now');
  define('TEXT_NO_PRODUCTS', 'There are no products to list in this category.');
  define('TEXT_NO_PRODUCTS2', 'There is no product available from this manufacturer.');
  define('TEXT_NUMBER_OF_PRODUCTS', 'Number of Products: ');
  define('TEXT_SHOW', '<b>Show:</b>');
  define('TEXT_BUY', 'Buy 1 \'');
  define('TEXT_NOW', '\' now');
  define('TEXT_ALL_CATEGORIES', 'All Categories');
  define('TEXT_ALL_MANUFACTURERS', 'All Manufacturers');
} elseif ($category_depth == 'top') {
  define('HEADING_TITLE', 'What\'s New Here?');
} elseif ($category_depth == 'nested') {
  define('HEADING_TITLE', 'Categories');
}
?>
