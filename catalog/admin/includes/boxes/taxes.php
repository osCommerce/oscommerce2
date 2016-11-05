<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;

  $cl_box_groups[] = array(
    'heading' => OSCOM::getDef('box_heading_location_and_taxes'),
    'apps' => array(
      array(
        'code' => FILENAME_COUNTRIES,
        'title' => OSCOM::getDef('box_taxes_countries'),
        'link' => OSCOM::link(FILENAME_COUNTRIES)
      ),
      array(
        'code' => FILENAME_ZONES,
        'title' => OSCOM::getDef('box_taxes_zones'),
        'link' => OSCOM::link(FILENAME_ZONES)
      ),
      array(
        'code' => FILENAME_GEO_ZONES,
        'title' => OSCOM::getDef('box_taxes_geo_zones'),
        'link' => OSCOM::link(FILENAME_GEO_ZONES)
      ),
      array(
        'code' => FILENAME_TAX_CLASSES,
        'title' => OSCOM::getDef('box_taxes_tax_classes'),
        'link' => OSCOM::link(FILENAME_TAX_CLASSES)
      ),
      array(
        'code' => FILENAME_TAX_RATES,
        'title' => OSCOM::getDef('box_taxes_tax_rates'),
        'link' => OSCOM::link(FILENAME_TAX_RATES)
      )
    )
  );
?>
