<?php
use OSC\OM\Db;
use OSC\OM\FileSystem;
use OSC\OM\Hash;
use OSC\OM\HTML;
use OSC\OM\OSCOM;

$OSCOM_Db = Db::initialize($_POST['DB_SERVER'], $_POST['DB_SERVER_USERNAME'], $_POST['DB_SERVER_PASSWORD'], $_POST['DB_DATABASE']);
$OSCOM_Db->setTablePrefix($_POST['DB_TABLE_PREFIX']);

$OSCOM_Db->save('configuration', ['configuration_value' => $_POST['CFG_STORE_NAME']], ['configuration_key' => 'STORE_NAME']);
$OSCOM_Db->save('configuration', ['configuration_value' => $_POST['CFG_STORE_OWNER_NAME']], ['configuration_key' => 'STORE_OWNER']);
$OSCOM_Db->save('configuration', ['configuration_value' => $_POST['CFG_STORE_OWNER_EMAIL_ADDRESS']], ['configuration_key' => 'STORE_OWNER_EMAIL_ADDRESS']);

if (!empty($_POST['CFG_STORE_OWNER_NAME']) && !empty($_POST['CFG_STORE_OWNER_EMAIL_ADDRESS'])) {
    $OSCOM_Db->save('configuration', ['configuration_value' => '"' . trim($_POST['CFG_STORE_OWNER_NAME']) . '" <' . trim($_POST['CFG_STORE_OWNER_EMAIL_ADDRESS']) . '>'], ['configuration_key' => 'EMAIL_FROM']);
} else {
    $OSCOM_Db->save('configuration', ['configuration_value' => $_POST['CFG_STORE_OWNER_EMAIL_ADDRESS']], ['configuration_key' => 'EMAIL_FROM']);
}

if (!empty($_POST['CFG_ADMINISTRATOR_USERNAME'])) {
    $Qcheck = $OSCOM_Db->prepare('select user_name from :table_administrators where user_name = :user_name');
    $Qcheck->bindValue(':user_name', $_POST['CFG_ADMINISTRATOR_USERNAME']);
    $Qcheck->execute();

    if ($Qcheck->fetch() !== false) {
        $OSCOM_Db->save('administrators', ['user_password' => Hash::encrypt(trim($_POST['CFG_ADMINISTRATOR_PASSWORD']))], ['user_name' => $_POST['CFG_ADMINISTRATOR_USERNAME']]);
    } else {
        $OSCOM_Db->save('administrators', ['user_name' => $_POST['CFG_ADMINISTRATOR_USERNAME'], 'user_password' => Hash::encrypt(trim($_POST['CFG_ADMINISTRATOR_PASSWORD']))]);
    }
}

if (FileSystem::isWritable(OSCOM::BASE_DIR . 'Work')) {
    if (!is_dir(OSCOM::BASE_DIR . 'Work/Cache')) {
        mkdir(OSCOM::BASE_DIR . 'Work/Cache', 0777);
    }

    if (!is_dir(OSCOM::BASE_DIR . 'Work/Session')) {
        mkdir(OSCOM::BASE_DIR . 'Work/Session', 0777);
    }
}

foreach (glob(OSCOM::BASE_DIR . 'Work/Cache/*.cache') as $c) {
    unlink($c);
}

$dir_fs_document_root = $_POST['DIR_FS_DOCUMENT_ROOT'];

if ((substr($dir_fs_document_root, -1) != '\\') && (substr($dir_fs_document_root, -1) != '/')) {
    if (strrpos($dir_fs_document_root, '\\') !== false) {
        $dir_fs_document_root .= '\\';
    } else {
        $dir_fs_document_root .= '/';
    }
}

$http_url = parse_url($_POST['HTTP_WWW_ADDRESS']);
$http_server = $http_url['scheme'] . '://' . $http_url['host'];
$http_catalog = $http_url['path'];

if (isset($http_url['port']) && !empty($http_url['port'])) {
    $http_server .= ':' . $http_url['port'];
}

if (substr($http_catalog, -1) != '/') {
    $http_catalog .= '/';
}

$admin_folder = 'admin';

if (isset($_POST['CFG_ADMIN_DIRECTORY']) && !empty($_POST['CFG_ADMIN_DIRECTORY']) && FileSystem::isWritable($dir_fs_document_root) && FileSystem::isWritable($dir_fs_document_root . 'admin')) {
    $admin_folder = preg_replace('/[^a-zA-Z0-9]/', '', trim($_POST['CFG_ADMIN_DIRECTORY']));

    if (empty($admin_folder)) {
        $admin_folder = 'admin';
    }
}

if ($admin_folder != 'admin') {
    @rename($dir_fs_document_root . 'admin', $dir_fs_document_root . $admin_folder);
}

$dbServer = trim($_POST['DB_SERVER']);
$dbUsername = trim($_POST['DB_SERVER_USERNAME']);
$dbPassword = trim($_POST['DB_SERVER_PASSWORD']);
$dbDatabase = trim($_POST['DB_DATABASE']);
$dbTablePrefix = trim($_POST['DB_TABLE_PREFIX']);
$timezone = trim($_POST['TIME_ZONE']);

$file_contents = <<<ENDCFG
<?php
\$ini = <<<EOD
db_server = "{$dbServer}"
db_server_username = "{$dbUsername}"
db_server_password = "{$dbPassword}"
db_database = "{$dbDatabase}"
db_table_prefix = "{$dbTablePrefix}"
store_sessions = "MySQL"
time_zone = "{$timezone}"
EOD;

ENDCFG;
// last empty line needed

file_put_contents(OSCOM::BASE_DIR . 'Conf/global.php', $file_contents);

@chmod(OSCOM::BASE_DIR . 'Conf/global.php', 0644);

$file_contents = <<<ENDCFG
<?php
\$ini = <<<EOD
dir_root = "{$dir_fs_document_root}"
http_server = "{$http_server}"
http_path = "{$http_catalog}"
http_images_path = "images/"
http_cookie_domain = ""
http_cookie_path = "{$http_catalog}"
ssl = "false"
https_server = "{$http_server}"
https_path = "{$http_catalog}"
https_images_path = "images/"
https_cookie_domain = ""
https_cookie_path = "{$http_catalog}"
EOD;

ENDCFG;
// last empty line needed

file_put_contents(OSCOM::BASE_DIR . 'Sites/Shop/site_conf.php', $file_contents);

@chmod(OSCOM::BASE_DIR . 'Sites/Shop/site_conf.php', 0644);

$admin_dir_fs_document_root = $dir_fs_document_root . $admin_folder . '/';
$admin_http_path = $http_catalog . $admin_folder . '/';

$file_contents = <<<ENDCFG
<?php
\$ini = <<<EOD
dir_root = "{$admin_dir_fs_document_root}"
http_server = "{$http_server}"
http_path = "{$admin_http_path}"
http_images_path = "images/"
http_cookie_domain = ""
http_cookie_path = "{$admin_http_path}"
ssl = "false"
https_server = "{$http_server}"
https_path = "{$admin_http_path}"
https_images_path = "images/"
https_cookie_domain = ""
https_cookie_path = "{$admin_http_path}"
EOD;

ENDCFG;
// last empty line needed

file_put_contents(OSCOM::BASE_DIR . 'Sites/Admin/site_conf.php', $file_contents);

@chmod(OSCOM::BASE_DIR . 'Sites/Admin/site_conf.php', 0644);
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
        <p>Step 4/4</p>

        <ol>
          <li>Database Server</li>
          <li>Web Server</li>
          <li>Online Store Settings</li>
          <li><strong>&gt; Finished!</strong></li>
        </ol>
      </div>
    </div>

    <div class="progress">
      <div class="progress-bar progress-bar-primary progress-bar-striped" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">100%</div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-xs-12 col-sm-push-3 col-sm-9">
    <h1>Finished!</h1>

    <div class="alert alert-success">The installation of your online store was successful! Click on either button to start your online selling experience:</div>

    <br />

    <div class="row">
      <div class="col-sm-6"><?php echo HTML::button('Online Store (Frontend)', 'fa fa-shopping-cart', $http_server . $http_catalog . 'index.php', 'primary', array('newwindow' => 1), 'btn-success btn-block'); ?></div>
      <div class="col-sm-6"><?php echo HTML::button('Administration Dashboard (Backend)', 'fa fa-lock', $http_server . $http_catalog . $admin_folder . '/index.php', 'primary', array('newwindow' => 1), 'btn-info btn-block'); ?></div>
    </div>
  </div>

  <div class="col-xs-12 col-sm-pull-9 col-sm-3">
    <div class="panel panel-success">
      <div class="panel-heading">
        <div class="panel-title">
          Step 4: Finished!
        </div>
      </div>

      <div class="panel-body">
        <p>Congratulations on installing and configuring osCommerce Online Merchant as your online store solution!</p>
        <p>We wish you all the best with the success of your online store and welcome you to join and participate in our community.</p>
        <p>- <a href="https://www.oscommerce.com/Us&Team" target="_blank">The osCommerce Team</a></p>
      </div>
    </div>
  </div>
</div>
