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

.pp-button {
  font-size: 14px;
  color: white;
  padding: 8px 16px;
  border: 0;
  border-radius: 4px;
  text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
  text-decoration: none;
  display: inline-block;
  cursor: pointer;
  white-space: nowrap;
  vertical-align: baseline;
  text-align: center;
}

small .pp-button {
  font-size: 12px;
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
  background-image: linear-gradient(transparent, rgba(0, 0, 0, 0.05) 40%, rgba(0, 0, 0, 0.1));
}

.pp-button.pp-button-success {
  background-color: #1cb841;
}

.pp-button.pp-button-error {
  background-color: #ca3c3c;
}

.pp-button.pp-button-warning {
  background-color: #ebaa16;
}

.pp-button.pp-button-info {
  background-color: #42B8DD;
}

.pp-button.pp-button-primary {
  background-color: #0078E7;
}

.pp-panel {
  padding: 5px 10px;
}

.pp-panel.pp-panel-info {
  background-color: #e7f6ff;
}

.pp-panel.pp-panel-warning {
  background-color: #fff4dd;
}

.pp-form input, .pp-form select {
  width: 400px;
}

.pp-form .pp-panel div p label {
  font-size: 13px;
  display: block;
  font-weight: bold;
  padding-top: 15px;
  padding-bottom: 10px;
}

.pp-form .pp-panel div:first-child p label {
  padding-top: 0;
}

.pp-table {
  border: 0;
  border-spacing: 0;
  width: 100%;
  line-height: 2;
}

.pp-table thead, .pp-table-header {
  background-color: #5091cc;
  color: #fff;
  background-image: linear-gradient(transparent, rgba(0, 0, 0, 0.05) 40%, rgba(0, 0, 0, 0.1));
}

.pp-table thead th, .pp-table-header th {
  text-align: left;
}

.pp-table.pp-table-hover tbody tr:hover:not(.pp-table-header) {
  background-color: #e1f2fa;
}

.logSuccess { font-weight: bold; color: #3fad3b; background-color: #fff; border: 1px solid #3fad3b; padding: 3px; }
.logError { font-weight: bold; color: #d32828; background-color: #fff; border: 1px solid #d32828; padding: 3px; }
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
  <div style="padding: 10px 0 0 10px;">
    <a href="<?php echo tep_href_link('paypal.php'); ?>"><img src="<?php echo tep_catalog_href_link('images/apps/paypal/paypal.png', '', 'SSL'); ?>" /></a>
  </div>

  <div style="padding: 0 10px 10px 10px;">
    <?php include(DIR_FS_CATALOG . 'includes/apps/paypal/admin/content/' . basename($content)); ?>
  </div>
</div>

<?php
  include(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
