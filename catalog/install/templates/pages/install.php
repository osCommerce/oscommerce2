<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

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

    $('#mBoxContents').html('<p><i class="fa fa-spinner fa-spin fa-2x"></i> Testing database connection..</p>');

    dbServer = $('#DB_SERVER').val();
    dbUsername = $('#DB_SERVER_USERNAME').val();
    dbPassword = $('#DB_SERVER_PASSWORD').val();
    dbName = $('#DB_DATABASE').val();

    $.get('rpc.php?action=dbCheck&server=' + encodeURIComponent(dbServer) + '&username=' + encodeURIComponent(dbUsername) + '&password=' + encodeURIComponent(dbPassword) + '&name=' + encodeURIComponent(dbName), function (response) {
      var result = /\[\[([^|]*?)(?:\|([^|]*?)){0,1}\]\]/.exec(response);
      result.shift();

      if (result[0] == '1') {
        $('#mBoxContents').html('<p><i class="fa fa-spinner fa-spin fa-2x"></i> The database structure is now being imported. Please be patient during this procedure.</p>');

        $.get('rpc.php?action=dbImport&server=' + encodeURIComponent(dbServer) + '&username=' + encodeURIComponent(dbUsername) + '&password='+ encodeURIComponent(dbPassword) + '&name=' + encodeURIComponent(dbName), function (response2) {
          var result2 = /\[\[([^|]*?)(?:\|([^|]*?)){0,1}\]\]/.exec(response2);
          result2.shift();

          if (result2[0] == '1') {
            $('#mBoxContents').html('<p class="text-success"><i class="fa fa-thumbs-up fa-2x"></i> Database imported successfully.</p>');

            formSuccess = true;

            setTimeout(function() {
              $('#installForm').submit();
            }, 2000);
          } else {
            var result2_error = result2[1].replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

            $('#mBoxContents').html('<p class="text-danger"><i class="fa fa-thumbs-down fa-2x text-danger"></i> There was a problem importing the database. The following error had occured:</p><p  class="text-danger"><strong>%s</strong></p><p class="text-danger">Please verify the connection parameters and try again.</p>'.replace('%s', result2_error));

            formSubmited = false;
          }
        }).fail(function() {
          formSubmited = false;
        });
      } else {
        var result_error = result[1].replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

        $('#mBoxContents').html('<p class="text-danger"><i class="fa fa-thumbs-down fa-2x text-danger"></i> There was a problem connecting to the database server. The following error had occured:</p><p class="text-danger"><strong>%s</strong></p><p class="text-danger">Please verify the connection parameters and try again.</p></div>'.replace('%s', result_error));

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
<div class="row">
  <div class="col-sm-9">
    <div class="alert alert-info">
      <h1>New Installation</h1>

      <p>This web-based installation routine will correctly setup and configure osCommerce Online Merchant to run on this server.</p>
      <p>Please follow the on-screen instructions that will take you through the database server, web server, and store configuration options. If help is needed at any stage, please consult the documentation or seek help at the community support forums.</p>
    </div>
  </div>
  <div class="col-sm-3">
    <div class="panel panel-default">
      <div class="panel-body">
        <ol>
          <li class="text-success"><strong>Database Server</strong></li>
          <li class="text-muted">Web Server</li>
          <li class="text-muted">Online Store Settings</li>
          <li class="text-muted">Finished!</li>
        </ol>
      </div>
    </div>
    <div class="progress">
      <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100" style="width: 25%">25%</div>
    </div>
  </div>
</div>
  
<div class="clearfix"></div>

<div class="row">
  <div class="col-xs-12 col-sm-push-3 col-sm-9">

    <div id="mBox">
      <div class="well well-sm">
        <div id="mBoxContents"></div>
      </div>
    </div>
    
    <div class="page-header">
      <p class="inputRequirement pull-right text-right"><span class="glyphicon glyphicon-asterisk inputRequirement"></span> Required information</p>
      <h2>Database Server</h2>
    </div>
    
    <form name="install" id="installForm" action="install.php?step=2" method="post" class="form-horizontal" role="form">
    
      <div class="form-group has-feedback">
        <label for="dbServer" class="control-label col-xs-3">Database Server</label>
        <div class="col-xs-9">
          <?php echo osc_draw_input_field('DB_SERVER', NULL, 'required aria-required="true" id="dbServer" placeholder="localhost"'); ?>
          <span class="glyphicon glyphicon-asterisk form-control-feedback inputRequirement"></span>
          <span class="help-block">The address of the database server in the form of a hostname or IP address.</span>
        </div>
      </div>
    
      <div class="form-group has-feedback">
        <label for="userName" class="control-label col-xs-3">Username</label>
        <div class="col-xs-9">
          <?php echo osc_draw_input_field('DB_SERVER_USERNAME', NULL, 'required aria-required="true" id="userName" placeholder="Username"'); ?>
          <span class="glyphicon glyphicon-asterisk form-control-feedback inputRequirement"></span>
          <span class="help-block">The username used to connect to the database server.</span>
        </div>
      </div>
    
      <div class="form-group has-feedback">
        <label for="passWord" class="control-label col-xs-3">Password</label>
        <div class="col-xs-9">
          <?php echo osc_draw_password_field('DB_SERVER_PASSWORD', NULL, 'required aria-required="true" id="passWord"'); ?>
          <span class="glyphicon glyphicon-asterisk form-control-feedback inputRequirement"></span>
          <span class="help-block">The password that is used together with the username to connect to the database server.</span>
        </div>
      </div>
    
      <div class="form-group has-feedback">
        <label for="dbName" class="control-label col-xs-3">Database Name</label>
        <div class="col-xs-9">
          <?php echo osc_draw_input_field('DB_DATABASE', NULL, 'required aria-required="true" id="dbName" placeholder="Database"'); ?>
          <span class="glyphicon glyphicon-asterisk form-control-feedback inputRequirement"></span>
          <span class="help-block">The name of the database to hold the data in.</span>
        </div>
      </div>

      <p><?php echo osc_draw_button('Continue To Step 2', 'triangle-1-e', null, 'primary', null, 'btn-success btn-block'); ?></p>

    </form>
    
  </div>
  <div class="col-xs-12 col-sm-pull-9 col-sm-3">
    <div class="panel panel-success">
      <div class="panel-heading">
        Step 1: Database Server
      </div>
      <div class="panel-body">
        <p>The database server stores the content of the online store such as product information, customer information, and the orders that have been made.</p>
        <p>Please consult your server administrator if your database server parameters are not yet known.</p>
      </div>
    </div>
  </div>
  
</div>
