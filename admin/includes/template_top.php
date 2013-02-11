<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce

  Released under the GNU General Public License
*/
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<meta name="robots" content="noindex,nofollow">
<title><?php echo TITLE; ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<base href="<?php echo HTTP_SERVER . DIR_WS_ADMIN; ?>" />
<!--[if IE]><script src="<?php echo osc_catalog_href_link('ext/flot/excanvas.min.js'); ?>"></script><![endif]-->
<link rel="stylesheet" type="text/css" href="<?php echo osc_catalog_href_link('ext/bootstrap/css/bootstrap.min.css'); ?>" />
    <style type="text/css">
    body {
		padding-top: 60px;
		padding-bottom: 40px;
	 }
      @media (max-width: 980px) {
        /* Enable use of floated navbar text */
        .navbar-text.pull-right {
          float: none;
          padding-left: 5px;
          padding-right: 5px;
        }
      }
    </style>
<link rel="stylesheet" type="text/css" href="<?php echo osc_catalog_href_link('ext/bootstrap/css/bootstrap-responsive.min.css'); ?>" />
<!-- this is until boot strap is 100% -->
<link rel="stylesheet" type="text/css" href="<?php echo osc_catalog_href_link('ext/jquery/ui/redmond/jquery-ui-1.8.23.css'); ?>">
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script src="includes/general.js"></script>
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="<?php echo osc_catalog_href_link('ext/assets/js/html5shiv.js'); ?>"></script>
    <![endif]-->
<script src="<?php echo osc_catalog_href_link('ext/jquery/jquery-1.9.1.min.js'); ?>"></script>
<script src="<?php echo osc_catalog_href_link('ext/bootstrap/js/bootstrap.min.js'); ?>"></script>
<!-- CDN is only for UI compatibility with jquery-1.9.1 untill boot strap is 100% integrated -->
<script src="//code.jquery.com/ui/1.10.0/jquery-ui.js"></script>

<?php
  if (osc_not_null(JQUERY_DATEPICKER_I18N_CODE)) {
?>
<script src="<?php echo osc_catalog_href_link('ext/jquery/ui/i18n/jquery.ui.datepicker-' . JQUERY_DATEPICKER_I18N_CODE . '.js'); ?>"></script>
<script type="text/javascript">
$.datepicker.setDefaults($.datepicker.regional['<?php echo JQUERY_DATEPICKER_I18N_CODE; ?>']);
</script>
<?php
  }
?>
<script src="<?php echo osc_catalog_href_link('ext/flot/jquery.flot.js'); ?>"></script>
</head>
<body>

<?php require(DIR_WS_INCLUDES . 'header.php'); ?>

<div class="container-fluid">
	<div class="row-fluid">

<?php
  if (isset($_SESSION['admin'])) {
    include(DIR_WS_INCLUDES . 'column_left.php');
  } else {
?>

<style>
#bodyContent {
  margin-left: 0;
}
</style>

<?php
  }
?>

<section id="bodyContent">
