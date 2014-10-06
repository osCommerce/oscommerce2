<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  if ($messageStack->size('header') > 0) {
    echo '<div>' . $messageStack->output('header') . '</div>';
  }

  echo $oscTemplate->getContent('header');
?>

<?php
  if (isset($_GET['error_message']) && tep_not_null($_GET['error_message'])) {
?>
<div class="clearfix"></div>
<div class="col-xs-12">
  <div class="alert alert-danger">
    <a href="#" class="close glyphicon glyphicon-remove" data-dismiss="alert"></a>
    <?php echo htmlspecialchars(stripslashes(urldecode($_GET['error_message']))); ?>
  </div>
</div>
<?php
  }

  if (isset($_GET['info_message']) && tep_not_null($_GET['info_message'])) {
?>
<div class="clearfix"></div>
<div class="col-xs-12">
  <div class="alert alert-info">
    <a href="#" class="close glyphicon glyphicon-remove" data-dismiss="alert"></a>
    <?php echo htmlspecialchars(stripslashes(urldecode($_GET['info_message']))); ?>
  </div>
</div>
<?php
  }
?>
