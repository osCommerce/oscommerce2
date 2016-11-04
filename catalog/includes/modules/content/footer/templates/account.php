<?php
use OSC\OM\OSCOM;
?>
<div class="col-sm-<?php echo $content_width; ?>">
  <div class="footerbox account">
    <h2><?php echo OSCOM::getDef('module_content_footer_account_heading_title'); ?></h2>
    <ul class="list-unstyled">
      <?php
      echo $account_content;
      ?>
    </ul>
  </div>
</div>
