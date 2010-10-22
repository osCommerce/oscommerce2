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
        'code' => FILENAME_COUNTRIES,
        'title' => BOX_TAXES_COUNTRIES,
        'link' => tep_href_link(FILENAME_COUNTRIES)
      ),
      array(
        'code' => FILENAME_ZONES,
        'title' => BOX_TAXES_ZONES,
        'link' => tep_href_link(FILENAME_ZONES)
      ),
      array(
        'code' => FILENAME_GEO_ZONES,
        'title' => BOX_TAXES_GEO_ZONES,
        'link' => tep_href_link(FILENAME_GEO_ZONES)
      ),
      array(
        'code' => FILENAME_TAX_CLASSES,
        'title' => BOX_TAXES_TAX_CLASSES,
        'link' => tep_href_link(FILENAME_TAX_CLASSES)
      ),
      array(
        'code' => FILENAME_TAX_RATES,
        'title' => BOX_TAXES_TAX_RATES,
        'link' => tep_href_link(FILENAME_TAX_RATES)
      )
    )
  );
?>
