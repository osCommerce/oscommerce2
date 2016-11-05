<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

use OSC\OM\OSCOM;
use OSC\OM\Registry;

$OSCOM_Language = Registry::get('Language');

$admin_menu['shop']['configuration']['administrators'] = OSCOM::link('administrators.php');

$Qgroups = $OSCOM_Db->get('configuration_group', [
  'configuration_group_id as cgID',
  'configuration_group_title as cgTitle'
], [
  'visible' => '1'
], 'sort_order');

while ($Qgroups->fetch()) {
  $OSCOM_Language->injectDefinitions([
    'admin_menu_shop_configuration_g' . $Qgroups->valueInt('cgID') => $Qgroups->value('cgTitle')
  ], 'global');

  $admin_menu['shop']['configuration']['g' . $Qgroups->valueInt('cgID')] = OSCOM::link('configuration.php', 'gID=' . $Qgroups->valueInt('cgID'));
}

  $cl_box_groups[] = [
    'heading' => OSCOM::getDef('box_heading_configuration'),
    'apps' => [
      [
        'code' => FILENAME_STORE_LOGO,
        'title' => OSCOM::getDef('box_configuration_store_logo'),
        'link' => OSCOM::link(FILENAME_STORE_LOGO)
      ]
    ]
  ];
?>
