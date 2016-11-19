<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;

  $cl_box_groups[] = array(
    'heading' => OSCOM::getDef('box_heading_customers'),
    'apps' => array(
      array(
        'code' => FILENAME_CUSTOMERS,
        'title' => OSCOM::getDef('box_customers_customers'),
        'link' => OSCOM::link(FILENAME_CUSTOMERS)
      )
    )
  );
?>
