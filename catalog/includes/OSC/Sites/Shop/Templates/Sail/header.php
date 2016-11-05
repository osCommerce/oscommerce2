<div class="modular-header">
  <?php echo $oscTemplate->getContent('header'); ?>
</div>

<div class="clearfix"></div>

<div class="body-sans-header clearfix">

<?php
  if (isset($_GET['error_message']) && tep_not_null($_GET['error_message'])) {
?>
<div class="clearfix"></div>
<div class="col-xs-12">
  <div class="alert alert-danger">
    <a href="#" class="close fa fa-remove" data-dismiss="alert"></a>
    <?php echo htmlspecialchars(stripslashes(urldecode($_GET['error_message']))); ?>
  </div>
</div>
<?php
  }

  if (isset($_GET['info_message']) && tep_not_null($_GET['info_message'])) {
?>
<div class="clearfix"></div>
<div class="col-xs-12">
  <div class="alert alert-info">
    <a href="#" class="close fa fa-remove" data-dismiss="alert"></a>
    <?php echo htmlspecialchars(stripslashes(urldecode($_GET['info_message']))); ?>
  </div>
</div>
<?php
  }
?>
