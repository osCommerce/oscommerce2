<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;

  $cl_box_groups[] = array(
    'heading' => OSCOM::getDef('box_heading_localization'),
    'apps' => array(
      array(
        'code' => FILENAME_CURRENCIES,
        'title' => OSCOM::getDef('box_localization_currencies'),
        'link' => OSCOM::link(FILENAME_CURRENCIES)
      ),
      array(
        'code' => FILENAME_LANGUAGES,
        'title' => OSCOM::getDef('box_localization_languages'),
        'link' => OSCOM::link(FILENAME_LANGUAGES)
      ),
      array(
        'code' => FILENAME_ORDERS_STATUS,
        'title' => OSCOM::getDef('box_localization_orders_status'),
        'link' => OSCOM::link(FILENAME_ORDERS_STATUS)
      )
    )
  );
?>
