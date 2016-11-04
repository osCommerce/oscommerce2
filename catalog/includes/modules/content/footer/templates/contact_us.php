<?php
use OSC\OM\OSCOM;
?>
<div class="col-sm-<?php echo $content_width; ?>">
  <div class="footerbox contact">
    <h2><?php echo OSCOM::getDef('module_content_footer_contact_us_heading_title'); ?></h2>
    <address>
      <strong><?php echo STORE_NAME; ?></strong><br>
      <?php echo nl2br(STORE_ADDRESS); ?><br>
      <abbr title="Phone">P:</abbr> <?php echo STORE_PHONE; ?><br>
      <abbr title="Email">E:</abbr> <?php echo STORE_OWNER_EMAIL_ADDRESS; ?>
    </address>
    <ul class="list-unstyled">
      <li><a class="btn btn-success btn-sm btn-block" role="button" href="<?php echo OSCOM::link('contact_us.php'); ?>"><i class="fa fa-send"></i> <?php echo OSCOM::getDef('module_content_footer_contact_us_email_link'); ?></a></li>
    </ul>
  </div>
</div>
