<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require(DIR_WS_INCLUDES . 'counter.php');
?>

<footer class="grid-container footer">
  <p align="center"><?php echo FOOTER_TEXT_BODY; ?></p>
</footer>

<?php
  if ($banner = tep_banner_exists('dynamic', 'footer')) {
?>

<div class="grid-container" style="text-align: center; padding-bottom: 20px;">
  <?php echo tep_display_banner('static', $banner); ?>
</div>

<?php
  }
?>

<script>
  head.ready(function() {
    $('.productListTable tr:nth-child(even)').addClass('alt');
  });
</script>
