<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

use OSC\OM\OSCOM;
use OSC\OM\Registry;

$OSCOM_Page = Registry::get('Site')->getPage();

require(OSCOM::BASE_DIR . 'template_top.php');
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<?php
if ($messageStack->size('login') > 0) {
    echo $messageStack->output('login');
}
?>

<div id="loginModules">
  <div class="row">
    <?php echo $OSCOM_Page->data['content']; ?>
  </div>
</div>

<?php
require(OSCOM::BASE_DIR . 'template_bottom.php');
?>
