<?php
use OSC\OM\OSCOM;
?>
<div class="col-sm-<?php echo $content_width; ?>">
  <div class="footerbox information">
    <h2><?php echo MODULE_CONTENT_FOOTER_INFORMATION_HEADING_TITLE; ?></h2>
    <ul class="list-unstyled">
      <li><a href="<?php echo OSCOM::link('shipping.php'); ?>"><?php echo MODULE_CONTENT_FOOTER_INFORMATION_SHIPPING; ?></a></li>
      <li><a href="<?php echo OSCOM::link('privacy.php'); ?>"><?php echo MODULE_CONTENT_FOOTER_INFORMATION_PRIVACY; ?></a></li>
      <li><a href="<?php echo OSCOM::link('conditions.php'); ?>"><?php echo MODULE_CONTENT_FOOTER_INFORMATION_CONDITIONS; ?></a></li>
      <li><a href="<?php echo OSCOM::link('contact_us.php'); ?>"><?php echo MODULE_CONTENT_FOOTER_INFORMATION_CONTACT; ?></a></li>
  	</ul>
  </div>
</div>
