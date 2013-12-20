<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

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
<html xmlns="http://www.w3.org/1999/xhtml" <?php echo HTML_PARAMS; ?>>
<head>
<meta charset="<?php echo CHARSET; ?>" />
<!--[if IE]><meta http-equiv="x-ua-compatible" content="ie=edge" /><![endif]-->
<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1" />
<title><?php echo tep_output_string_protected($oscTemplate->getTitle()); ?></title>

<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>" />

<link rel="stylesheet" href="ext/headjs/html5.min.css" />
<link rel="stylesheet" href="ext/unsemantic/assets/stylesheets/unsemantic-grid-base<?php echo ((stripos(HTML_PARAMS, 'dir="rtl"') !== false) ? '-rtl' : ''); ?>.css" />
<noscript>
  <link rel="stylesheet" href="ext/unsemantic/assets/stylesheets/unsemantic-grid-mobile<?php echo ((stripos(HTML_PARAMS, 'dir="rtl"') !== false) ? '-rtl' : ''); ?>.css" />
</noscript>
<link rel="stylesheet" type="text/css" href="ext/jquery/ui/redmond/jquery-ui-1.10.3.min.css" />
<link rel="stylesheet" type="text/css" href="ext/colorbox/colorbox.css" />
<link rel="stylesheet" type="text/css" href="stylesheet.css" />

<script>
  var ADAPT_CONFIG = {
    path: 'ext/unsemantic/assets/stylesheets/',
    dynamic: true,
    range: [
      '0 to 767px = unsemantic-grid-mobile<?php echo ((stripos(HTML_PARAMS, 'dir="rtl"') !== false) ? '-rtl' : ''); ?>.css',
      '767px = unsemantic-grid-desktop<?php echo ((stripos(HTML_PARAMS, 'dir="rtl"') !== false) ? '-rtl' : ''); ?>.css'
    ]
  };
</script>
<script src="ext/unsemantic/assets/javascripts/adapt.min.js"></script>

<script src="ext/headjs/head.min.js"></script>
<script>
  head.load("ext/jquery/jquery-1.10.2.min.js", "ext/jquery/ui/jquery-ui-1.10.3.min.js", "ext/photoset-grid/jquery.photoset-grid.min.js", "ext/colorbox/jquery.colorbox-min.js");

<?php
  if (tep_not_null(JQUERY_DATEPICKER_I18N_CODE)) {
?>
  head.load("ext/jquery/ui/i18n/jquery.ui.datepicker-<?php echo JQUERY_DATEPICKER_I18N_CODE; ?>.js", function () {
    $.datepicker.setDefaults($.datepicker.regional['<?php echo JQUERY_DATEPICKER_I18N_CODE; ?>']);
  });
<?php
  }
?>

</script>

<?php echo $oscTemplate->getBlocks('header_tags'); ?>
</head>
<body>

<?php require(DIR_WS_INCLUDES . 'header.php'); ?>

<article id="bodyWrapper" class="grid-container">

  <section id="bodyContent" class="grid-<?php echo $oscTemplate->getGridContentWidth(); ?> <?php echo ($oscTemplate->hasBlocks('boxes_column_left') ? 'push-' . $oscTemplate->getGridColumnWidth() : ''); ?>">
