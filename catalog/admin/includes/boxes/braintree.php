<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  include(DIR_FS_CATALOG . 'includes/apps/braintree/admin/functions/boxes.php');

  $cl_box_groups[] = array('heading' => MODULES_ADMIN_MENU_BRAINTREE_HEADING,
                           'apps' => app_braintree_get_admin_box_links());
?>
