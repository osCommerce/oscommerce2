<?php
use OSC\OM\FileSystem;
use OSC\OM\HTML;
use OSC\OM\OSCOM;

if ((isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on')) || (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == 443))) {
    $conn = 'https';
} else {
    $conn = 'http';
}

$www_location = $conn . '://' . $_SERVER['HTTP_HOST'];

if (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI'])) {
    $www_location .= $_SERVER['REQUEST_URI'];
} else {
    $www_location .= $_SERVER['SCRIPT_FILENAME'];
}

$www_location = substr($www_location, 0, strpos($www_location, 'install'));

$dir_fs_www_root = dirname(dirname(OSCOM::BASE_DIR)) . '/';
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
        <p>Step 2/4</p>

        <ol>
          <li>Database Server</li>
          <li><strong>&gt; Web Server</strong></li>
          <li>Online Store Settings</li>
          <li>Finished!</li>
        </ol>
      </div>
    </div>

    <div class="progress">
      <div class="progress-bar progress-bar-primary progress-bar-striped" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100" style="width: 50%">50%</div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-xs-12 col-sm-push-3 col-sm-9">
    <h1>Web Server</h1>

    <form name="install" id="installForm" action="install.php?step=3" method="post">
      <div class="form-group has-feedback">
        <label for="wwwAddress">WWW Address</label>
        <?php echo HTML::inputField('HTTP_WWW_ADDRESS', $www_location, 'required aria-required="true" id="wwwAddress" placeholder="http://"'); ?>
        <span class="help-block">The web address to the online store.</span>
      </div>

      <div class="form-group has-feedback">
        <label for="webRoot">Webserver Root Directory</label>
        <?php echo HTML::inputField('DIR_FS_DOCUMENT_ROOT', str_replace('\\', '/', FileSystem::displayPath($dir_fs_www_root)), 'required aria-required="true" id="webRoot"'); ?>
        <span class="help-block">The directory where the online store is installed on the server.</span>
      </div>

      <p><?php echo HTML::button('Continue to Step 3', 'triangle-1-e', null, null, 'btn-success'); ?></p>

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
          Step 2: Web Server
        </div>
      </div>

      <div class="panel-body">
        <p>The web server takes care of serving the pages of your online store to your guests and customers. The web server parameters make sure the links to the pages point to the correct location.</p>
      </div>
    </div>
  </div>
</div>
