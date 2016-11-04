<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;

  $cl_box_groups[] = array(
    'heading' => OSCOM::getDef('box_heading_reports'),
    'apps' => array(
      array(
        'code' => FILENAME_STATS_PRODUCTS_VIEWED,
        'title' => OSCOM::getDef('box_reports_products_viewed'),
        'link' => OSCOM::link(FILENAME_STATS_PRODUCTS_VIEWED)
      ),
      array(
        'code' => FILENAME_STATS_PRODUCTS_PURCHASED,
        'title' => OSCOM::getDef('box_reports_products_purchased'),
        'link' => OSCOM::link(FILENAME_STATS_PRODUCTS_PURCHASED)
      ),
      array(
        'code' => FILENAME_STATS_CUSTOMERS,
        'title' => OSCOM::getDef('box_reports_orders_total'),
        'link' => OSCOM::link(FILENAME_STATS_CUSTOMERS)
      )
    )
  );
?>
