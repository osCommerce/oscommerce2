<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

use OSC\OM\OSCOM;

$admin_menu['shop']['configuration']['administrators'] = OSCOM::link('administrators.php');

$Qgroups = $OSCOM_Db->get('configuration_group', [
  'configuration_group_id as cgID',
  'configuration_group_title as cgTitle'
], [
  'visible' => '1'
], 'sort_order');

while ($Qgroups->fetch()) {
  define('ADMIN_MENU_SHOP_CONFIGURATION_G' . $Qgroups->valueInt('cgID'), $Qgroups->value('cgTitle'));

  $admin_menu['shop']['configuration']['g' . $Qgroups->valueInt('cgID')] = OSCOM::link('configuration.php', 'gID=' . $Qgroups->valueInt('cgID'));
}

  $cl_box_groups[] = [
    'heading' => BOX_HEADING_CONFIGURATION,
    'apps' => [
      [
        'code' => FILENAME_STORE_LOGO,
        'title' => BOX_CONFIGURATION_STORE_LOGO,
        'link' => OSCOM::link(FILENAME_STORE_LOGO)
      ]
    ]
  ];
?>
