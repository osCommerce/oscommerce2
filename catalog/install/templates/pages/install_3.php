<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  $dir_fs_document_root = $_POST['DIR_FS_DOCUMENT_ROOT'];
  if ((substr($dir_fs_document_root, -1) != '\\') && (substr($dir_fs_document_root, -1) != '/')) {
    if (strrpos($dir_fs_document_root, '\\') !== false) {
      $dir_fs_document_root .= '\\';
    } else {
      $dir_fs_document_root .= '/';
    }
  }
?>

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
          <li class="text-muted">Database Server</li>
          <li class="text-muted">Web Server</li>
          <li class="text-success"><strong>Online Store Settings</strong></li>
          <li class="text-muted">Finished!</li>
        </ol>
      </div>
    </div>
    <div class="progress">
      <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 75%">75%</div>
    </div>
  </div>
</div>

<div class="clearfix"></div>

<div class="row">
  <div class="col-xs-12 col-sm-push-3 col-sm-9">

    <div class="page-header">
      <p class="inputRequirement pull-right text-right"><span class="glyphicon glyphicon-asterisk inputRequirement"></span> Required information</p>
      <h2>Online Store Settings</h2>
    </div>

    <form name="install" id="installForm" action="install.php?step=4" method="post" class="form-horizontal" role="form">

      <div class="form-group has-feedback">
        <label for="storeName" class="control-label col-xs-3">Store Name</label>
        <div class="col-xs-9">
          <?php echo osc_draw_input_field('CFG_STORE_NAME', NULL, 'required aria-required="true" id="storeName" placeholder="Your Store Name"'); ?>
          <span class="glyphicon glyphicon-asterisk form-control-feedback inputRequirement"></span>
          <span class="help-block">The name of the online store that is presented to the public.</span>
        </div>
      </div>
      

      <div class="form-group has-feedback">
        <label for="ownerName" class="control-label col-xs-3">Store Owner Name</label>
        <div class="col-xs-9">
          <?php echo osc_draw_input_field('CFG_STORE_OWNER_NAME', NULL, 'required aria-required="true" id="ownerName" placeholder="Your Name"'); ?>
          <span class="glyphicon glyphicon-asterisk form-control-feedback inputRequirement"></span>
          <span class="help-block">The name of the store owner that is presented to the public.</span>
        </div>
      </div>
      
      <div class="form-group has-feedback">
        <label for="ownerEmail" class="control-label col-xs-3">Store Owner E-Mail Address</label>
        <div class="col-xs-9">
          <?php echo osc_draw_input_field('CFG_STORE_OWNER_EMAIL_ADDRESS', NULL, 'required aria-required="true" id="ownerEmail" placeholder="you@yours.com"'); ?>
          <span class="glyphicon glyphicon-asterisk form-control-feedback inputRequirement"></span>
          <span class="help-block">The e-mail address of the store owner that is presented to the public.</span>
        </div>
      </div>
      
      <div class="form-group has-feedback">
        <label for="adminUsername" class="control-label col-xs-3">Administrator Username</label>
        <div class="col-xs-9">
          <?php echo osc_draw_input_field('CFG_ADMINISTRATOR_USERNAME', NULL, 'required aria-required="true" id="adminUsername" placeholder="Username"'); ?>
          <span class="glyphicon glyphicon-asterisk form-control-feedback inputRequirement"></span>
          <span class="help-block">The administrator username to use for the administration tool.</span>
        </div>
      </div>
      
      <div class="form-group has-feedback">
        <label for="adminPassword" class="control-label col-xs-3">Administrator Password</label>
        <div class="col-xs-9">
          <?php echo osc_draw_input_field('CFG_ADMINISTRATOR_PASSWORD', NULL, 'required aria-required="true" id="adminPassword"'); ?>
          <span class="glyphicon glyphicon-asterisk form-control-feedback inputRequirement"></span>
          <span class="help-block">The password to use for the administrator account.</span>
        </div>
      </div>

<?php
  if (osc_is_writable($dir_fs_document_root) && osc_is_writable($dir_fs_document_root . 'admin')) {
?>
      <div class="form-group has-feedback">
        <label for="adminDir" class="control-label col-xs-3">Administration Directory Name</label>
        <div class="col-xs-9">
          <?php echo osc_draw_input_field('CFG_ADMIN_DIRECTORY', 'admin', 'required aria-required="true" id="adminDir"'); ?>
          <span class="glyphicon glyphicon-asterisk form-control-feedback inputRequirement"></span>
          <span class="help-block">This is the directory where the administration section will be installed. You should change this for security reasons.</span>
        </div>
      </div>
<?php
  }

?>
      <div class="form-group has-feedback">
        <label for="Zulu" class="control-label col-xs-3">Time Zone</label>
        <div class="col-xs-9">
          <?php echo osc_draw_time_zone_select_menu('CFG_TIME_ZONE'); ?>
          <span class="glyphicon glyphicon-asterisk form-control-feedback inputRequirement"></span>
          <span class="help-block">The time zone to base the date and time on.</span>
        </div>
      </div>

      <p><?php echo osc_draw_button('Continue To Step 4', 'triangle-1-e', null, 'primary', null, 'btn-success btn-block'); ?></p>

      <?php
      foreach ( $_POST as $key => $value ) {
        if (($key != 'x') && ($key != 'y')) {
          echo osc_draw_hidden_field($key, $value);
        }
      }
      ?>

    </form>

  </div>
  <div class="col-xs-12 col-sm-pull-9 col-sm-3">
    <div class="panel panel-success">
      <div class="panel-heading">
        Step 3: Online Store Settings
      </div>
      <div class="panel-body">
        <p>Here you can define the name of your online store and the contact information for the store owner.</p>
        <p>The administrator username and password are used to log into the protected administration tool section.</p>
      </div>
    </div>
  </div>
  
</div>
