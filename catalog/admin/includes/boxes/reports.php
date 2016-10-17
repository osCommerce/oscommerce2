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
    'heading' => BOX_HEADING_REPORTS,
    'apps' => array(
      array(
        'code' => FILENAME_STATS_PRODUCTS_VIEWED,
        'title' => BOX_REPORTS_PRODUCTS_VIEWED,
        'link' => OSCOM::link(FILENAME_STATS_PRODUCTS_VIEWED)
      ),
      array(
        'code' => FILENAME_STATS_PRODUCTS_PURCHASED,
        'title' => BOX_REPORTS_PRODUCTS_PURCHASED,
        'link' => OSCOM::link(FILENAME_STATS_PRODUCTS_PURCHASED)
      ),
      array(
        'code' => FILENAME_STATS_CUSTOMERS,
        'title' => BOX_REPORTS_ORDERS_TOTAL,
        'link' => OSCOM::link(FILENAME_STATS_CUSTOMERS)
      )
    )
  );
?>
