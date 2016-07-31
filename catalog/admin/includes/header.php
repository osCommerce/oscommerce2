<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  if ($messageStack->size > 0) {
    echo $messageStack->output();
  }
?>

<div class="navbar navbar-default navbar-static-top" role="navigation">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="<?php echo OSCOM::link(FILENAME_DEFAULT); ?>"><i class="fa fa-home"></i></a>
    </div>

<?php
  if (isset($_SESSION['admin'])) {
?>

    <div class="navbar-collapse collapse">
      <ul class="nav navbar-nav">
        <li><a href="#">Shop <span class="caret"></span></a>
          <ul class="dropdown-menu">

<?php
    foreach ($cl_box_groups as $groups) {
      echo '<li><a>' . $groups['heading'] . ' <span class="caret"></span></a>
              <ul class="dropdown-menu">';

      foreach ($groups['apps'] as $app) {
        echo '<li><a href="' . $app['link'] . '">' . $app['title'] . '</a></li>';
      }

      echo '  </ul>
            </li>';
    }
?>

          </ul>
        </li>
        <li><a href="#">Apps <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a>Manager</a></li>

<?php
    if (!empty($cl_apps_groups)) {
      echo '<li class="divider"></li>';

      foreach ($cl_apps_groups as $groups) {
        echo '<li><a>' . $groups['heading'] . ' <span class="caret"></span></a>
                <ul class="dropdown-menu">';

        foreach ($groups['apps'] as $app) {
          echo '<li><a href="' . $app['link'] . '">' . $app['title'] . '</a></li>';
        }

        echo '  </ul>
              </li>';
      }
    }
?>

          </ul>
        </li>
      </ul>

<?php
  }
?>

      <ul class="nav navbar-nav navbar-right">

<?php
  if (isset($_SESSION['admin'])) {
?>

        <li><a><?php echo $_SESSION['admin']['username']; ?> <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="<?php echo OSCOM::link(FILENAME_LOGIN, 'action=logoff'); ?>">Logoff</a></li>
          </ul>
        </li>

<?php
  }
?>

        <li><a><i class="fa fa-question-circle"></i></a>
          <ul class="dropdown-menu">
            <li><a href="<?php echo OSCOM::link('Shop/', null, 'SSL'); ?>">Visit Shop</a></li>
            <li class="divider"></li>
            <li><a href="https://www.oscommerce.com">osCommerce Website</a></li>
            <li><a href="https://www.oscommerce.com/Support">Support</a></li>
            <li><a href="https://library.oscommerce.com">Documentation</a></li>
            <li><a href="http://forums.oscommerce.com">Community Forum</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</div>
