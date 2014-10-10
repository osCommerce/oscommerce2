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
      window.open('http://apps.oscommerce.com/index.php?Info&paypal&app');
    }
  });

  (OSCOM.APP.PAYPAL.getUpdates = function() {
    $('#ppUpdateInfo').empty();

    $('a[data-button="ppUpdateButton"]').html('Retrieving &hellip;');

    $('#ppUpdateInfo').append('<div class="pp-panel pp-panel-info"><p>Retrieving update availability list &hellip;</p></div>');

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

            $('a[data-button="ppUpdateButton"]').html('Apply Update').removeClass('pp-button-info').addClass('pp-button-success');

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

                $('a[data-button="ppUpdateButton"]').html('Visit App at osCommerce').removeClass('pp-button-info').addClass('pp-button-warning');

                $('#ppUpdateInfo').append('<div class="pp-panel pp-panel-warning"><p>v' + OSCOM.htmlSpecialChars(release[1]) + ' is available as an update! This can be downloaded and applied manually from the osCommerce Apps site.</p></div>');
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
            $('a[data-button="ppUpdateButton"]').html('Check for Updates');

            $('#ppUpdateInfo').append('<div class="pp-panel pp-panel-info"><p>No updates are currently available.</p></div>');

            break;

          case 'INVALID_FORMAT':
            $('a[data-button="ppUpdateButton"]').html('Check for Updates');

            $('#ppUpdateInfo').append('<div class="pp-panel pp-panel-error"><p>Could not read the update availability list. Please try again.</p></div>');

            break;
        }
      }
    }).fail(function() {
      $('#ppUpdateInfo').empty();

      $('a[data-button="ppUpdateButton"]').html('Check for Updates');

      $('#ppUpdateInfo').append('<div class="pp-panel pp-panel-error"><p>The update availability list could not be requested. Please try again.</p></div>');
    });
  })();

  OSCOM.APP.PAYPAL.doUpdate = function() {
    $('#ppUpdateInfo').empty();

    OSCOM.APP.PAYPAL.getUpdatesProgress = 'updating';

    $('a[data-button="ppUpdateButton"]').html('Applying Updates &hellip;');

    $('#ppUpdateInfo').append('<h3 class="pp-panel-header-info">Applying Updates</h3><div class="pp-panel pp-panel-info"></div>');

    var releases = OSCOM.APP.PAYPAL.versionHistory['releases'];
    var versions = [];
    var updateError = false;

    for ( var i = 0; i < releases.length; i++ ) {
      if ( releases[i]['version'] > OSCOM.APP.PAYPAL.version ) {
        versions.push(releases[i]['version']);
      }
    }

    versions.sort(function(a, b) { return a-b; });

    if ( versions.length > 0 ) {
      var runQueueInOrder = function(i) {
        if ( updateError == true ) {
          return;
        }

        if ( i >= versions.length ) {
          OSCOM.APP.PAYPAL.getUpdatesProgress = 'retrieveFresh';

          $('a[data-button="ppUpdateButton"]').html('Check for Updates').removeClass('pp-button-success').addClass('pp-button-info');

          $('#ppUpdateInfo').append('<div class="pp-panel pp-panel-success"><p>Updates have been applied successfully!</p></div>');

          return;
        }

        $('#ppUpdateInfo div').append('<p>' + OSCOM.htmlSpecialChars('Downloading v' + versions[i]) + ' &hellip;</p>');

        $.getJSON('<?php echo tep_href_link('paypal.php', 'action=update&subaction=download&v=APPDLV'); ?>'.replace('APPDLV', versions[i]), function (data) {
          if ( (typeof data == 'object') && ('rpcStatus' in data) && (data['rpcStatus'] == 1) ) {
            $('#ppUpdateInfo div').append('<p>' + OSCOM.htmlSpecialChars('Applying v' + versions[i]) + ' &hellip;</p>');

            $.getJSON('<?php echo tep_href_link('paypal.php', 'action=update&subaction=apply&v=APPDLV'); ?>'.replace('APPDLV', versions[i]), function (data) {
              if ( (typeof data == 'object') && ('rpcStatus' in data) && (data['rpcStatus'] == 1) ) {
              } else {
                updateError = true;

                OSCOM.APP.PAYPAL.getUpdatesProgress = 'retrieve';

                $('a[data-button="ppUpdateButton"]').html('Check for Updates').removeClass('pp-button-success').addClass('pp-button-info');

                $('#ppUpdateInfo').append('<h3 class="pp-panel-header-error">' + OSCOM.htmlSpecialChars('Could not apply v' + versions[i] + '!') + '</h3><div id="ppUpdateErrorLog" class="pp-panel pp-panel-error"><p>An error occured during this update.</p></div>');

                $.getJSON('<?php echo tep_href_link('paypal.php', 'action=update&subaction=log&v=APPDLV'); ?>'.replace('APPDLV', versions[i]), function (data) {
                  if ( (typeof data == 'object') && ('rpcStatus' in data) && (data['rpcStatus'] == 1) ) {
                    $('#ppUpdateErrorLog').append('<p>' + OSCOM.nl2br(OSCOM.htmlSpecialChars(data['log'])) + '</p>');
                  }
                });
              }
            }).fail(function() {
              OSCOM.APP.PAYPAL.getUpdatesProgress = 'retrieve';

              $('a[data-button="ppUpdateButton"]').html('Check for Updates').removeClass('pp-button-success').addClass('pp-button-info');

              $('#ppUpdateInfo').append('<h3 class="pp-panel-header-error">' + OSCOM.htmlSpecialChars('Could not apply v' + versions[i] + '!') + '</h3><div id="ppUpdateErrorLog" class="pp-panel pp-panel-error"><p>Could not start the procedure to apply the update. Please try again.</p></div>');
            }).then(function() {
              i++;
              runQueueInOrder(i);
            });
          } else {
            updateError = true;

            OSCOM.APP.PAYPAL.getUpdatesProgress = 'retrieve';

            $('a[data-button="ppUpdateButton"]').html('Check for Updates').removeClass('pp-button-success').addClass('pp-button-info');

            if ( (typeof data == 'object') && ('error' in data) ) {
              $('#ppUpdateInfo').append('<h3 class="pp-panel-header-error">Error!</h3><div class="pp-panel pp-panel-error"><p>' + data['error'] + '</p></div>');
            } else {
              $('#ppUpdateInfo').append('<h3 class="pp-panel-header-error">Error!</h3><div class="pp-panel pp-panel-error"><p>Could not start the procedure to download v' + OSCOM.htmlSpecialChars(versions[i]) + '. Please try again.</p></div>');
            }
          }
        }).fail(function() {
          OSCOM.APP.PAYPAL.getUpdatesProgress = 'retrieve';

          $('a[data-button="ppUpdateButton"]').html('Check for Updates').removeClass('pp-button-success').addClass('pp-button-info');

          $('#ppUpdateInfo').append('<h3 class="pp-panel-header-error">Error!</h3><div class="pp-panel pp-panel-error"><p>Could not start the procedure to download v' + OSCOM.htmlSpecialChars(versions[i]) + '. Please try again.</p></div>');
        });
      }

      runQueueInOrder(0);
    } else {
      OSCOM.APP.PAYPAL.getUpdatesProgress = 'retrieve';

      $('a[data-button="ppUpdateButton"]').html('Check for Updates').removeClass('pp-button-success').addClass('pp-button-info');

      $('#ppUpdateInfo div').append('<p>No versions could be found to update to. Please try again.</p>');
    }
  }
});
</script>
