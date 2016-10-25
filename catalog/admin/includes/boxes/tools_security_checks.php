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
    if ( $group['heading'] == BOX_HEADING_TOOLS ) {
      $group['apps'][] = array('code' => 'security_checks.php',
                               'title' => MODULES_ADMIN_MENU_TOOLS_SECURITY_CHECKS,
                               'link' => OSCOM::link('security_checks.php'));

      break;
    }
  }
?>
