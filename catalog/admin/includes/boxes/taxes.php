<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  $cl_box_groups[] = array(
    'heading' => BOX_HEADING_LOCATION_AND_TAXES,
    'apps' => array(
      array(
        'code' => 'countries.php',
        'title' => BOX_TAXES_COUNTRIES,
        'link' => tep_href_link('countries.php')
      ),
      array(
        'code' => 'zones.php',
        'title' => BOX_TAXES_ZONES,
        'link' => tep_href_link('zones.php')
      ),
      array(
        'code' => 'geo_zones.php',
        'title' => BOX_TAXES_GEO_ZONES,
        'link' => tep_href_link('geo_zones.php')
      ),
      array(
        'code' => 'tax_classes.php',
        'title' => BOX_TAXES_TAX_CLASSES,
        'link' => tep_href_link('tax_classes.php')
      ),
      array(
        'code' => 'tax_rates.php',
        'title' => BOX_TAXES_TAX_RATES,
        'link' => tep_href_link('tax_rates.php')
      )
    )
  );
?>
