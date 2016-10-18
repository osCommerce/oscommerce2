<?php
use OSC\OM\DateTime;
use OSC\OM\FileSystem;
use OSC\OM\HTML;

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
      <h2>New Installation</h2>

      <p>This web-based installation routine will correctly setup and configure osCommerce Online Merchant to run on this server.</p>
      <p>Please follow the on-screen instructions that will take you through the database server, web server, and store configuration options. If help is needed at any stage, please consult the <a href="https://library.oscommerce.com" target="_blank" class="alert-link">osCommerce documentation</a>, seek help at the <a href="http://forums.oscommerce.com" target="_blank" class="alert-link">osCommerce community forums</a>, visit the <a href="https://www.oscommerce.com/Support" target="_blank" class="alert-link">osCommerce support page</a>, or send an enquiry to your server administrator or hosting server provider.</p>
    </div>
  </div>

  <div class="col-sm-3">
    <div class="panel panel-primary">
      <div class="panel-heading">
        <p>Step 3/4</p>

        <ol>
          <li>Database Server</li>
          <li>Web Server</li>
          <li><strong>&gt; Online Store Settings</strong></li>
          <li>Finished!</li>
        </ol>
      </div>
    </div>

    <div class="progress">
      <div class="progress-bar progress-bar-primary progress-bar-striped" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 75%">75%</div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-xs-12 col-sm-push-3 col-sm-9">
    <h1>Online Store Settings</h1>

    <form name="install" id="installForm" action="install.php?step=4" method="post">
      <div class="form-group has-feedback">
        <label for="storeName">Store Name</label>
        <?php echo HTML::inputField('CFG_STORE_NAME', null, 'required aria-required="true" id="storeName"'); ?>
        <span class="help-block">The name of the online store that is presented to the public.</span>
      </div>

      <div class="form-group has-feedback">
        <label for="ownerName">Store Owner Name</label>
        <?php echo HTML::inputField('CFG_STORE_OWNER_NAME', null, 'required aria-required="true" id="ownerName"'); ?>
        <span class="help-block">The name of the store owner that is presented to the public.</span>
      </div>

      <div class="form-group has-feedback">
        <label for="ownerEmail">Store Owner E-Mail Address</label>
        <?php echo HTML::inputField('CFG_STORE_OWNER_EMAIL_ADDRESS', null, 'required aria-required="true" id="ownerEmail"'); ?>
        <span class="help-block">The e-mail address of the store owner that is presented to the public.</span>
      </div>

      <div class="form-group has-feedback">
        <label for="adminUsername">Administrator Username</label>
        <?php echo HTML::inputField('CFG_ADMINISTRATOR_USERNAME', null, 'required aria-required="true" id="adminUsername"'); ?>
        <span class="help-block">The administrator username to use for the administration tool.</span>
      </div>

      <div class="form-group has-feedback">
        <label for="adminPassword">Administrator Password</label>
        <?php echo HTML::inputField('CFG_ADMINISTRATOR_PASSWORD', null, 'required aria-required="true" id="adminPassword"'); ?>
        <span class="help-block">The password to use for the administrator account.</span>
      </div>

<?php
if (FileSystem::isWritable($dir_fs_document_root) && FileSystem::isWritable($dir_fs_document_root . 'admin')) {
?>

      <div class="form-group has-feedback">
        <label for="adminDir">Administration Directory Name</label>
        <?php echo HTML::inputField('CFG_ADMIN_DIRECTORY', 'admin', 'required aria-required="true" id="adminDir"'); ?>
        <span class="help-block">This is the directory where the administration section will be installed. You should change this for security reasons.</span>
      </div>

<?php
}
?>

      <div class="form-group has-feedback">
        <label for="Zulu">Time Zone</label>
        <?php echo HTML::selectField('TIME_ZONE', DateTime::getTimeZones(), date_default_timezone_get(), 'id="Zulu"'); ?>
        <span class="help-block">The time zone to base the date and time on.</span>
      </div>

      <p><?php echo HTML::button('Continue to Step 4', 'triangle-1-e', null, null, 'btn-success'); ?></p>

<?php
foreach ($_POST as $key => $value) {
    if (($key != 'x') && ($key != 'y')) {
        echo HTML::hiddenField($key, $value);
    }
}
?>

    </form>
  </div>

  <div class="col-xs-12 col-sm-pull-9 col-sm-3">
    <div class="panel panel-success">
      <div class="panel-heading">
        <div class="panel-title">
          Step 3: Online Store Settings
        </div>
      </div>

      <div class="panel-body">
        <p>Here you can define the name of your online store and the contact information for the store owner.</p>
        <p>The administrator username and password are used to log into the protected administration tool section.</p>
      </div>
    </div>
  </div>
</div>
