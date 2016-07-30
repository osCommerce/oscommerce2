<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;

  $cl_box_groups[] = [
    'heading' => BOX_HEADING_CONFIGURATION,
    'apps' => [
      [
        'code' => FILENAME_ADMINISTRATORS,
        'title' => BOX_CONFIGURATION_ADMINISTRATORS,
        'link' => OSCOM::link(FILENAME_ADMINISTRATORS)
      ],
      [
        'code' => FILENAME_STORE_LOGO,
        'title' => BOX_CONFIGURATION_STORE_LOGO,
        'link' => OSCOM::link(FILENAME_STORE_LOGO)
      ]
    ]
  ];

  $Qgroups = $OSCOM_Db->get('configuration_group', [
    'configuration_group_id as cgID',
    'configuration_group_title as cgTitle'
  ], [
    'visible' => '1'
  ], 'sort_order');

  while ($Qgroups->fetch()) {
    $cl_box_groups[sizeof($cl_box_groups)-1]['apps'][] = [
      'code' => FILENAME_CONFIGURATION,
      'title' => $Qgroups->value('cgTitle'),
      'link' => OSCOM::link(FILENAME_CONFIGURATION, 'gID=' . $Qgroups->value('cgID'))
    ];
  }
?>
