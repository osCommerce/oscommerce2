<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/
?>

<footer>
  <div class="container-fluid row-fluid">
    <div class="col-sm-12 text-center"><?php echo FOOTER_TEXT_BODY; ?></div>
    <?php
    if ($banner = tep_banner_exists('dynamic', 'footer')) {
      ?>

      <div class="col-sm-12 text-center">
        <?php echo tep_display_banner('static', $banner); ?>
      </div>

      <?php
    }
    ?>
  </div>
</footer>
