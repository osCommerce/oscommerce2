<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
ini_set('display_errors', '1');

  if ( tep_db_num_rows(tep_db_query("show tables like 'oscom_app_paypal_log'")) != 1 ) {
    $sql = <<<EOD
CREATE TABLE oscom_app_paypal_log (
  id int unsigned NOT NULL auto_increment,
  customers_id int NOT NULL,
  module varchar(3) NOT NULL,
  action varchar(255) NOT NULL,
  result tinyint NOT NULL,
  server tinyint NOT NULL,
  request text NOT NULL,
  response text NOT NULL,
  ip_address int unsigned,
  date_added datetime,
  PRIMARY KEY (id),
  KEY idx_oapl_module (module)
);
EOD;

    tep_db_query($sql);
  }

  require(DIR_FS_CATALOG . 'includes/apps/paypal/OSCOM_PayPal.php');
  $OSCOM_PayPal = new OSCOM_PayPal();

  if ( $OSCOM_PayPal->migrate() ) {
    tep_redirect(tep_href_link('paypal.php', tep_get_all_get_params()));
  }

  $content = 'start.php';

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : 'start');

  if ( file_exists(DIR_FS_CATALOG . 'includes/apps/paypal/admin/actions/' . basename($action) . '.php') ) {
    include(DIR_FS_CATALOG . 'includes/apps/paypal/admin/actions/' . basename($action) . '.php');
  }

  $subaction = (isset($HTTP_GET_VARS['subaction']) ? $HTTP_GET_VARS['subaction'] : '');

  if ( !empty($subaction) && file_exists(DIR_FS_CATALOG . 'includes/apps/paypal/admin/actions/' . basename($action) . '/' . basename($subaction) . '.php') ) {
    include(DIR_FS_CATALOG . 'includes/apps/paypal/admin/actions/' . basename($action) . '/' . basename($subaction) . '.php');
  }

  include(DIR_WS_INCLUDES . 'template_top.php');
?>

<style>
.pp-container {
  font-size: 12px;
  line-height: 1.5;
}

.pp-header {
  padding: 15px 0 15px 15px;
}

.pp-button {
  font-size: 12px;
  font-weight: bold;
  color: white;
  padding: 6px 10px;
  border: 0;
  border-radius: 4px;
  text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
  text-decoration: none;
  display: inline-block;
  cursor: pointer;
  white-space: nowrap;
  vertical-align: baseline;
  text-align: center;
  background-image: linear-gradient(transparent, rgba(0, 0, 0, 0.05) 40%, rgba(0, 0, 0, 0.1));
}

small .pp-button {
  font-size: 11px;
  padding: 4px 8px;
}

.pp-button:active {
  box-shadow: 0 0 0 1px rgba(0,0,0, 0.15) inset, 0 0 6px rgba(0,0,0, 0.20) inset;
}

.pp-button:focus {
  outline: 0;
}

.pp-button:hover {
  text-decoration: none;
  background-image: linear-gradient(transparent, rgba(0, 0, 0, 0.01) 100%, rgba(0, 0, 0, 0.1));
}

.pp-button.pp-button-success {
  background-color: #1cb841;
  border-left: 1px solid #097c20;
  border-bottom: 1px solid #097c20;
}

.pp-button.pp-button-error {
  background-color: #ca3c3c;
  border-left: 1px solid #610404;
  border-bottom: 1px solid #610404;
}

.pp-button.pp-button-warning {
  background-color: #ebaa16;
  border-left: 1px solid #986008;
  border-bottom: 1px solid #986008;
}

.pp-button.pp-button-info {
  background-color: #42b8dd;
  border-left: 1px solid #177a93;
  border-bottom: 1px solid #177a93;
}

.pp-button.pp-button-primary {
  background-color: #0078e7;
  border-left: 1px solid #023c63;
  border-bottom: 1px solid #023c63;
}

.pp-panel {
  padding: 1px 10px;
  margin-bottom: 15px;
}

.pp-panel.pp-panel-info {
  background-color: #e2f2f8;
  border-left: 2px solid #97c5dd;
  color: #20619a;
}

.pp-panel.pp-panel-warning {
  background-color: #fff4dd;
  border-left: 2px solid #e2ab62;
  color: #cd7c20;
}

.pp-panel-header-info {
  background-color: #97c5dd;
  background-image: linear-gradient(transparent, rgba(0, 0, 0, 0.05) 40%, rgba(0, 0, 0, 0.1));
  color: #fff;
  margin: 0;
  padding: 3px 15px;
}

.pp-panel-header-warning {
  background-color: #e2ab62;
  background-image: linear-gradient(transparent, rgba(0, 0, 0, 0.05) 40%, rgba(0, 0, 0, 0.1));
  color: #fff;
  margin: 0;
  padding: 3px 15px;
}

.pp-form input, .pp-form select {
  width: 400px;
}

.pp-form .pp-panel div p label {
  display: block;
  font-size: 12px;
  font-weight: bold;
  padding-top: 15px;
  padding-bottom: 10px;
}

.pp-form .pp-panel div:first-child p label {
  padding-top: 0;
}

.pp-table {
  background-color: #e2f2f8;
  border-left: 2px solid #97c5dd;
  border-spacing: 0;
  line-height: 2;
  margin-bottom: 15px;
  color: #20619a;
}

.pp-table thead, .pp-table-header {
  background-color: #97c5dd;
  background-image: linear-gradient(transparent, rgba(0, 0, 0, 0.05) 40%, rgba(0, 0, 0, 0.1));
  color: #fff;
  margin: 0;
  font-weight: bold;
  font-size: 12px;
}

.pp-table thead th, .pp-table-header th {
  text-align: left;
  padding: 3px 15px;
}

.pp-table tbody tr td {
  padding: 3px 15px;
}

.pp-table.pp-table-hover tbody tr:hover:not(.pp-table-header) {
  background-color: #fff;
}

.logSuccess { font-size: 11px; font-weight: bold; color: #fff; background-color: #3fad3b; padding: 4px; }
.logError { font-size: 11px; font-weight: bold; color: #fff; background-color: #d32828; padding: 4px; }

.pp-alerts ul { list-style-type: none; padding: 15px; margin: 10px; }
.pp-alerts .pp-alerts-error { background-color: #f2dede; border: 1px solid #ebccd1; border-radius: 4px; color: #a94442; }
.pp-alerts .pp-alerts-success { background-color: #dff0d8; border: 1px solid #d6e9c6; border-radius: 4px; color: #3c763d; }
.pp-alerts .pp-alerts-warning { background-color: #fcf8e3; border: 1px solid #faebcc; border-radius: 4px; color: #8a6d3b; }
}
</style>

<script>
if ( typeof jQuery == 'undefined' ) {
  document.write('<scr' + 'ipt src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></scr' + 'ipt>');
}

if ( typeof jQuery.ui == 'undefined' ) {
  document.write('<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/redmond/jquery-ui.css" />');
  document.write('<scr' + 'ipt src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></scr' + 'ipt>');
}
</script>

<div class="pp-container">
  <div class="pp-header">
    <a href="<?php echo tep_href_link('paypal.php'); ?>"><img src="<?php echo tep_catalog_href_link('images/apps/paypal/paypal.png', '', 'SSL'); ?>" /></a>
  </div>

<?php
  if ( $OSCOM_PayPal->hasAlert() ) {
    echo $OSCOM_PayPal->getAlerts();
  }
?>

  <div style="padding: 0 10px 10px 10px;">
    <?php include(DIR_FS_CATALOG . 'includes/apps/paypal/admin/content/' . basename($content)); ?>
  </div>
</div>

<?php
  include(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
