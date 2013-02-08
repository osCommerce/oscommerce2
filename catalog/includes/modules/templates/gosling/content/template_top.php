<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce

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
<!doctype html>

<html <?php echo HTML_PARAMS; ?>>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" />

<title><?php echo tep_output_string_protected($oscTemplate->getTitle()); ?></title>

<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>" />

<link rel="icon" type="image/png" href="{publiclink}images/oscommerce_icon.png{publiclink}" />

<meta name="generator" content="osCommerce Online Merchant" />

<script type="text/javascript" src="ext/jquery/jquery-1.9.1.min.js"></script>

<script type="text/javascript" src="ext/bootstrap/js/bootstrap.min.js"></script>
<link rel="stylesheet" type="text/css" href="public/template/gosling/css/general.css" />






<link rel="stylesheet" type="text/css" href="ext/jquery/ui/redmond/jquery-ui-1.8.23.css" />
<script type="text/javascript" src="ext/jquery/ui/jquery-ui-1.8.23.min.js"></script>

<?php
  if (tep_not_null(JQUERY_DATEPICKER_I18N_CODE)) {
?>
<script type="text/javascript" src="ext/jquery/ui/i18n/jquery.ui.datepicker-<?php echo JQUERY_DATEPICKER_I18N_CODE; ?>.js"></script>
<script type="text/javascript">
$.datepicker.setDefaults($.datepicker.regional['<?php echo JQUERY_DATEPICKER_I18N_CODE; ?>']);
</script>
<?php
  }
?>

<script type="text/javascript" src="ext/jquery/bxGallery/jquery.bxGallery.1.1.min.js"></script>
<link rel="stylesheet" type="text/css" href="ext/jquery/fancybox/jquery.fancybox-1.3.4.css" />
<script type="text/javascript" src="ext/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>

<?php echo $oscTemplate->getBlocks('header_tags'); ?>
</head>
<body>

<div id="bodyWrapper" class="container-fluid">

<?php require(DIR_WS_INCLUDES . 'header.php'); ?>

<div class="row-fluid">

<?php
  if ($oscTemplate->hasBlocks('boxes_column_left')) {
?>

<div id="columnLeft" class="span<?php echo $oscTemplate->getGridColumnWidth(); ?>">
  <?php echo $oscTemplate->getBlocks('boxes_column_left'); ?>
</div>

<?php
  }
?>

<div id="bodyContent" class="span<?php echo $oscTemplate->getGridContentWidth(); ?>">
