<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;

  foreach ( $cl_box_groups as &$group ) {
    if ( $group['heading'] == OSCOM::getDef('box_heading_tools') ) {
      $group['apps'][] = array('code' => 'database_tables.php',
                               'title' => OSCOM::getDef('modules_admin_menu_tools_database_tables'),
                               'link' => OSCOM::link('database_tables.php'));

      break;
    }
  }
?>
