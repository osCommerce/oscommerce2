<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/
?>

  </section> <!-- bodyContent //-->

<?php
  if ($oscTemplate->hasBlocks('boxes_column_left')) {
?>

  <section id="columnLeft" class="grid-<?php echo $oscTemplate->getGridColumnWidth(); ?> pull-<?php echo $oscTemplate->getGridContentWidth(); ?>">
    <?php echo $oscTemplate->getBlocks('boxes_column_left'); ?>
  </section>

<?php
  }

  if ($oscTemplate->hasBlocks('boxes_column_right')) {
?>

  <section id="columnRight" class="grid-<?php echo $oscTemplate->getGridColumnWidth(); ?>">
    <?php echo $oscTemplate->getBlocks('boxes_column_right'); ?>
  </section>

<?php
  }
?>

</article> <!-- bodyWrapper //-->

<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>

<?php echo $oscTemplate->getBlocks('footer_scripts'); ?>

</body>
</html>
