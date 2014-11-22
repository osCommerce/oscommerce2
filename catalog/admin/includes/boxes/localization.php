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
        'code' => 'currencies.php',
        'title' => BOX_LOCALIZATION_CURRENCIES,
        'link' => tep_href_link('currencies.php')
      ),
      array(
        'code' => 'languages.php',
        'title' => BOX_LOCALIZATION_LANGUAGES,
        'link' => tep_href_link('languages.php')
      ),
      array(
        'code' => 'orders_status.php',
        'title' => BOX_LOCALIZATION_ORDERS_STATUS,
        'link' => tep_href_link('orders_status.php')
      )
    )
  );
?>
