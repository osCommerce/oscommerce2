<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;

  foreach ( $cl_box_groups as &$group ) {
    if ( $group['heading'] == OSCOM::getDef('box_heading_tools') ) {
      $group['apps'][] = array('code' => 'security_checks.php',
                               'title' => OSCOM::getDef('modules_admin_menu_tools_security_checks'),
                               'link' => OSCOM::link('security_checks.php'));

      break;
    }
  }
?>
