<?php
  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  $oscTemplate->buildBlocks();

  if (!$oscTemplate->hasBlocks('boxes_column_left')) {
    $oscTemplate->setGridContentWidth($oscTemplate->getGridContentWidth() + $oscTemplate->getGridColumnWidth());
  }

  if (!$oscTemplate->hasBlocks('boxes_column_right')) {
    $oscTemplate->setGridContentWidth($oscTemplate->getGridContentWidth() + $oscTemplate->getGridColumnWidth());
  }
?>
<!DOCTYPE html>
<html <?php echo OSCOM::getDef('html_params'); ?>>
<head>
<meta charset="<?php echo OSCOM::getDef('charset'); ?>">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title><?php echo HTML::outputProtected($oscTemplate->getTitle()); ?></title>
<base href="<?= OSCOM::getConfig('http_server', 'Shop') . OSCOM::getConfig('http_path', 'Shop'); ?>">

<link href="ext/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<!-- font awesome -->
<link href="ext/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">

<link href="<?= $oscTemplate->getPublicFile('css/custom.css'); ?>" rel="stylesheet">
<link href="<?= $oscTemplate->getPublicFile('css/user.css'); ?>" rel="stylesheet">

<!--[if lt IE 9]>
   <script src="ext/js/html5shiv.js"></script>
   <script src="ext/js/respond.min.js"></script>
   <script src="ext/js/excanvas.min.js"></script>
<![endif]-->

<script src="ext/jquery/jquery-2.2.3.min.js"></script>

<?php echo $oscTemplate->getBlocks('header_tags'); ?>
</head>
<body>

  <?php echo $oscTemplate->getContent('navigation'); ?>

  <div id="bodyWrapper" class="<?php echo BOOTSTRAP_CONTAINER; ?>">
    <div class="row">

      <?php require($oscTemplate->getFile('header.php')); ?>

      <div id="bodyContent" class="col-md-<?php echo $oscTemplate->getGridContentWidth(); ?> <?php echo ($oscTemplate->hasBlocks('boxes_column_left') ? 'col-md-push-' . $oscTemplate->getGridColumnWidth() : ''); ?>">
