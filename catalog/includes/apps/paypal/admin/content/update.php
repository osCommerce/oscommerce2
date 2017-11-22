<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/
?>

<div style="padding-bottom: 15px;">
  <?php echo $OSCOM_PayPal->drawButton('&nbsp;', '#', 'info', 'data-button="ppUpdateButton"'); ?>
</div>

<div id="ppUpdateInfo"></div>

<script>
$(function() {
  OSCOM.APP.PAYPAL.getUpdatesProgress = 'retrieve';

  $('a[data-button="ppUpdateButton"]').click(function(e) {
    e.preventDefault();

    if ( OSCOM.APP.PAYPAL.getUpdatesProgress == 'retrieve' ) {
      OSCOM.APP.PAYPAL.getUpdates();
    } else if ( OSCOM.APP.PAYPAL.getUpdatesProgress == 'update' ) {
      OSCOM.APP.PAYPAL.doUpdate();
    } else if ( OSCOM.APP.PAYPAL.getUpdatesProgress == 'retrieveFresh' ) {
      window.location('<?php echo tep_href_link('paypal.php', 'action=update'); ?>');
    } else if ( OSCOM.APP.PAYPAL.getUpdatesProgress == 'manualDownload' ) {
      window.open('https://apps.oscommerce.com/index.php?Info&paypal&app');
    }
  });

  (OSCOM.APP.PAYPAL.getUpdates = function() {
    var def = {
      'button_apply_update': '<?php echo addslashes($OSCOM_PayPal->getDef('button_apply_update')); ?>',
      'button_check_for_updates': '<?php echo addslashes($OSCOM_PayPal->getDef('button_check_for_updates')); ?>',
      'button_retrieving_progress': '<?php echo addslashes($OSCOM_PayPal->getDef('button_retrieving_progress')); ?>',
      'button_visit_app_page': '<?php echo addslashes($OSCOM_PayPal->getDef('button_visit_app_page')); ?>',
      'could_not_request_update_list': '<?php echo addslashes($OSCOM_PayPal->getDef('could_not_request_update_list')); ?>',
      'invalid_update_list_format': '<?php echo addslashes($OSCOM_PayPal->getDef('invalid_update_list_format')); ?>',
      'manual_update_available': '<?php echo addslashes($OSCOM_PayPal->getDef('manual_update_available')); ?>',
      'no_updates_available': '<?php echo addslashes($OSCOM_PayPal->getDef('no_updates_available')); ?>',
      'retrieving_update_list': '<?php echo addslashes($OSCOM_PayPal->getDef('retrieving_update_list')); ?>'
    };

    $('#ppUpdateInfo').empty();

    $('a[data-button="ppUpdateButton"]').html(def['button_retrieving_progress']);

    $('#ppUpdateInfo').append('<div class="pp-panel pp-panel-info"><p>' + def['retrieving_update_list'] + '</p></div>');

    $.get('<?php echo tep_href_link('paypal.php', 'action=checkVersion'); ?>', function (data) {
      var error = false;

      $('#ppUpdateInfo').empty();

      if ( OSCOM.APP.PAYPAL.canApplyOnlineUpdates == true ) {
        try {
          data = $.parseJSON(data);
        } catch (ex) {
        }

        if ( (typeof data == 'object') && ('rpcStatus' in data) && (data['rpcStatus'] == 1) ) {
          if ( ('releases' in data) && (data['releases'].length > 0) ) {
            OSCOM.APP.PAYPAL.getUpdatesProgress = 'update';
            OSCOM.APP.PAYPAL.versionHistory = data;

            $('a[data-button="ppUpdateButton"]').html(def['button_apply_update']).removeClass('pp-button-info').addClass('pp-button-success');

            for ( var i = 0; i < data['releases'].length; i++ ) {
              var record = data['releases'][i];

              $('#ppUpdateInfo').append('<h3 class="pp-panel-header-info">v' + OSCOM.htmlSpecialChars(record['version']) + ' <small>(' + OSCOM.htmlSpecialChars(record['date_added']) + ')</small></h3><div class="pp-panel pp-panel-info"><p>' + OSCOM.nl2br(OSCOM.htmlSpecialChars(record['changelog'])) + '</p></div>');
            }
          } else {
            error = 'NO_UPDATE';
          }
        } else {
          error = 'INVALID_FORMAT';
        }
      } else {
        if ( (typeof data == 'string') && (data.indexOf('rpcStatus') > -1) ) {
          var result = data.split("\n", 2);

          if ( result.length == 2 ) {
            var rpcStatus = result[0].split('=', 2);

            if ( rpcStatus[1] == 1 ) {
              var release = result[1].split('=', 2);

              if ( release[1] > OSCOM.APP.PAYPAL.version ) {
                OSCOM.APP.PAYPAL.getUpdatesProgress = 'manualDownload';

                $('a[data-button="ppUpdateButton"]').html(def['button_visit_app_page']).removeClass('pp-button-info').addClass('pp-button-warning');

                $('#ppUpdateInfo').append('<div class="pp-panel pp-panel-warning"><p>' + def['manual_update_available'].replace(':version', OSCOM.htmlSpecialChars(release[1])) + '</p></div>');
              } else {
                error = 'NO_UPDATE';
              }
            } else {
              error = 'INVALID_FORMAT';
            }
          } else {
            error = 'INVALID_FORMAT';
          }
        } else {
          error = 'INVALID_FORMAT';
        }
      }

      if ( error != false ) {
        switch ( error ) {
          case 'NO_UPDATE':
            $('a[data-button="ppUpdateButton"]').html(def['button_check_for_updates']);

            $('#ppUpdateInfo').append('<div class="pp-panel pp-panel-info"><p>' + def['no_updates_available'] + '</p></div>');

            break;

          case 'INVALID_FORMAT':
            $('a[data-button="ppUpdateButton"]').html(def['button_check_for_updates']);

            $('#ppUpdateInfo').append('<div class="pp-panel pp-panel-error"><p>' + lang['invalid_update_list_format'] + '</p></div>');

            break;
        }
      }
    }).fail(function() {
      $('#ppUpdateInfo').empty();

      $('a[data-button="ppUpdateButton"]').html(def['button_check_for_updates']);

      $('#ppUpdateInfo').append('<div class="pp-panel pp-panel-error"><p>' + lang['could_not_request_update_list'] + '</p></div>');
    });
  })();

  OSCOM.APP.PAYPAL.doUpdate = function() {
    var def = {
      'button_applying_updates_progress': '<?php echo addslashes($OSCOM_PayPal->getDef('button_applying_updates_progress')); ?>',
      'button_check_for_updates': '<?php echo addslashes($OSCOM_PayPal->getDef('button_check_for_updates')); ?>',
      'applying_updates_heading': '<?php echo addslashes($OSCOM_PayPal->getDef('applying_updates_heading')); ?>',
      'applying_updates_success': '<?php echo addslashes($OSCOM_PayPal->getDef('applying_updates_success')); ?>',
      'downloading_version_progress': '<?php echo addslashes($OSCOM_PayPal->getDef('downloading_version_progress')); ?>',
      'applying_version_progress': '<?php echo addslashes($OSCOM_PayPal->getDef('applying_version_progress')); ?>',
      'error_applying_heading': '<?php echo addslashes($OSCOM_PayPal->getDef('error_applying_heading')); ?>',
      'error_applying': '<?php echo addslashes($OSCOM_PayPal->getDef('error_applying')); ?>',
      'error_applying_start': '<?php echo addslashes($OSCOM_PayPal->getDef('error_applying_start')); ?>',
      'error_heading': '<?php echo addslashes($OSCOM_PayPal->getDef('error_heading')); ?>',
      'error_download_start': '<?php echo addslashes($OSCOM_PayPal->getDef('error_download_start')); ?>',
      'no_updates_found': '<?php echo addslashes($OSCOM_PayPal->getDef('no_updates_found')); ?>'
    }

    $('#ppUpdateInfo').empty();

    OSCOM.APP.PAYPAL.getUpdatesProgress = 'updating';

    $('a[data-button="ppUpdateButton"]').html(def['button_applying_updates_progress']);

    $('#ppUpdateInfo').append('<h3 class="pp-panel-header-info">' + def['applying_updates_heading'] + '</h3><div class="pp-panel pp-panel-info"></div>');

    var releases = OSCOM.APP.PAYPAL.versionHistory['releases'];
    var versions = [];
    var updateError = false;

    for ( var i = 0; i < releases.length; i++ ) {
      if ( releases[i]['version'] > OSCOM.APP.PAYPAL.version ) {
        versions.push(releases[i]['version']);
      }
    }

    versions.sort(function(a, b) {
      return a - b;
    });

    if ( versions.length > 0 ) {
      var runQueueInOrder = function(i) {
        if ( updateError == true ) {
          return;
        }

        if ( i >= versions.length ) {
          OSCOM.APP.PAYPAL.getUpdatesProgress = 'retrieveFresh';

          $('a[data-button="ppUpdateButton"]').html(def['button_check_for_updates']).removeClass('pp-button-success').addClass('pp-button-info');

          $('#ppUpdateInfo').append('<div class="pp-panel pp-panel-success"><p>' + def['applying_updates_success'] + '</p></div>');

          return;
        }

        $('#ppUpdateInfo div').append('<p>' + def['downloading_version_progress'].replace(':version', OSCOM.htmlSpecialChars(versions[i])) + '</p>');

        $.getJSON('<?php echo tep_href_link('paypal.php', 'action=update&subaction=download&v=APPDLV'); ?>'.replace('APPDLV', versions[i]), function (data) {
          if ( (typeof data == 'object') && ('rpcStatus' in data) && (data['rpcStatus'] == 1) ) {
            $('#ppUpdateInfo div').append('<p>' + def['applying_version_progress'].replace(':version', OSCOM.htmlSpecialChars(versions[i])) + '</p>');

            $.getJSON('<?php echo tep_href_link('paypal.php', 'action=update&subaction=apply&v=APPDLV'); ?>'.replace('APPDLV', versions[i]), function (data) {
              if ( (typeof data == 'object') && ('rpcStatus' in data) && (data['rpcStatus'] == 1) ) {
              } else {
                updateError = true;

                OSCOM.APP.PAYPAL.getUpdatesProgress = 'retrieve';

                $('a[data-button="ppUpdateButton"]').html(def['button_check_for_updates']).removeClass('pp-button-success').addClass('pp-button-info');

                $('#ppUpdateInfo').append('<h3 class="pp-panel-header-error">' + def['error_applying_heading'].replace(':version', OSCOM.htmlSpecialChars(versions[i])) + '</h3><div id="ppUpdateErrorLog" class="pp-panel pp-panel-error"><p>' + def['error_applying'] + '</p></div>');

                $.getJSON('<?php echo tep_href_link('paypal.php', 'action=update&subaction=log&v=APPDLV'); ?>'.replace('APPDLV', versions[i]), function (data) {
                  if ( (typeof data == 'object') && ('rpcStatus' in data) && (data['rpcStatus'] == 1) ) {
                    $('#ppUpdateErrorLog').append('<p>' + OSCOM.nl2br(OSCOM.htmlSpecialChars(data['log'])) + '</p>');
                  }
                });
              }
            }).fail(function() {
              OSCOM.APP.PAYPAL.getUpdatesProgress = 'retrieve';

              $('a[data-button="ppUpdateButton"]').html(def['button_check_for_updates']).removeClass('pp-button-success').addClass('pp-button-info');

              $('#ppUpdateInfo').append('<h3 class="pp-panel-header-error">' + def['error_applying_heading'].replace(':version', OSCOM.htmlSpecialChars(versions[i])) + '</h3><div id="ppUpdateErrorLog" class="pp-panel pp-panel-error"><p>' + def['error_applying_start'] + '</p></div>');
            }).then(function() {
              i++;
              runQueueInOrder(i);
            });
          } else {
            updateError = true;

            OSCOM.APP.PAYPAL.getUpdatesProgress = 'retrieve';

            $('a[data-button="ppUpdateButton"]').html(def['button_check_for_updates']).removeClass('pp-button-success').addClass('pp-button-info');

            if ( (typeof data == 'object') && ('error' in data) ) {
              $('#ppUpdateInfo').append('<h3 class="pp-panel-header-error">' + def['error_heading'] + '</h3><div class="pp-panel pp-panel-error"><p>' + data['error'] + '</p></div>');
            } else {
              $('#ppUpdateInfo').append('<h3 class="pp-panel-header-error">' + def['error_heading'] + '</h3><div class="pp-panel pp-panel-error"><p>' + def['error_download_start'].replace(':version', OSCOM.htmlSpecialChars(versions[i])) + '</p></div>');
            }
          }
        }).fail(function() {
          OSCOM.APP.PAYPAL.getUpdatesProgress = 'retrieve';

          $('a[data-button="ppUpdateButton"]').html(def['button_check_for_updates']).removeClass('pp-button-success').addClass('pp-button-info');

          $('#ppUpdateInfo').append('<h3 class="pp-panel-header-error">' + def['error_heading'] + '</h3><div class="pp-panel pp-panel-error"><p>' + def['error_download_start'].replace(':version', OSCOM.htmlSpecialChars(versions[i])) + '</p></div>');
        });
      }

      runQueueInOrder(0);
    } else {
      OSCOM.APP.PAYPAL.getUpdatesProgress = 'retrieve';

      $('a[data-button="ppUpdateButton"]').html(def['button_check_for_updates']).removeClass('pp-button-success').addClass('pp-button-info');

      $('#ppUpdateInfo div').append('<p>' + def['no_updates_found'] + '</p>');
    }
  }
});
</script>
