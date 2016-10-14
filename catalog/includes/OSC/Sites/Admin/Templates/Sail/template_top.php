<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

use OSC\OM\OSCOM;
?>
<!DOCTYPE html>
<html <?= HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?= CHARSET; ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title><?= TITLE; ?></title>
<base href="<?= ($request_type == 'SSL') ? OSCOM::getConfig('https_server', 'Admin') . OSCOM::getConfig('https_path', 'Admin') : OSCOM::getConfig('http_server', 'Admin') . OSCOM::getConfig('http_path', 'Admin'); ?>" />
<link rel="stylesheet" type="text/css" href="<?= OSCOM::link('Shop/ext/jquery/ui/redmond/jquery-ui-1.11.4.min.css', '', 'AUTO', false); ?>">
<script type="text/javascript" src="<?= OSCOM::link('Shop/ext/jquery/jquery-2.2.3.min.js', '', 'AUTO', false); ?>"></script>
<script type="text/javascript" src="<?= OSCOM::link('Shop/ext/jquery/ui/jquery-ui-1.11.4.min.js', '', 'AUTO', false); ?>"></script>

<link href="<?= OSCOM::link('Shop/ext/bootstrap/css/bootstrap.min.css', '', 'AUTO', false); ?>" rel="stylesheet">
<link href="<?= OSCOM::link('Shop/ext/font-awesome/4.6.3/css/font-awesome.min.css', '', 'AUTO', false); ?>" rel="stylesheet">
<link href="<?= OSCOM::link('Shop/ext/smartmenus/jquery.smartmenus.bootstrap.css', '', 'AUTO', false); ?>" rel="stylesheet">
<link href="<?= OSCOM::link('Shop/ext/chartist/chartist.min.css', '', 'AUTO', false); ?>" rel="stylesheet">

<?php
  if (tep_not_null(JQUERY_DATEPICKER_I18N_CODE)) {
?>
<script type="text/javascript" src="<?= OSCOM::link('Shop/ext/jquery/ui/i18n/datepicker-' . JQUERY_DATEPICKER_I18N_CODE . '.js', '', 'AUTO', false); ?>"></script>
<script type="text/javascript">
$.datepicker.setDefaults($.datepicker.regional['<?= JQUERY_DATEPICKER_I18N_CODE; ?>']);
</script>
<?php
  }
?>

<link rel="stylesheet" type="text/css" href="<?= $oscTemplate->getPublicFile('css/stylesheet.css'); ?>">
<script src="<?= OSCOM::linkPublic('js/general.js'); ?>"></script>
</head>
<body>

<?php require($oscTemplate->getFile('header.php')); ?>

<div id="contentText" class="container-fluid">
