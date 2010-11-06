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

<div id="columnLeft" class="grid_4 pull_16">
  <?php echo $oscTemplate->getBlocks('boxes_column_left'); ?>
</div>

<div id="columnRight" class="grid_4">
  <?php echo $oscTemplate->getBlocks('boxes_column_right'); ?>
</div>

<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>

</div> <!-- bodyWrapper //-->

<?php echo $oscTemplate->getBlocks('footer_scripts'); ?>

</body>
</html>
