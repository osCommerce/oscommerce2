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
