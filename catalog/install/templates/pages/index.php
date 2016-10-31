<?php
use OSC\OM\FileSystem;
use OSC\OM\OSCOM;

$configfile_array = [
    OSCOM::BASE_DIR . 'Conf/global.php',
    OSCOM::BASE_DIR . 'Sites/Shop/site_conf.php',
    OSCOM::BASE_DIR . 'Sites/Admin/site_conf.php'
];

foreach ($configfile_array as $key => $f) {
    if (!is_file($f)) {
        continue;
    } elseif (!FileSystem::isWritable($f)) {
// try to chmod and try again
        @chmod($f, 0777);

        if (!FileSystem::isWritable($f)) {
            continue;
        }
    }

// file exists and is writable
    unset($configfile_array[$key]);
}

$warning_array = [];

if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')) {
    $warning_array[] = 'The PDO MySQL driver extension is not installed. Please enable it in the PHP configuration to continue installation.';
}

if (PHP_VERSION < 5.5) {
    $warning_array[] = 'The minimum required PHP version is v5.5 - please ask your host or server administrator to upgrade the PHP version to continue installation.';
}

$https_url = 'https://' . $_SERVER['HTTP_HOST'];

if (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI'])) {
    $https_url .= $_SERVER['REQUEST_URI'];
} else {
    $https_url .= $_SERVER['SCRIPT_FILENAME'];
}
?>

<div class="alert alert-info">
  <h2>Welcome to osCommerce Online Merchant <small>v<?php echo OSCOM::getVersion(); ?></small></h2>

  <p>osCommerce Online Merchant helps you sell products worldwide with your own online store. Its Administration Dashboard manages products, customers, orders, newsletters, specials, and more to successfully build the success of your online business.</p>
  <p>osCommerce has attracted a large community of store owners and developers who support each other and have provided over 7,000 free add-ons that can extend the features and potential of your online store.</p>
</div>

<div class="alert alert-warning">
  <h2>Beta Release</h2>

  <p>This release is currently a beta release recommended only for development and testing purposes. Please visit the <a href="https://www.oscommerce.com" target="_blank" class="alert-link">osCommerce</a> website to stay up to date on production-ready releases.</p>
</div>

<div class="row">
  <div class="col-xs-12 col-sm-push-3 col-sm-9">
    <h1>New Installation</h1>

<?php
if (!empty($warning_array)) {
?>

    <div class="alert alert-danger">
      <p>Please correct the following errors and try the installation procedure again with the changes in place.</p>

      <ul style="margin-top: 20px; margin-bottom: 20px;">

<?php
    foreach ($warning_array as $key => $value) {
        echo '<li>' . $value . '</li>';
    }
?>

      </ul>

      <p><i>Changing webserver configuration parameters may require the webserver service to be restarted before the changes take affect.</i></p>
    </div>

<?php
}

if (!empty($configfile_array)) {
?>

    <div class="alert alert-danger">
      <p>The webserver is not able to save to the following installation configuration files. Please update the file permissions of the following files to world-writable (chmod 777) and try the installation procedure again:</p>

      <ul style="margin-top: 20px;">

<?php
    foreach ($configfile_array as $file) {
        echo '<li>' . FileSystem::displayPath($file) . '</li>';
    }
?>

      </ul>
    </div>

<?php
}

if (!empty($configfile_array) || !empty($warning_array)) {
?>

    <p><a href="index.php" class="btn btn-danger" role="button">Retry Installation</a></p>

<?php
} else {
?>

    <div id="detectHttps" class="alert alert-info">
      <p><i class="fa fa-spinner fa-spin fa-fw"></i> Please wait, detecting web server environment..</p>
    </div>

    <div id="jsOn" style="display: none;">
      <p>The web server environment has been verified to proceed with a successful installation and configuration of your online store.</p>

      <div id="httpsNotice" style="display: none;">
        <div class="alert alert-warning">
          <p><strong>HTTPS Server Detected</strong></p>

          <p>A HTTPS configured web server has been detected. It is recommended to install your online store in a secure environment. Please click the following <span class="label label-warning">Reload in HTTPS</span> button to reload this installation procedure in HTTPS. If you receive an error, please use your browsers back button to return to this page and continue the installation using the <span class="label label-success">Start the Installation Procedure</span> button below.</p>

          <p><a href="<?= $https_url; ?>" class="btn btn-warning btn-sm" role="button">Reload in HTTPS</a></p>
        </div>
      </div>

      <p><a href="install.php" class="btn btn-success" role="button">Start the Installation Procedure</a></p>
    </div>

    <div id="jsOff">
      <p class="text-danger">Please enable Javascript in your browser to be able to start the installation procedure.</p>
      <p><a href="index.php" class="btn btn-danger" role="button">Retry Installation</a></p>
    </div>

<script>
$(function() {
  $('#jsOff').hide();

  if (document.location.protocol == 'https:') {
    $('#detectHttps').hide();
    $('#jsOn').show();
  } else {
    var httpsCheckUrl = 'rpc.php?action=httpsCheck';

    $.post(httpsCheckUrl, null, function (response) {
      if (('status' in response) && ('message' in response)) {
        if ((response.status == '1') && (response.message == 'success')) {
          $('#detectHttps').hide();
          $('#httpsNotice').show();
          $('#jsOn').show();
        } else {
          $('#detectHttps').hide();
          $('#jsOn').show();
        }
      } else {
        $('#detectHttps').hide();
        $('#jsOn').show();
      }
    }, 'json').fail(function() {
      $('#detectHttps').hide();
      $('#jsOn').show();
    });
  }
});
</script>

<?php
}
?>

  </div>

  <div class="col-xs-12 col-sm-pull-9 col-sm-3">
    <div class="panel panel-success">
      <div class="panel-heading">
        Server Capabilities
      </div>

      <p style="margin: 5px;"><strong>PHP Version</strong></p>

      <table class="table">
        <tbody>
          <tr>
            <td><?php echo PHP_VERSION; ?></td>
            <td class="text-right" width="25"><?php echo ((PHP_VERSION >= 5.5) ? '<i class="fa fa-thumbs-up text-success"></i>' : '<i class="fa fa-exclamation-circle text-danger"></i>'); ?></td>
          </tr>
        </tbody>
      </table>

<?php
if (function_exists('ini_get')) {
?>

      <p style="margin: 5px;"><strong>PHP Settings</strong></p>

      <table class="table">
        <tbody>
          <tr>
            <td>file_uploads</td>
            <td class="text-right"><?php echo (((int)ini_get('file_uploads') === 0) ? 'Off' : 'On'); ?></td>
            <td class="text-right"><?php echo (((int)ini_get('file_uploads') === 1) ? '<i class="fa fa-thumbs-up text-success"></i>' : '<i class="fa fa-exclamation-circle text-danger"></i>'); ?></td>
          </tr>
        </tbody>
      </table>

      <p style="margin: 5px;"><strong>PHP Extensions</strong></p>

      <table class="table">
        <tbody>
          <tr>
            <td>PDO MySQL</td>
            <td class="text-right"><?php echo extension_loaded('pdo') && extension_loaded('pdo_mysql') ? '<i class="fa fa-thumbs-up text-success"></i>' : '<i class="fa fa-exclamation-circle text-danger"></i>'; ?></td>
          </tr>
          <tr>
            <td>GD</td>
            <td class="text-right"><?php echo extension_loaded('gd') ? '<i class="fa fa-thumbs-up text-success"></i>' : '<i class="fa fa-exclamation-circle text-warning"></i>'; ?></td>
          </tr>
          <tr>
            <td>cURL</td>
            <td class="text-right"><?php echo extension_loaded('curl') ? '<i class="fa fa-thumbs-up text-success"></i>' : '<i class="fa fa-exclamation-circle text-warning"></i>'; ?></td>
          </tr>
          <tr>
            <td>OpenSSL</td>
            <td class="text-right"><?php echo extension_loaded('openssl') ? '<i class="fa fa-thumbs-up text-success"></i>' : '<i class="fa fa-exclamation-circle text-warning"></i>'; ?></td>
          </tr>
        </tbody>
      </table>

<?php
}
?>

    </div>
  </div>
</div>
