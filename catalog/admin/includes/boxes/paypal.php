<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  include(DIR_FS_CATALOG . 'includes/apps/paypal/admin/functions/boxes.php');

  $cl_box_groups[] = array('heading' => MODULES_ADMIN_MENU_PAYPAL_HEADING,
                           'apps' => app_paypal_get_admin_box_links());
?>
