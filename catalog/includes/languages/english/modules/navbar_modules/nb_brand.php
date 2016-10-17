<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

use OSC\OM\OSCOM;

  define('MODULE_NAVBAR_BRAND_TITLE', 'Brand');
  define('MODULE_NAVBAR_BRAND_DESCRIPTION', 'Show Brand in Navbar.  <div class="secWarning">This can be a simple link or something more complicated such as an image.<br><br>For more details about using an image, see <a target="_blank" href="http://getbootstrap.com/components/#navbar-brand-image"><u>#navbar-brand-image</u></a></div>');

  define('MODULE_NAVBAR_BRAND_PUBLIC_TEXT', '<a class="navbar-brand" href="' . OSCOM::link('index.php') . '">' . STORE_NAME . '</a>');
