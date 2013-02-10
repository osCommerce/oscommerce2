<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2008 osCommerce

  Released under the GNU General Public License
*/
?>
<header class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="brand" href="#"><img src="./images/oscommerce_white_fill.png" alt="osCommerce Online Merchant" width="202" height="30" ></a>
          <div class="nav-collapse collapse">
            <p class="navbar-text pull-right">
			<?php echo (isset($_SESSION['admin']) ? 'Logged in as: ' . $_SESSION['admin']['username']  . ' (<a href="' . osc_href_link(FILENAME_LOGIN, 'action=logoff') . '" class="navbar-link">Logoff</a>)' : ''); ?>
            </p>
            <ul class="nav">
              <li><?php echo '<a href="' . osc_href_link(FILENAME_DEFAULT, '', 'NONSSL') . '">' . HEADER_TITLE_ADMINISTRATION . '</a>'; ?></a></li>
              <li><?php echo '<a href="' . osc_catalog_href_link() . '">' . HEADER_TITLE_ONLINE_CATALOG . '</a>'; ?></li>
              <li><?php echo '<a href="http://www.oscommerce.com">' . HEADER_TITLE_SUPPORT_SITE . '</a>'; ?></li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </header>




<?php
  if ($messageStack->size > 0) {
    echo $messageStack->output();
  }
?>
