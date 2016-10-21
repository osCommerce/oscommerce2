<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  require(DIR_FS_CATALOG . 'includes/apps/braintree/OSCOM_Braintree.php');
  $OSCOM_Braintree = new OSCOM_Braintree();

  $content = 'configure.php';
  $action = 'configure';
  $subaction = '';

  $OSCOM_Braintree->loadLanguageFile('admin.php');

  if ( isset($HTTP_GET_VARS['action']) && file_exists(DIR_FS_CATALOG . 'includes/apps/braintree/admin/actions/' . basename($HTTP_GET_VARS['action']) . '.php') ) {
    $action = basename($HTTP_GET_VARS['action']);
  }

  $OSCOM_Braintree->loadLanguageFile('admin/' . $action . '.php');

  if ( $OSCOM_Braintree->migrate() ) {
    tep_redirect(tep_href_link('braintree.php', tep_get_all_get_params()));
  }

  include(DIR_FS_CATALOG . 'includes/apps/braintree/admin/actions/' . $action . '.php');

  if ( isset($HTTP_GET_VARS['subaction']) && file_exists(DIR_FS_CATALOG . 'includes/apps/braintree/admin/actions/' . $action . '/' . basename($HTTP_GET_VARS['subaction']) . '.php') ) {
    $subaction = basename($HTTP_GET_VARS['subaction']);
  }

  if ( !empty($subaction) ) {
    include(DIR_FS_CATALOG . 'includes/apps/braintree/admin/actions/' . $action . '/' . $subaction . '.php');
  }

  include(DIR_WS_INCLUDES . 'template_top.php');
?>

<style>
.bt-container {
  font-size: 12px;
  line-height: 1.5;
}

.bt-header {
  padding: 15px;
}

#btAppInfo {
  color: #898989;
}

#btAppInfo a {
  color: #000;
  padding-left: 10px;
}

#btAppInfo a:hover {
  color: #000;
}

.bt-button {
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

small .bt-button {
  font-size: 11px;
  padding: 4px 8px;
}

.bt-button:active {
  box-shadow: 0 0 0 1px rgba(0,0,0, 0.15) inset, 0 0 6px rgba(0,0,0, 0.20) inset;
}

.bt-button:focus {
  outline: 0;
}

.bt-button:hover {
  text-decoration: none;
  background-image: linear-gradient(transparent, rgba(0, 0, 0, 0.01) 100%, rgba(0, 0, 0, 0.1));
}

.bt-button.bt-button-success {
  background-color: #1cb841;
  border-left: 1px solid #097c20;
  border-bottom: 1px solid #097c20;
}

.bt-button.bt-button-error {
  background-color: #ca3c3c;
  border-left: 1px solid #610404;
  border-bottom: 1px solid #610404;
}

.bt-button.bt-button-warning {
  background-color: #ebaa16;
  border-left: 1px solid #986008;
  border-bottom: 1px solid #986008;
}

.bt-button.bt-button-info {
  background-color: #42b8dd;
  border-left: 1px solid #177a93;
  border-bottom: 1px solid #177a93;
}

.bt-button.bt-button-primary {
  background-color: #0078e7;
  border-left: 1px solid #023c63;
  border-bottom: 1px solid #023c63;
}

.bt-panel {
  padding: 1px 10px;
  margin-bottom: 15px;
}

.bt-panel.bt-panel-info {
  background-color: #e2f2f8;
  border-left: 2px solid #97c5dd;
  color: #20619a;
}

.bt-panel.bt-panel-warning {
  background-color: #fff4dd;
  border-left: 2px solid #e2ab62;
  color: #cd7c20;
}

.bt-panel.bt-panel-success {
  background-color: #e8ffe1;
  border-left: 2px solid #a0e097;
  color: #349a20;
}

.bt-panel.bt-panel-error {
  background-color: #fceaea;
  border-left: 2px solid #df9a9a;
  color: #9a2020;
}

.bt-panel-header-info {
  background-color: #97c5dd;
  background-image: linear-gradient(transparent, rgba(0, 0, 0, 0.05) 40%, rgba(0, 0, 0, 0.1));
  font-size: 12px;
  color: #fff;
  margin: 0;
  padding: 3px 15px;
}

.bt-panel-header-warning {
  background-color: #e2ab62;
  background-image: linear-gradient(transparent, rgba(0, 0, 0, 0.05) 40%, rgba(0, 0, 0, 0.1));
  font-size: 12px;
  color: #fff;
  margin: 0;
  padding: 3px 15px;
}

.bt-panel-header-success {
  background-color: #a0e097;
  background-image: linear-gradient(transparent, rgba(0, 0, 0, 0.05) 40%, rgba(0, 0, 0, 0.1));
  font-size: 12px;
  color: #fff;
  margin: 0;
  padding: 3px 15px;
}

.bt-panel-header-error {
  background-color: #df9a9a;
  background-image: linear-gradient(transparent, rgba(0, 0, 0, 0.05) 40%, rgba(0, 0, 0, 0.1));
  font-size: 12px;
  color: #fff;
  margin: 0;
  padding: 3px 15px;
}

.bt-form input, .bt-form select {
  width: 400px;
}

.bt-form .bt-panel div p label {
  display: block;
  font-size: 12px;
  font-weight: bold;
  padding-top: 15px;
  padding-bottom: 10px;
}

.bt-form .bt-panel div:first-child p label {
  padding-top: 0;
}

.bt-table {
  background-color: #e2f2f8;
  border-left: 2px solid #97c5dd;
  border-spacing: 0;
  line-height: 2;
  margin-bottom: 15px;
  color: #20619a;
}

.bt-table thead, .bt-table-header {
  background-color: #97c5dd;
  background-image: linear-gradient(transparent, rgba(0, 0, 0, 0.05) 40%, rgba(0, 0, 0, 0.1));
  color: #fff;
  margin: 0;
  font-weight: bold;
  font-size: 12px;
}

.bt-table thead th, .bt-table-header th {
  text-align: left;
  padding: 3px 15px;
}

.bt-table tbody tr td {
  padding: 3px 15px;
}

.bt-table tbody tr td.bt-table-action {
  text-align: right;
  visibility: hidden;
  display: block;
}

.bt-table tbody tr:hover td.bt-table-action {
  visibility: visible;
}

.bt-table.bt-table-hover tbody tr:hover:not(.bt-table-header) {
  background-color: #fff;
}

.logSuccess { font-size: 11px; font-weight: bold; color: #fff; background-color: #3fad3b; padding: 4px; }
.logError { font-size: 11px; font-weight: bold; color: #fff; background-color: #d32828; padding: 4px; }

.bt-alerts ul { list-style-type: none; padding: 15px; margin: 10px; }
.bt-alerts .bt-alerts-error { background-color: #f2dede; border: 1px solid #ebccd1; border-radius: 4px; color: #a94442; }
.bt-alerts .bt-alerts-success { background-color: #dff0d8; border: 1px solid #d6e9c6; border-radius: 4px; color: #3c763d; }
.bt-alerts .bt-alerts-warning { background-color: #fcf8e3; border: 1px solid #faebcc; border-radius: 4px; color: #8a6d3b; }

.bt-button-menu {
  position: absolute;
  width: 300px;
  z-index: 100;
}

.bt-button-menu li > a {
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
    BRAINTREE: {
      version: '<?php echo $OSCOM_Braintree->getVersion(); ?>',
      versionCheckResult: <?php echo (defined('OSCOM_APP_PAYPAL_BRAINTREE_VERSION_CHECK')) ? '"' . OSCOM_APP_PAYPAL_BRAINTREE_VERSION_CHECK . '"' : 'undefined'; ?>,
      action: '<?php echo $action; ?>',
      doOnlineVersionCheck: false,
      canApplyOnlineUpdates: <?php echo class_exists('ZipArchive') && function_exists('json_encode') && function_exists('openssl_verify') ? 'true' : 'false'; ?>,
      accountTypes: {
        live: <?php echo ($OSCOM_Braintree->hasApiCredentials('live') === true) ? 'true' : 'false'; ?>,
        sandbox: <?php echo ($OSCOM_Braintree->hasApiCredentials('sandbox') === true) ? 'true' : 'false'; ?>
      },
      versionCheck: function() {
        $.get('<?php echo tep_href_link('braintree.php', 'action=checkVersion'); ?>', function (data) {
          var versions = [];

          if ( OSCOM.APP.BRAINTREE.canApplyOnlineUpdates == true ) {
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
            OSCOM.APP.BRAINTREE.versionCheckResult = [ OSCOM.dateNow.getDate(), Math.max.apply(Math, versions) ];

            OSCOM.APP.BRAINTREE.versionCheckNotify();
          }
        });
      },
      versionCheckNotify: function() {
        if ( (typeof this.versionCheckResult[0] != 'undefined') && (typeof this.versionCheckResult[1] != 'undefined') ) {
          if ( this.versionCheckResult[1] > this.version ) {
            $('#btAppUpdateNotice').show();
          }
        }
      }
    }
  }
};

if ( typeof OSCOM.APP.BRAINTREE.versionCheckResult != 'undefined' ) {
  OSCOM.APP.BRAINTREE.versionCheckResult = OSCOM.APP.BRAINTREE.versionCheckResult.split('-', 2);
}
</script>

<div class="bt-container">
  <div class="bt-header">
    <div id="btAppInfo" style="float: right;">
      <?php echo $OSCOM_Braintree->getTitle() . ' v' . $OSCOM_Braintree->getVersion() . ' <a href="' . tep_href_link('braintree.php', 'action=info') . '">' . $OSCOM_Braintree->getDef('app_link_info') . '</a> <a href="' . tep_href_link('braintree.php', 'action=privacy') . '">' . $OSCOM_Braintree->getDef('app_link_privacy') . '</a>'; ?>
    </div>

    <a href="<?php echo tep_href_link('braintree.php', 'action=' . $action); ?>"><img src="<?php echo tep_catalog_href_link('images/apps/braintree/braintree.png', '', 'SSL'); ?>" /></a>
  </div>

  <div id="btAppUpdateNotice" style="padding: 0 12px 0 12px; display: none;">
    <div class="bt-panel bt-panel-success">
      <?php echo $OSCOM_Braintree->getDef('update_available_body', array('button_view_update' => $OSCOM_Braintree->drawButton($OSCOM_Braintree->getDef('button_view_update'), tep_href_link('braintree.php', 'action=update'), 'success'))); ?>
    </div>
  </div>

<?php
  if ( $OSCOM_Braintree->hasAlert() ) {
    echo $OSCOM_Braintree->getAlerts();
  }
?>

  <div style="padding: 0 10px 10px 10px;">
<script>
// Make sure jQuery >= v1.5 is loaded for jQuery Deferred Objects (eg $.get().fail())
if ( !$.isFunction($.Deferred) ) {
  document.write('<div class="bt-panel bt-panel-error"><p>jQuery version is too old (v' + $.fn.jquery + '). Please update your Administration Tool template to use at least v1.5.</p></div>');
}
</script>

    <?php include(DIR_FS_CATALOG . 'includes/apps/braintree/admin/content/' . basename($content)); ?>
  </div>
</div>

<script>
$(function() {
  if ( (OSCOM.APP.BRAINTREE.action != 'update') && (OSCOM.APP.BRAINTREE.action != 'info') ) {
    if ( typeof OSCOM.APP.BRAINTREE.versionCheckResult == 'undefined' ) {
      OSCOM.APP.BRAINTREE.doOnlineVersionCheck = true;
    } else {
      if ( typeof OSCOM.APP.BRAINTREE.versionCheckResult[0] != 'undefined' ) {
        if ( OSCOM.dateNow.getDate() != OSCOM.APP.BRAINTREE.versionCheckResult[0] ) {
          OSCOM.APP.BRAINTREE.doOnlineVersionCheck = true;
        }
      }
    }

    if ( OSCOM.APP.BRAINTREE.doOnlineVersionCheck == true ) {
      OSCOM.APP.BRAINTREE.versionCheck();
    } else {
      OSCOM.APP.BRAINTREE.versionCheckNotify();
    }
  }
});
</script>

<?php
  include(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
