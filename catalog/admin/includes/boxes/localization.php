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
    'heading' => BOX_HEADING_LOCALIZATION,
    'apps' => array(
      array(
        'code' => FILENAME_CURRENCIES,
        'title' => BOX_LOCALIZATION_CURRENCIES,
        'link' => OSCOM::link(FILENAME_CURRENCIES)
      ),
      array(
        'code' => FILENAME_LANGUAGES,
        'title' => BOX_LOCALIZATION_LANGUAGES,
        'link' => OSCOM::link(FILENAME_LANGUAGES)
      ),
      array(
        'code' => FILENAME_ORDERS_STATUS,
        'title' => BOX_LOCALIZATION_ORDERS_STATUS,
        'link' => OSCOM::link(FILENAME_ORDERS_STATUS)
      )
    )
  );
?>
