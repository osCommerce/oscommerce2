<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;

  foreach ( $cl_box_groups as &$group ) {
    if ( $group['heading'] == OSCOM::getDef('box_heading_modules') ) {
      $group['apps'][] = array('code' => 'modules_content.php',
                               'title' => OSCOM::getDef('modules_admin_menu_modules_content'),
                               'link' => OSCOM::link('modules_content.php'));

      break;
    }
  }
?>
