<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  $cl_box_groups[] = array(
    'heading' => BOX_HEADING_LOCALIZATION,
    'apps' => array(
      array(
        'code' => FILENAME_CURRENCIES,
        'title' => BOX_LOCALIZATION_CURRENCIES,
        'link' => tep_href_link(FILENAME_CURRENCIES)
      ),
      array(
        'code' => FILENAME_LANGUAGES,
        'title' => BOX_LOCALIZATION_LANGUAGES,
        'link' => tep_href_link(FILENAME_LANGUAGES)
      ),
      array(
        'code' => FILENAME_ORDERS_STATUS,
        'title' => BOX_LOCALIZATION_ORDERS_STATUS,
        'link' => tep_href_link(FILENAME_ORDERS_STATUS)
      )
    )
  );
?>
