<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  $cl_box_groups[] = array(
    'heading' => BOX_HEADING_REPORTS,
    'apps' => array(
      array(
        'code' => 'stats_products_viewed.php',
        'title' => BOX_REPORTS_PRODUCTS_VIEWED,
        'link' => tep_href_link('stats_products_viewed.php')
      ),
      array(
        'code' => 'stats_products_purchased.php',
        'title' => BOX_REPORTS_PRODUCTS_PURCHASED,
        'link' => tep_href_link('stats_products_purchased.php')
      ),
      array(
        'code' => 'stats_customers.php',
        'title' => BOX_REPORTS_ORDERS_TOTAL,
        'link' => tep_href_link('stats_customers.php')
      )
    )
  );
?>
