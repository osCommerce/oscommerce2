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

<div id="ppUpdate-dialog-confirm" title="Success!">
  <p><span class="ui-icon ui-icon-check" style="float:left; margin:0 7px 20px 0;"></span>Updates have been applied successfully!</p>
</div>

<script>
$(function() {
  $('#ppUpdate-dialog-confirm').dialog({
    autoOpen: false,
    resizable: false,
    height: 140,
    modal: true,
    close: function() {
      window.location = '<?php echo tep_href_link('paypal.php', 'action=info'); ?>';
    },
    buttons: {
      "Ok": function() {
        window.location = '<?php echo tep_href_link('paypal.php', 'action=info'); ?>';
      }
    }
  });

  OSCOM.APP.PAYPAL.getUpdatesProgress = 'retrieve';

  $('a[data-button="ppUpdateButton"]').click(function(e) {
    e.preventDefault();

    if ( OSCOM.APP.PAYPAL.getUpdatesProgress == 'retrieve' ) {
      OSCOM.APP.PAYPAL.getUpdates();
    } else if ( OSCOM.APP.PAYPAL.getUpdatesProgress == 'update' ) {
      OSCOM.APP.PAYPAL.doUpdate();
    }
  });

  (OSCOM.APP.PAYPAL.getUpdates = function() {
    $('#ppUpdateInfo').empty();

    $('a[data-button="ppUpdateButton"]').html('Retrieving &hellip;');

    $('#ppUpdateInfo').append('<div class="pp-panel pp-panel-info"><p>Retrieving update availability list &hellip;</p></div>');

    $.getJSON('<?php echo tep_href_link('paypal.php', 'action=checkVersion'); ?>', function (data) {
      $('#ppUpdateInfo').empty();

      if ( (typeof data == 'object') && ('rpcStatus' in data) && (data['rpcStatus'] == 1) ) {
        if ( data['releases'].length > 0 ) {
          OSCOM.APP.PAYPAL.getUpdatesProgress = 'update';
          OSCOM.APP.PAYPAL.versionHistory = data;

          $('a[data-button="ppUpdateButton"]').html('Apply Update').removeClass('pp-button-info').addClass('pp-button-success');

          for ( var i = 0; i < data['releases'].length; i++ ) {
            var record = data['releases'][i];

            $('#ppUpdateInfo').append('<h3 class="pp-panel-header-info">v' + OSCOM.htmlSpecialChars(record['version']) + ' <small>(' + OSCOM.htmlSpecialChars(record['date_added']) + ')</small></h3><div class="pp-panel pp-panel-info"><p>' + OSCOM.nl2br(OSCOM.htmlSpecialChars(record['changelog'])) + '</p></div>');
          }
        } else {
          $('a[data-button="ppUpdateButton"]').html('Check for Updates');

          $('#ppUpdateInfo').append('<div class="pp-panel pp-panel-info"><p>No updates are currently available.</p></div>');
        }
      } else {
        $('a[data-button="ppUpdateButton"]').html('Check for Updates');

        $('#ppUpdateInfo').append('<div class="pp-panel pp-panel-error"><p>Could not read the update availability list. Please try again.</p></div>');
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

    for ( var i = 0; i < releases.length; i++ ) {
      if ( releases[i]['version'] > OSCOM.APP.PAYPAL.version ) {
        versions.push(releases[i]['version']);
      }
    }

    versions.sort(function(a, b) { return a-b; });

    if ( versions.length > 0 ) {
      var runQueueInOrder = function(i) {
        if ( i >= versions.length ) {
          $('#ppUpdateInfo div').append('<p>-- Updates have been applied successfully! --</p>');

          $('#ppUpdate-dialog-confirm').dialog('open');

          return;
        }

        $('#ppUpdateInfo div').append('<p>' + OSCOM.htmlSpecialChars('Downloading v' + versions[i]) + ' &hellip;</p>');

        $.getJSON('<?php echo tep_href_link('paypal.php', 'action=update&subaction=download&v=APPDLV'); ?>'.replace('APPDLV', versions[i]), function (data) {
          if ( (typeof data == 'object') && ('rpcStatus' in data) && (data['rpcStatus'] == 1) ) {
            $('#ppUpdateInfo div').append('<p>' + OSCOM.htmlSpecialChars('Applying v' + versions[i]) + ' &hellip;</p>');

            $.getJSON('<?php echo tep_href_link('paypal.php', 'action=update&subaction=apply&v=APPDLV'); ?>'.replace('APPDLV', versions[i]), function (data) {
              if ( (typeof data == 'object') && ('rpcStatus' in data) && (data['rpcStatus'] == 1) ) {
              } else {
                alert('343434');
              }
            }).fail(function() {
              alert('fail 222');
            }).then(function() {
              i++;
              runQueueInOrder(i);
            });
          } else {
            alert('12121212');
          }
        }).fail(function() {
          alert('fail');
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
