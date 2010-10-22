<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  $cl_box_groups[] = array(
    'heading' => BOX_HEADING_MODULES,
    'apps' => array(
      array(
        'code' => FILENAME_MODULES,
        'title' => BOX_MODULES_PAYMENT,
        'link' => tep_href_link(FILENAME_MODULES, 'set=payment')
      ),
      array(
        'code' => FILENAME_MODULES,
        'title' => BOX_MODULES_SHIPPING,
        'link' => tep_href_link(FILENAME_MODULES, 'set=shipping')
      ),
      array(
        'code' => FILENAME_MODULES,
        'title' => BOX_MODULES_ORDER_TOTAL,
        'link' => tep_href_link(FILENAME_MODULES, 'set=ordertotal')
      ),
      array(
        'code' => FILENAME_MODULES,
        'title' => BOX_MODULES_ACTION_RECORDER,
        'link' => tep_href_link(FILENAME_MODULES, 'set=actionrecorder')
      ),
      array(
        'code' => FILENAME_MODULES,
        'title' => BOX_MODULES_HEADER_TAGS,
        'link' => tep_href_link(FILENAME_MODULES, 'set=header_tags')
      ),
      array(
        'code' => FILENAME_MODULES,
        'title' => BOX_MODULES_SOCIAL_BOOKMARKS,
        'link' => tep_href_link(FILENAME_MODULES, 'set=social_bookmarks')
      ),
      array(
        'code' => FILENAME_MODULES,
        'title' => BOX_MODULES_ADMIN_DASHBOARD,
        'link' => tep_href_link(FILENAME_MODULES, 'set=dashboard')
      )
    )
  );
?>
