<?php
use OSC\OM\OSCOM;

?>
<div class="col-sm-<?php echo $content_width; ?> reviews">
  <h4 class="page-header"><?php echo OSCOM::getDef('reviews_text_title'); ?></h4>
  <?php echo $review_data; ?>
</div>

