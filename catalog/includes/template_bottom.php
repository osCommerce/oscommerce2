<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/
?>

</div> <!-- bodyContent //-->

<?php
  if ($oscTemplate->hasBlocks('boxes_column_left')) {
?>

<div id="columnLeft" class="col-md-<?php echo $oscTemplate->getGridColumnWidth(); ?> col-md-pull-<?php echo $oscTemplate->getGridContentWidth(); ?>">
  <?php echo $oscTemplate->getBlocks('boxes_column_left'); ?>
</div>

<?php
  }

  if ($oscTemplate->hasBlocks('boxes_column_right')) {
?>

<div id="columnRight" class="col-md-<?php echo $oscTemplate->getGridColumnWidth(); ?>">
  <?php echo $oscTemplate->getBlocks('boxes_column_right'); ?>
</div>

<?php
  }
?>

</div> <!-- row //-->

</div> <!-- bodyWrapper //-->

<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>

<?php echo $oscTemplate->getBlocks('footer_scripts'); ?>

</body>
</html>
