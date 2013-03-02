<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  if (isset($_SESSION['admin'])) {
    $cl_box_groups = array();

    include(DIR_WS_BOXES . 'configuration.php');
    include(DIR_WS_BOXES . 'catalog.php');
    include(DIR_WS_BOXES . 'modules.php');
    include(DIR_WS_BOXES . 'customers.php');
    include(DIR_WS_BOXES . 'taxes.php');
    include(DIR_WS_BOXES . 'localization.php');
    include(DIR_WS_BOXES . 'reports.php');
    include(DIR_WS_BOXES . 'tools.php');
?>

<div id="adminAppMenu" class="well well-small span2">
  <ul class="nav nav-list">

<?php
    foreach ($cl_box_groups as $groups) {
      echo '<li class="nav-header">' . $groups['heading'] . '</li>';

      foreach ($groups['apps'] as $app) {
        echo '<li';

        if ($app['code'] == $PHP_SELF) {
          echo ' class="active"';
        }

        echo '><a href="' . $app['link'] . '">' . $app['title'] . '</a></li>';
      }
    }
?>

  </ul>
</div>

<?php
  }
?>
