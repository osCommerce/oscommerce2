<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  require(DIR_WS_INCLUDES . 'counter.php');
?>

<footer class="grid-container footer">
  <div class="grid-100"><?php echo FOOTER_TEXT_BODY; ?></div>
</footer>

<?php
  if ($banner = tep_banner_exists('dynamic', 'footer')) {
?>

<div role="banner" class="grid-container" style="text-align: center; padding-bottom: 20px;">
  <?php echo tep_display_banner('static', $banner); ?>

</div>

<?php
  }
?>
