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
  <?php echo $OSCOM_PayPal->drawButton('Update', '#', 'success', 'data-button="ppStartUpdate"'); ?>
</div>

<table id="ppUpdateLog" class="pp-table pp-table-hover" width="100%">
  <thead>
    <tr>
      <th width="200">Version</th>
      <th colspan="2">Date</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td colspan="3">Retrieving update availability list &hellip;</td>
    </tr>
  </tbody>
</table>

<div id="updateInfo-dialog-confirm" title="Changelog"></div>

<script>
$(function() {
  $('#updateInfo-dialog-confirm').dialog({
    autoOpen: false,
    resizable: false,
    height: 400,
    modal: true,
    buttons: {
      Close: function() {
        $(this).dialog('close');
      }
    }
  });

  $('#ppUpdateLog').on('click', 'a[data-button="viewUpdateInfo"]', function(e) {
    e.preventDefault();

    var buttonId = $(this).closest('td').attr('id').split('_', 2);

    $('#updateInfo-dialog-confirm').html(OSCOM.nl2br(OSCOM.htmlSpecialChars(OSCOM.APP.PAYPAL.versionHistory['releases'][buttonId[1]]['changelog']))).dialog('open');
  });

  $.getJSON('<?php echo tep_href_link('paypal.php', 'action=checkVersion'); ?>', function (data) {
    $('#ppUpdateLog').find('tbody tr').remove();

    if ( ('rpcStatus' in data) && (data['rpcStatus'] == 1) ) {
      if ( data['releases'].length > 0 ) {
        OSCOM.APP.PAYPAL.versionHistory = data;

        var rowCounter = 0;

        for ( var i = 0; i < data['releases'].length; i++ ) {
          var record = data['releases'][i];

          var newRow = $('#ppUpdateLog')[0].tBodies[0].insertRow(rowCounter);

          var newCell = newRow.insertCell(0);
          newCell.innerHTML = OSCOM.htmlSpecialChars(record['version']);

          var newCell = newRow.insertCell(1);
          newCell.innerHTML = OSCOM.htmlSpecialChars(record['date_added']);

          var newCell = newRow.insertCell(2);
          newCell.id = 'viewUpdateInfo_' + i;
          newCell.innerHTML = '<small><?php echo $OSCOM_PayPal->drawButton('View', '#', 'info', 'data-button="viewUpdateInfo"'); ?></small>';
          newCell.className = 'pp-table-action';

          rowCounter++;
        }
      } else {
        var newRow = $('#ppUpdateLog')[0].tBodies[0].insertRow(0);

        var newCell = newRow.insertCell(0);
        newCell.innerHTML = OSCOM.htmlSpecialChars('No updates are available.');
        newCell.colSpan = '3';
      }
    } else {
      var newRow = $('#ppUpdateLog')[0].tBodies[0].insertRow(0);

      var newCell = newRow.insertCell(0);
      newCell.innerHTML = OSCOM.htmlSpecialChars('Could not read the update availability list. Please try again.');
      newCell.colSpan = '3';
    }
  }).fail(function() {
    $('#ppUpdateLog').find('tbody tr').remove();

    var newRow = $('#ppUpdateLog')[0].tBodies[0].insertRow(0);

    var newCell = newRow.insertCell(0);
    newCell.innerHTML = OSCOM.htmlSpecialChars('The update availability list could not be requested. Please try again.');
    newCell.colSpan = '3';
  });

  $('a[data-button="ppStartUpdate"]').click(function(e) {
    e.preventDefault();

    $('#ppUpdateLog').find('tbody tr').remove();

    var rowCounter = 0;

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
          return;
        }

        var newRow = $('#ppUpdateLog')[0].tBodies[0].insertRow(rowCounter);

        var newCell = newRow.insertCell(0);
        newCell.innerHTML = OSCOM.htmlSpecialChars('Downloading v' + versions[i]) + ' &hellip;';
        newCell.colSpan = '3';

        rowCounter++;

        $.getJSON('<?php echo tep_href_link('paypal.php', 'action=update&subaction=download&v=APPDLV'); ?>'.replace('APPDLV', versions[i]), function (data) {
          if ( (typeof data == 'object') && ('rpcStatus' in data) && (data['rpcStatus'] == 1) ) {
            var newRow = $('#ppUpdateLog')[0].tBodies[0].insertRow(rowCounter);

            var newCell = newRow.insertCell(0);
            newCell.innerHTML = OSCOM.htmlSpecialChars('Applying v' + versions[i]) + ' &hellip;';
            newCell.colSpan = '3';

            rowCounter++;

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
      var newRow = $('#ppUpdateLog')[0].tBodies[0].insertRow(rowCounter);

      var newCell = newRow.insertCell(0);
      newCell.innerHTML = OSCOM.htmlSpecialChars('Error: No versions could be found to update to. Please try again.');
      newCell.colSpan = '3';

      rowCounter++;
    }
  });
});
</script>
