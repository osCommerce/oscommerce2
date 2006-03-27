<?php
/*
  $Id: upgrade.php,v 1.1 2002/01/29 11:48:51 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application.php');

  $page_file = 'upgrade.php';
  $page_title = 'Upgrade';

  switch ($HTTP_GET_VARS['step']) {
    case '2':
      $page_contents = 'upgrade_2.php';
      break;
    case '3':
      $page_contents = 'upgrade_3.php';
      break;
    default:
      $page_contents = 'upgrade.php';
  }

  require('templates/main_page.php');
?>
