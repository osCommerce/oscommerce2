<?php
use OSC\OM\HTML;
?>

<div class="row">
  <div class="col-sm-9">
    <div class="alert alert-info">
      <h2>New Installation</h2>

      <p>This web-based installation routine will correctly setup and configure osCommerce Online Merchant to run on this server.</p>
      <p>Please follow the on-screen instructions that will take you through the database server, web server, and store configuration options. If help is needed at any stage, please consult the <a href="https://library.oscommerce.com" target="_blank" class="alert-link">osCommerce documentation</a>, seek help at the <a href="http://forums.oscommerce.com" target="_blank" class="alert-link">osCommerce community forums</a>, visit the <a href="https://www.oscommerce.com/Support" target="_blank" class="alert-link">osCommerce support page</a>, or send an enquiry to your server administrator or hosting server provider.</p>
    </div>
  </div>

  <div class="col-sm-3">
    <div class="panel panel-primary">
      <div class="panel-heading">
        <p>Step 1/4</p>

        <ol>
          <li><strong>&gt; Database Server</strong></li>
          <li>Web Server</li>
          <li>Online Store Settings</li>
          <li>Finished!</li>
        </ol>
      </div>
    </div>

    <div class="progress">
      <div class="progress-bar progress-bar-primary progress-bar-striped" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100" style="width: 25%">25%</div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-xs-12 col-sm-push-3 col-sm-9">
    <div id="mBox"></div>

    <h1>Database Server</h1>

    <form name="install" id="installForm" action="install.php?step=2" method="post">
      <div class="form-group has-feedback">
        <label for="dbServer">Database Server</label>
        <?php echo HTML::inputField('DB_SERVER', null, 'required aria-required="true" id="dbServer" placeholder="localhost"'); ?>
        <span class="help-block">The address of the database server in the form of a hostname or IP address.</span>
      </div>

      <div class="form-group has-feedback">
        <label for="username">Username</label>
        <?php echo HTML::inputField('DB_SERVER_USERNAME', null, 'required aria-required="true" id="username"'); ?>
        <span class="help-block">The username used to connect to the database server.</span>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <?php echo HTML::passwordField('DB_SERVER_PASSWORD', null, 'id="password"'); ?>
        <span class="help-block">The password that is used together with the username to connect to the database server.</span>
      </div>

      <div class="form-group has-feedback">
        <label for="dbName">Database Name</label>
        <?php echo HTML::inputField('DB_DATABASE', null, 'required aria-required="true" id="dbName"'); ?>
        <span class="help-block">The name of the database to hold the data in.</span>
      </div>

      <div class="form-group">
        <label for="dbTablePrefix">Table Prefix</label>
        <?php echo HTML::inputField('DB_TABLE_PREFIX', 'osc_', 'id="dbTablePrefix"'); ?>
        <span class="help-block">Prefix all table names in the database with this value.</span>
      </div>

      <p>
        <?=
          HTML::button('Continue to Step 2', 'triangle-1-e', null, ['params' => 'id="buttonDoImport"'], 'btn-success') . '&nbsp;' .
          HTML::button('or continue and skip database import', null, null, ['params' => 'id="buttonSkipImport"'], 'btn-link');
        ?>
      </p>
    </form>
  </div>

  <div class="col-xs-12 col-sm-pull-9 col-sm-3">
    <div class="panel panel-success">
      <div class="panel-heading">
        <div class="panel-title">
          Step 1: Database Server
        </div>
      </div>

      <div class="panel-body">
        <p>The database server stores the content of the online store such as product information, customer information, and the orders that have been made.</p>
        <p>Please consult your server administrator if your database server parameters are not yet known.</p>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="installModal" tabindex="-1" role="dialog" aria-labelledby="installModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="installModalLabel">Please wait..</h4>
      </div>

      <div class="modal-body"></div>
    </div>
  </div>
</div>

<script>
$(function() {
  var formSubmited = false;
  var formSuccess = false;
  var dbNameToCreate;
  var doImport = true;

  function prepareDB() {
    if (formSubmited == true) {
      return false;
    }

    formSubmited = true;

    $('#installModal .modal-body').html('<p><i class="fa fa-spinner fa-spin"></i> Testing database connection..</p>');

    $('#installModal').modal({
      keyboard: false,
      show: true
    });

    var dbParams = {
      server: $('#dbServer').val(),
      username: $('#username').val(),
      password: $('#password').val(),
      name: $('#dbName').val(),
      prefix: $('#dbTablePrefix').val()
    };

    var dbCheckUrl = 'rpc.php?action=dbCheck';

    if (dbParams.name == dbNameToCreate) {
      dbCheckUrl = dbCheckUrl + '&createDb=true';
    }

    $.post(dbCheckUrl, dbParams, function (response) {
      if (('status' in response) && ('message' in response)) {
        if ((response.status == '1') && (response.message == 'success')) {
          if (doImport === true) {
            $('#installModal .modal-body').html('<p><i class="fa fa-spinner fa-spin"></i> The database structure is now being imported. Please be patient during this procedure.</p>');

            $.post('rpc.php?action=dbImport', dbParams, function (response2) {
              if (('status' in response2) && ('message' in response2)) {
                if ((response2.status == '1') && (response2.message == 'success')) {
                  $('#installModal .modal-body').html('<div class="alert alert-success"><i class="fa fa-thumbs-up"></i> Database imported successfully. Proceeding to next step..</div>');

                  formSuccess = true;

                  setTimeout(function() {
                    $('#installForm').submit();
                  }, 2000);
                } else {
                  $('#installModal').modal('hide');

                  $('#mBox').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle text-danger"></i> There was a problem importing the database. The following error had occured:<br><br><strong>%s</strong><br><br>Please verify the connection parameters and try again.</div>'.replace('%s', response2.message));

                  formSubmited = false;
                }
              } else {
                $('#installModal').modal('hide');

                $('#mBox').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle text-danger"></i> There was a problem importing the database. Please verify the connection parameters and try again.</div>');

                formSubmited = false;
              }
            }, 'json').fail(function() {
              $('#installModal').modal('hide');

              $('#mBox').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle text-danger"></i> There was a problem importing the database. Please verify the connection parameters and try again.</div>');

              formSubmited = false;
            });
          } else {
            $('#installModal .modal-body').html('<div class="alert alert-success"><i class="fa fa-thumbs-up"></i> Database connection made successfully. Proceeding to next step..</div>');

            formSuccess = true;

            setTimeout(function() {
              $('#installForm').submit();
            }, 2000);
          }
        } else {
          $('#installModal').modal('hide');

          if ((response.status == '1049') && (dbParams.name != dbNameToCreate)) {
            dbNameToCreate = dbParams.name;

            var result_error = 'The database name of \'' + dbParams.name.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;') + '\' does not exist. If you submit the form again with the same database name, an attempt will be made to create it.';

            $('#mBox').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle text-danger"></i> <strong>%s</strong></div>'.replace('%s', result_error));
          } else {
            $('#mBox').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle text-danger"></i> There was a problem connecting to the database server. The following error had occured:<br><br><strong>%s</strong><br><br>Please verify the connection parameters and try again.</div>'.replace('%s', response.message));
          }

          formSubmited = false;
        }
      } else {
        $('#installModal').modal('hide');

        $('#mBox').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle text-danger"></i> There was a problem connecting to the database server. Please verify the connection parameters and try again.</div>');

        formSubmited = false;
      }
    }, 'json').fail(function() {
      $('#installModal').modal('hide');

      $('#mBox').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle text-danger"></i> There was a problem connecting to the database server. Please verify the connection parameters and try again.</div>');

      formSubmited = false;
    });
  }

  // disable ENTER and force click on continue buttons
  $('#installForm').on('keyup keypress', function(e) {
    var keyCode = e.keyCode || e.which;

    if (keyCode === 13) {
      e.preventDefault();

      return false;
    }
  });

  $('#installForm').submit(function(e) {
    if (formSuccess == false) {
      e.preventDefault();

      prepareDB();
    } else {
      if (doImport !== true) {
        $('#installForm').append('<input type="hidden" name="DB_SKIP_IMPORT" value="true">');
      }
    }
  });

  $('#buttonDoImport').on('click', function(e) {
    doImport = true;
  });

  $('#buttonSkipImport').on('click', function(e) {
    doImport = false;
  });
});
</script>
