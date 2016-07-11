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
    'heading' => BOX_HEADING_ORDERS,
    'apps' => array(
      array(
        'code' => FILENAME_ORDERS,
        'title' => BOX_ORDERS_ORDERS,
        'link' => OSCOM::link(FILENAME_ORDERS)
      )
    )
  );
?>
