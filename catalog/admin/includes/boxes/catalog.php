<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  $cl_box_groups[] = array(
    'heading' => BOX_HEADING_CATALOG,
    'apps' => array(
      array(
        'code' => 'categories.php',
        'title' => BOX_CATALOG_CATEGORIES_PRODUCTS,
        'link' => tep_href_link('categories.php')
      ),
      array(
        'code' => 'products_attributes.php',
        'title' => BOX_CATALOG_CATEGORIES_PRODUCTS_ATTRIBUTES,
        'link' => tep_href_link('products_attributes.php')
      ),
      array(
        'code' => 'manufacturers.php',
        'title' => BOX_CATALOG_MANUFACTURERS,
        'link' => tep_href_link('manufacturers.php')
      ),
      array(
        'code' => 'reviews.php',
        'title' => BOX_CATALOG_REVIEWS,
        'link' => tep_href_link('reviews.php')
      ),
      array(
        'code' => 'specials.php',
        'title' => BOX_CATALOG_SPECIALS,
        'link' => tep_href_link('specials.php')
      ),
      array(
        'code' => 'products_expected.php',
        'title' => BOX_CATALOG_PRODUCTS_EXPECTED,
        'link' => tep_href_link('products_expected.php')
      )
    )
  );
?>
