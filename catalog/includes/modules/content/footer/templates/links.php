<?php
use OSC\OM\OSCOM;
?>
<div class="col-sm-<?php echo $content_width; ?>">
  <div class="footerbox information">
    <h2><?php echo OSCOM::getDef('module_content_footer_information_heading_title'); ?></h2>
    <ul class="nav nav-pills nav-stacked">
      <li><a href="<?php echo OSCOM::link('shipping.php'); ?>"><?php echo OSCOM::getDef('module_content_footer_information_shipping'); ?></a></li>
      <li><a href="<?php echo OSCOM::link('privacy.php'); ?>"><?php echo OSCOM::getDef('module_content_footer_information_privacy'); ?></a></li>
      <li><a href="<?php echo OSCOM::link('conditions.php'); ?>"><?php echo OSCOM::getDef('module_content_footer_information_conditions'); ?></a></li>
      <li><a href="<?php echo OSCOM::link('contact_us.php'); ?>"><?php echo OSCOM::getDef('module_content_footer_information_contact'); ?></a></li>
    </ul>
  </div>
</div>
