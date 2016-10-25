<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

use OSC\OM\OSCOM;

define('MODULE_SECURITY_CHECK_EXTENDED_EXT_DIRECTORY_LISTING_TITLE', 'ext/ Directory Listing');
define('MODULE_SECURITY_CHECK_EXTENDED_EXT_DIRECTORY_LISTING_HTTP_200', 'The <a href="' . OSCOM::link('Shop/ext/') . '" target="_blank">' . OSCOM::getConfig('http_path', 'Shop') . 'ext/</a> directory is publicly accessible and/or browsable - please disable directory listing for this directory in your web server configuration.');
?>
