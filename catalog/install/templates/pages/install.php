<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/
?>

<script>
<!--

  var dbServer;
  var dbUsername;
  var dbPassword;
  var dbName;

  var formSubmited = false;
  var formSuccess = false;

  function prepareDB() {
    if (formSubmited == true) {
      return false;
    }

    formSubmited = true;

    $('#mBox').show();

    $('#mBoxContents').html('<p><img src="images/progress.gif" align="right" hspace="5" vspace="5" />Testing database connection..</p>');

    dbServer = $('#DB_SERVER').val();
    dbUsername = $('#DB_SERVER_USERNAME').val();
    dbPassword = $('#DB_SERVER_PASSWORD').val();
    dbName = $('#DB_DATABASE').val();

    $.get('rpc.php?action=dbCheck&server=' + encodeURIComponent(dbServer) + '&username=' + encodeURIComponent(dbUsername) + '&password=' + encodeURIComponent(dbPassword) + '&name=' + encodeURIComponent(dbName), function (response) {
      var result = /\[\[([^|]*?)(?:\|([^|]*?)){0,1}\]\]/.exec(response);
      result.shift();

      if (result[0] == '1') {
        $('#mBoxContents').html('<p><img src="images/progress.gif" align="right" hspace="5" vspace="5" />The database structure is now being imported. Please be patient during this procedure.</p>');

        $.get('rpc.php?action=dbImport&server=' + encodeURIComponent(dbServer) + '&username=' + encodeURIComponent(dbUsername) + '&password='+ encodeURIComponent(dbPassword) + '&name=' + encodeURIComponent(dbName), function (response2) {
          var result2 = /\[\[([^|]*?)(?:\|([^|]*?)){0,1}\]\]/.exec(response2);
          result2.shift();

          if (result2[0] == '1') {
            $('#mBoxContents').html('<p><img src="images/success.gif" align="right" hspace="5" vspace="5" />Database imported successfully.</p>');

            formSuccess = true;

            setTimeout(function() {
              $('#installForm').submit();
            }, 2000);
          } else {
            var result2_error = result2[1].replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

            $('#mBoxContents').html('<p><img src="images/failed.gif" align="right" hspace="5" vspace="5" />There was a problem importing the database. The following error had occured:</p><p><strong>%s</strong></p><p>Please verify the connection parameters and try again.</p>'.replace('%s', result2_error));

            formSubmited = false;
          }
        }).fail(function() {
          formSubmited = false;
        });
      } else {
        var result_error = result[1].replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

        $('#mBoxContents').html('<p><img src="images/failed.gif" align="right" hspace="5" vspace="5" />There was a problem connecting to the database server. The following error had occured:</p><p><strong>%s</strong></p><p>Please verify the connection parameters and try again.</p>'.replace('%s', result_error));

        formSubmited = false;
      }
    }).fail(function() {
      formSubmited = false;
    });
  }

  $(function() {
    $('#installForm').submit(function(e) {
      if ( formSuccess == false ) {
        e.preventDefault();

        prepareDB();
      }
    });
  });

//-->
</script>

<div class="mainBlock">
  <div class="stepsBox">
    <ol>
      <li style="font-weight: bold;">Database Server</li>
      <li>Web Server</li>
      <li>Online Store Settings</li>
      <li>Finished!</li>
    </ol>
  </div>

  <h1>New Installation</h1>

  <p>This web-based installation routine will correctly setup and configure osCommerce Online Merchant to run on this server.</p>
  <p>Please follow the on-screen instructions that will take you through the database server, web server, and store configuration options. If help is needed at any stage, please consult the documentation or seek help at the community support forums.</p>
</div>

<div class="contentBlock">
  <div class="infoPane">
    <h3>Step 1: Database Server</h3>

    <div class="infoPaneContents">
      <p>The database server stores the content of the online store such as product information, customer information, and the orders that have been made.</p>
      <p>Please consult your server administrator if your database server parameters are not yet known.</p>
    </div>

  </div>

  <div class="contentPane">
    <div id="mBox">
      <div id="mBoxContents"></div>
    </div>

    <h2>Database Server</h2>

    <form name="install" id="installForm" action="install.php?step=2" method="post">

    <table border="0" width="99%" cellspacing="0" cellpadding="5" class="inputForm">
      <tr>
        <td class="inputField"><?php echo 'Database Server<br />' . osc_draw_input_field('DB_SERVER', 'localhost', 'class="text"'); ?></td>
        <td class="inputDescription">The address of the database server in the form of a hostname or IP address.</td>
      </tr>
      <tr>
        <td class="inputField"><?php echo 'Username<br />' . osc_draw_input_field('DB_SERVER_USERNAME', null, 'class="text"'); ?></td>
        <td class="inputDescription">The username used to connect to the database server.</td>
      </tr>
      <tr>
        <td class="inputField"><?php echo 'Password<br />' . osc_draw_password_field('DB_SERVER_PASSWORD', 'class="text"'); ?></td>
        <td class="inputDescription">The password that is used together with the username to connect to the database server.</td>
      </tr>
      <tr>
        <td class="inputField"><?php echo 'Database Name<br />' . osc_draw_input_field('DB_DATABASE', null, 'class="text"'); ?></td>
        <td class="inputDescription">The name of the database to hold the data in.</td>
      </tr>
    </table>

    <p><?php echo osc_draw_button('Continue', 'triangle-1-e', null, 'primary'); ?></p>

    </form>
  </div>
</div>
