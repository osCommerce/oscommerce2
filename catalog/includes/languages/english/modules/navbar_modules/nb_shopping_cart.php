<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

use OSC\OM\OSCOM;

  define('MODULE_NAVBAR_SHOPPING_CART_TITLE', 'Shopping Cart');
  define('MODULE_NAVBAR_SHOPPING_CART_DESCRIPTION', 'Show Shopping Cart in Navbar');

  define('MODULE_NAVBAR_SHOPPING_CART_CONTENTS', '<i class="fa fa-shopping-cart"></i> %s item(s) <span class="caret"></span>');
  define('MODULE_NAVBAR_SHOPPING_CART_NO_CONTENTS', '<i class="fa fa-shopping-cart"></i> 0 items');
  define('MODULE_NAVBAR_SHOPPING_CART_HAS_CONTENTS', '%s item(s), %s');
  define('MODULE_NAVBAR_SHOPPING_CART_VIEW_CART', 'View Cart');
  define('MODULE_NAVBAR_SHOPPING_CART_CHECKOUT', '<i class="fa fa-angle-right"></i> Checkout');

  define('MODULE_NAVBAR_SHOPPING_CART_PRODUCT', '<a href="' . OSCOM::link('product_info.php', 'products_id=%s') . '">%s x %s</a>');
