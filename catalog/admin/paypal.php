<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  if ( tep_db_num_rows(tep_db_query("show tables like 'oscom_app_paypal_log'")) != 1 ) {
    $sql = <<<EOD
CREATE TABLE oscom_app_paypal_log (
  id int unsigned NOT NULL auto_increment,
  customers_id int NOT NULL,
  module varchar(8) NOT NULL,
  action varchar(255) NOT NULL,
  result tinyint NOT NULL,
  server tinyint NOT NULL,
  request text NOT NULL,
  response text NOT NULL,
  ip_address int unsigned,
  date_added datetime,
  PRIMARY KEY (id),
  KEY idx_oapl_module (module)
) CHARACTER SET utf8 COLLATE utf8_unicode_ci;

EOD;

    tep_db_query($sql);
  }

  require(DIR_FS_CATALOG . 'includes/apps/paypal/OSCOM_PayPal.php');
  $OSCOM_PayPal = new OSCOM_PayPal();

  $content = 'start.php';
  $action = 'start';
  $subaction = '';

  $OSCOM_PayPal->loadLanguageFile('admin.php');

  if ( isset($HTTP_GET_VARS['action']) && file_exists(DIR_FS_CATALOG . 'includes/apps/paypal/admin/actions/' . basename($HTTP_GET_VARS['action']) . '.php') ) {
    $action = basename($HTTP_GET_VARS['action']);
  }

  $OSCOM_PayPal->loadLanguageFile('admin/' . $action . '.php');

  if ( $action == 'start' ) {
    if ( $OSCOM_PayPal->migrate() ) {
      if ( defined('MODULE_ADMIN_DASHBOARD_INSTALLED') ) {
        $admin_dashboard_modules = explode(';', MODULE_ADMIN_DASHBOARD_INSTALLED);

        if ( !in_array('d_paypal_app.php', $admin_dashboard_modules) ) {
          $admin_dashboard_modules[] = 'd_paypal_app.php';

          tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input(implode(';', $admin_dashboard_modules)) . "' where configuration_key = 'MODULE_ADMIN_DASHBOARD_INSTALLED'");
          tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ADMIN_DASHBOARD_PAYPAL_APP_SORT_ORDER', '5000', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
        }
      }

      tep_redirect(tep_href_link('paypal.php', tep_get_all_get_params()));
    }
  }

  include(DIR_FS_CATALOG . 'includes/apps/paypal/admin/actions/' . $action . '.php');

  if ( isset($HTTP_GET_VARS['subaction']) && file_exists(DIR_FS_CATALOG . 'includes/apps/paypal/admin/actions/' . $action . '/' . basename($HTTP_GET_VARS['subaction']) . '.php') ) {
    $subaction = basename($HTTP_GET_VARS['subaction']);
  }

  if ( !empty($subaction) ) {
    include(DIR_FS_CATALOG . 'includes/apps/paypal/admin/actions/' . $action . '/' . $subaction . '.php');
  }

  include(DIR_FS_ADMIN . 'includes/template_top.php');
?>

<style>
.pp-container {
  font-size: 12px;
  line-height: 1.5;
}

.pp-header {
  padding: 15px;
}

#ppAppInfo {
  color: #898989;
}

#ppAppInfo a {
  color: #000;
  padding-left: 10px;
}

#ppAppInfo a:hover {
  color: #000;
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

.pp-panel.pp-panel-success {
  background-color: #e8ffe1;
  border-left: 2px solid #a0e097;
  color: #349a20;
}

.pp-panel.pp-panel-error {
  background-color: #fceaea;
  border-left: 2px solid #df9a9a;
  color: #9a2020;
}

.pp-panel-header-info {
  background-color: #97c5dd;
  background-image: linear-gradient(transparent, rgba(0, 0, 0, 0.05) 40%, rgba(0, 0, 0, 0.1));
  font-size: 12px;
  color: #fff;
  margin: 0;
  padding: 3px 15px;
}

.pp-panel-header-warning {
  background-color: #e2ab62;
  background-image: linear-gradient(transparent, rgba(0, 0, 0, 0.05) 40%, rgba(0, 0, 0, 0.1));
  font-size: 12px;
  color: #fff;
  margin: 0;
  padding: 3px 15px;
}

.pp-panel-header-success {
  background-color: #a0e097;
  background-image: linear-gradient(transparent, rgba(0, 0, 0, 0.05) 40%, rgba(0, 0, 0, 0.1));
  font-size: 12px;
  color: #fff;
  margin: 0;
  padding: 3px 15px;
}

.pp-panel-header-error {
  background-color: #df9a9a;
  background-image: linear-gradient(transparent, rgba(0, 0, 0, 0.05) 40%, rgba(0, 0, 0, 0.1));
  font-size: 12px;
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

.pp-table tbody tr td.pp-table-action {
  text-align: right;
  visibility: hidden;
  display: block;
}

.pp-table tbody tr:hover td.pp-table-action {
  visibility: visible;
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

.pp-button-menu {
  position: absolute;
  width: 300px;
  z-index: 100;
}

.pp-button-menu li > a {
  display: block;
}
</style>

<script>
if ( typeof jQuery == 'undefined' ) {
  document.write('<scr' + 'ipt src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></scr' + 'ipt>');
}
</script>
<script>
if ( typeof jQuery.ui == 'undefined' ) {
  document.write('<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/redmond/jquery-ui.css" />');
  document.write('<scr' + 'ipt src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></scr' + 'ipt>');
/* Custom jQuery UI */
  document.write('<style>.ui-widget { font-family: Lucida Grande, Lucida Sans, Verdana, Arial, sans-serif; font-size: 11px; } .ui-dialog { min-width: 500px; }</style>');
}
</script>

<script>
var OSCOM = {
  dateNow: new Date(),
  htmlSpecialChars: function(string) {
    if ( string == null ) {
      string = '';
    }

    return $('<span />').text(string).html();
  },
  nl2br: function(string) {
    return string.replace(/\n/g, '<br />');
  },
  APP: {
    PAYPAL: {
      version: '<?php echo $OSCOM_PayPal->getVersion(); ?>',
      versionCheckResult: <?php echo (defined('OSCOM_APP_PAYPAL_VERSION_CHECK')) ? '"' . OSCOM_APP_PAYPAL_VERSION_CHECK . '"' : 'undefined'; ?>,
      action: '<?php echo $action; ?>',
      doOnlineVersionCheck: false,
      canApplyOnlineUpdates: <?php echo class_exists('ZipArchive') && function_exists('json_encode') && function_exists('openssl_verify') ? 'true' : 'false'; ?>,
      accountTypes: {
        live: <?php echo ($OSCOM_PayPal->hasApiCredentials('live') === true) ? 'true' : 'false'; ?>,
        sandbox: <?php echo ($OSCOM_PayPal->hasApiCredentials('sandbox') === true) ? 'true' : 'false'; ?>
      },
      versionCheck: function() {
        $.get('<?php echo tep_href_link('paypal.php', 'action=checkVersion'); ?>', function (data) {
          var versions = [];

          if ( OSCOM.APP.PAYPAL.canApplyOnlineUpdates == true ) {
            try {
              data = $.parseJSON(data);
            } catch (ex) {
            }

            if ( (typeof data == 'object') && ('rpcStatus' in data) && (data['rpcStatus'] == 1) && ('releases' in data) && (data['releases'].length > 0) ) {
              for ( var i = 0; i < data['releases'].length; i++ ) {
                versions.push(data['releases'][i]['version']);
              }
            }
          } else {
            if ( (typeof data == 'string') && (data.indexOf('rpcStatus') > -1) ) {
              var result = data.split("\n", 2);

              if ( result.length == 2 ) {
                var rpcStatus = result[0].split('=', 2);

                if ( rpcStatus[1] == 1 ) {
                  var release = result[1].split('=', 2);

                  versions.push(release[1]);
                }
              }
            }
          }

          if ( versions.length > 0 ) {
            OSCOM.APP.PAYPAL.versionCheckResult = [ OSCOM.dateNow.getDate(), Math.max.apply(Math, versions) ];

            OSCOM.APP.PAYPAL.versionCheckNotify();
          }
        });
      },
      versionCheckNotify: function() {
        if ( (typeof this.versionCheckResult[0] != 'undefined') && (typeof this.versionCheckResult[1] != 'undefined') ) {
          if ( this.versionCheckResult[1] > this.version ) {
            $('#ppAppUpdateNotice').show();
          }
        }
      }
    }
  }
};

if ( typeof OSCOM.APP.PAYPAL.versionCheckResult != 'undefined' ) {
  OSCOM.APP.PAYPAL.versionCheckResult = OSCOM.APP.PAYPAL.versionCheckResult.split('-', 2);
}
</script>

<div class="pp-container">
  <div class="pp-header">
    <div id="ppAppInfo" style="float: right;">
      <?php echo $OSCOM_PayPal->getTitle() . ' v' . $OSCOM_PayPal->getVersion() . ' <a href="' . tep_href_link('paypal.php', 'action=info') . '">' . $OSCOM_PayPal->getDef('app_link_info') . '</a> <a href="' . tep_href_link('paypal.php', 'action=privacy') . '">' . $OSCOM_PayPal->getDef('app_link_privacy') . '</a>'; ?>
    </div>

    <a href="<?php echo tep_href_link('paypal.php', 'action=' . $action); ?>"><img src="<?php echo tep_catalog_href_link('images/apps/paypal/paypal.png', '', 'SSL'); ?>" /></a>
  </div>

  <div id="ppAppUpdateNotice" style="padding: 0 12px 0 12px; display: none;">
    <div class="pp-panel pp-panel-success">
      <?php echo $OSCOM_PayPal->getDef('update_available_body', array('button_view_update' => $OSCOM_PayPal->drawButton($OSCOM_PayPal->getDef('button_view_update'), tep_href_link('paypal.php', 'action=update'), 'success'))); ?>
    </div>
  </div>

<?php
  if ( $OSCOM_PayPal->hasAlert() ) {
    echo $OSCOM_PayPal->getAlerts();
  }
?>

  <div style="padding: 0 10px 10px 10px;">
<script>
// Make sure jQuery >= v1.5 is loaded for jQuery Deferred Objects (eg $.get().fail())
if ( !$.isFunction($.Deferred) ) {
  document.write('<div class="pp-panel pp-panel-error"><p>jQuery version is too old (v' + $.fn.jquery + '). Please update your Administration Tool template to use at least v1.5.</p></div>');
}
</script>

    <?php include(DIR_FS_CATALOG . 'includes/apps/paypal/admin/content/' . basename($content)); ?>
  </div>
</div>

<script>
$(function() {
  if ( (OSCOM.APP.PAYPAL.action != 'update') && (OSCOM.APP.PAYPAL.action != 'info') ) {
    if ( typeof OSCOM.APP.PAYPAL.versionCheckResult == 'undefined' ) {
      OSCOM.APP.PAYPAL.doOnlineVersionCheck = true;
    } else {
      if ( typeof OSCOM.APP.PAYPAL.versionCheckResult[0] != 'undefined' ) {
        if ( OSCOM.dateNow.getDate() != OSCOM.APP.PAYPAL.versionCheckResult[0] ) {
          OSCOM.APP.PAYPAL.doOnlineVersionCheck = true;
        }
      }
    }

    if ( OSCOM.APP.PAYPAL.doOnlineVersionCheck == true ) {
      OSCOM.APP.PAYPAL.versionCheck();
    } else {
      OSCOM.APP.PAYPAL.versionCheckNotify();
    }
  }
});
</script>

<?php
  include(DIR_FS_ADMIN . 'includes/template_bottom.php');
  require(DIR_FS_ADMIN . 'includes/application_bottom.php');
?>
