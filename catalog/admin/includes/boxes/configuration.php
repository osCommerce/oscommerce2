<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  $cl_box_groups[] = array(
    'heading' => BOX_HEADING_CONFIGURATION,
    'apps' => array(
      array(
        'code' => FILENAME_ADMINISTRATORS,
        'title' => BOX_CONFIGURATION_ADMINISTRATORS,
        'link' => osc_href_link(FILENAME_ADMINISTRATORS)
      ),
      array(
        'code' => FILENAME_STORE_LOGO,
        'title' => BOX_CONFIGURATION_STORE_LOGO,
        'link' => osc_href_link(FILENAME_STORE_LOGO)
      )
    )
  );

  $configuration_groups_query = osc_db_query("select configuration_group_id as cgID, configuration_group_title as cgTitle from " . TABLE_CONFIGURATION_GROUP . " where visible = '1' order by sort_order");
  while ($configuration_groups = osc_db_fetch_array($configuration_groups_query)) {
    $cl_box_groups[sizeof($cl_box_groups)-1]['apps'][] = array(
      'code' => FILENAME_CONFIGURATION,
      'title' => $configuration_groups['cgTitle'],
      'link' => osc_href_link(FILENAME_CONFIGURATION, 'gID=' . $configuration_groups['cgID'])
    );
  }
?>
