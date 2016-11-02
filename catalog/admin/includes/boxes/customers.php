<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
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
