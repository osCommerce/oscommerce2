<div class="col-sm-<?php echo $content_width; ?>">
  <div class="footerbox contact">
    <h2><?php echo MODULE_CONTENT_FOOTER_CONTACT_US_HEADING_TITLE; ?></h2>
    <address>
      <strong><?php echo STORE_NAME; ?></strong><br>
      <?php echo nl2br(STORE_ADDRESS); ?><br>
      <abbr title="Phone">P:</abbr> <?php echo STORE_PHONE; ?><br>
      <abbr title="Email">E:</abbr> <?php echo STORE_OWNER_EMAIL_ADDRESS; ?>
    </address>
    <ul class="list-unstyled">
      <li><a class="btn btn-success btn-sm btn-block" role="button" href="<?php echo tep_href_link('contact_us.php'); ?>"><i class="glyphicon glyphicon-send"></i> <?php echo MODULE_CONTENT_FOOTER_CONTACT_US_EMAIL_LINK; ?></a></li>
    </ul>
  </div>
</div>
