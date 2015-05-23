<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  foreach ( $cl_box_groups as &$group ) {
    if ( $group['heading'] == BOX_HEADING_MODULES ) {
      $group['apps'][] = array('code' => 'modules_hooks.php',
                               'title' => MODULES_ADMIN_MENU_MODULES_HOOKS,
                               'link' => tep_href_link('modules_hooks.php'));

      break;
    }
  }
?>
