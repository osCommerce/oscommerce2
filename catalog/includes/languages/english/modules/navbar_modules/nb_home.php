<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

use OSC\OM\OSCOM;

  define('MODULE_NAVBAR_HOME_TITLE', 'Home');
  define('MODULE_NAVBAR_HOME_DESCRIPTION', 'Show Home Link in Navbar. <div class="secWarning">If you wish to have a Home button permanently displayed (even when the rest of the Menu is collapsed, eg in XS viewport) you could use the Brand module instead.</div>');

  define('MODULE_NAVBAR_HOME_PUBLIC_TEXT', '<li><a href="' . OSCOM::link('index.php') . '"><i class="fa fa-home"></i><span class="hidden-sm"> Home</span></a></li>');
