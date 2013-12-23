<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  $oscTemplate->addBlock('//code.jquery.com/jquery-1.10.2.min.js', 'jquery1');
  $oscTemplate->addBlock('//code.jquery.com/ui/1.10.3/jquery-ui.min.js', 'jquery1');

  $oscTemplate->addBlock('//code.jquery.com/jquery.min.js', 'jquery2');
  $oscTemplate->addBlock('//code.jquery.com/ui/1.10.3/jquery-ui.min.js', 'jquery2');

  $oscTemplate->addBlock('$("#headerShortcuts").buttonset();', 'headjs_onloads');
  $oscTemplate->addBlock('$(\'.productListTable tr:nth-child(even)\').addClass(\'alt\');', 'headjs_onloads');

  if (tep_not_null(JQUERY_DATEPICKER_I18N_CODE)) {
    $oscTemplate->addBlock('ext/jquery/ui/i18n/jquery.ui.datepicker-' . JQUERY_DATEPICKER_I18N_CODE . '.js', 'headjs_scripts');
    $oscTemplate->addBlock('$.datepicker.setDefaults($.datepicker.regional[\'' . JQUERY_DATEPICKER_I18N_CODE . '\']);', 'headjs_onloads');
  }

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
<link rel="stylesheet" href="//code.jquery.com/ui/1.10.3/themes/redmond/jquery-ui.min.css" />
<?php if ($oscTemplate->hasBlocks('css')) { echo $oscTemplate->getBlocksArray('css', '', "\n", ''); } ?>
<link rel="stylesheet" href="stylesheet.css" />

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
if (head.browser.ie && head.browser.version < 9) {
  head.load(<?php echo $oscTemplate->getBlocksArray('jquery1'); ?>);
} else {
  head.load(<?php echo $oscTemplate->getBlocksArray('jquery2'); ?>);
}

<?php
  if ($oscTemplate->hasBlocks('headjs_scripts')) {
    echo 'head.load(';
    echo $oscTemplate->getBlocksArray('headjs_scripts');
  }

  if ($oscTemplate->hasBlocks('headjs_onloads')) {
    if ($oscTemplate->hasBlocks('headjs_scripts')) {
      echo ',function(){';
    } else {
      echo 'head.ready(function(){';
    }
    echo $oscTemplate->getBlocks('headjs_onloads');
    echo '}';
  }

  if ($oscTemplate->hasBlocks('headjs_scripts') || $oscTemplate->hasBlocks('headjs_onloads')) {
    echo ');';
  }
?>

</script>

<?php echo $oscTemplate->getBlocks('header_tags'); ?>

</head>

<body>

<?php require(DIR_WS_INCLUDES . 'header.php'); ?>

<div id="bodyWrapper" class="grid-container">

  <article id="bodyContent" class="grid-<?php echo $oscTemplate->getGridContentWidth(); ?> <?php echo ($oscTemplate->hasBlocks('boxes_column_left') ? 'push-' . $oscTemplate->getGridColumnWidth() : ''); ?>" <?php echo (isset($schemaOrg)?$schemaOrg:''); ?>>
