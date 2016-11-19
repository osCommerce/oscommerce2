<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
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
