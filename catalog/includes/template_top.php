<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  $oscTemplate->buildBlocks();

  if (!$oscTemplate->hasBlocks('boxes_column_left')) {
    $oscTemplate->setGridContentWidth($oscTemplate->getGridContentWidth() + $oscTemplate->getGridColumnWidth());
  }

  if (!$oscTemplate->hasBlocks('boxes_column_right')) {
    $oscTemplate->setGridContentWidth($oscTemplate->getGridContentWidth() + $oscTemplate->getGridColumnWidth());
  }
?>
<!DOCTYPE html>
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta charset="<?php echo CHARSET; ?>" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo tep_output_string_protected($oscTemplate->getTitle()); ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>" />

<!-- Bootstrap -->
<link href="ext/bootstrap/css/bootstrap.min.css" rel="stylesheet">

<script src="ext/jquery/jquery-1.11.1.min.js"></script>

<script src="ext/bootstrap/js/bootstrap.min.js"></script>

<script src="ext/photoset-grid/jquery.photoset-grid.min.js"></script>

<link rel="stylesheet" href="ext/colorbox/colorbox.css" />
<script type="text/javascript" src="ext/colorbox/jquery.colorbox-min.js"></script>

<!-- Custom -->
<link href="custom.css" rel="stylesheet">
<!-- Custom -->
<link href="user.css" rel="stylesheet">

<!-- font awesome -->
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">

<?php echo $oscTemplate->getBlocks('header_tags'); ?>
</head>
<body>

<div id="bodyWrapper" class="container-fluid">

<div class="row">

<?php require(DIR_WS_INCLUDES . 'header.php'); ?>


<div id="bodyContent" class="col-md-<?php echo $oscTemplate->getGridContentWidth(); ?> <?php echo ($oscTemplate->hasBlocks('boxes_column_left') ? 'col-md-push-' . $oscTemplate->getGridColumnWidth() : ''); ?>">
