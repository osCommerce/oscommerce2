<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

use OSC\OM\OSCOM;

  define('MODULE_NAVBAR_REVIEWS_TITLE', 'Reviews');
  define('MODULE_NAVBAR_REVIEWS_DESCRIPTION', 'Show Reviews Link in Navbar.');

  define('MODULE_NAVBAR_REVIEWS_PUBLIC_TEXT', '<li><a href="' . OSCOM::link('reviews.php') . '"><i class="fa fa-comment"></i><span class="hidden-sm"> Reviews</span></a></li>');
