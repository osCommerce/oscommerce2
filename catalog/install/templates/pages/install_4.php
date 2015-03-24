<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  require('../includes/database_tables.php');

  osc_db_connect(trim($_POST['DB_SERVER']), trim($_POST['DB_SERVER_USERNAME']), trim($_POST['DB_SERVER_PASSWORD']));
  osc_db_select_db(trim($_POST['DB_DATABASE']));

  osc_db_query('update ' . TABLE_CONFIGURATION . ' set configuration_value = "' . trim($_POST['CFG_STORE_NAME']) . '" where configuration_key = "STORE_NAME"');
  osc_db_query('update ' . TABLE_CONFIGURATION . ' set configuration_value = "' . trim($_POST['CFG_STORE_OWNER_NAME']) . '" where configuration_key = "STORE_OWNER"');
  osc_db_query('update ' . TABLE_CONFIGURATION . ' set configuration_value = "' . trim($_POST['CFG_STORE_OWNER_EMAIL_ADDRESS']) . '" where configuration_key = "STORE_OWNER_EMAIL_ADDRESS"');

  if (!empty($_POST['CFG_STORE_OWNER_NAME']) && !empty($_POST['CFG_STORE_OWNER_EMAIL_ADDRESS'])) {
    osc_db_query('update ' . TABLE_CONFIGURATION . ' set configuration_value = "\"' . trim($_POST['CFG_STORE_OWNER_NAME']) . '\" <' . trim($_POST['CFG_STORE_OWNER_EMAIL_ADDRESS']) . '>" where configuration_key = "EMAIL_FROM"');
  } else {
    osc_db_query('update ' . TABLE_CONFIGURATION . ' set configuration_value = "' . trim($_POST['CFG_STORE_OWNER_EMAIL_ADDRESS']) . '" where configuration_key = "EMAIL_FROM"');
  }

  if ( !empty($_POST['CFG_ADMINISTRATOR_USERNAME']) ) {
    $check_query = osc_db_query('select user_name from ' . TABLE_ADMINISTRATORS . ' where user_name = "' . trim($_POST['CFG_ADMINISTRATOR_USERNAME']) . '"');

    if (osc_db_num_rows($check_query)) {
      osc_db_query('update ' . TABLE_ADMINISTRATORS . ' set user_password = "' . osc_encrypt_password(trim($_POST['CFG_ADMINISTRATOR_PASSWORD'])) . '" where user_name = "' . trim($_POST['CFG_ADMINISTRATOR_USERNAME']) . '"');
    } else {
      osc_db_query('insert into ' . TABLE_ADMINISTRATORS . ' (user_name, user_password) values ("' . trim($_POST['CFG_ADMINISTRATOR_USERNAME']) . '", "' . osc_encrypt_password(trim($_POST['CFG_ADMINISTRATOR_PASSWORD'])) . '")');
    }
  }

  osc_db_query('update ' . TABLE_CONFIGURATION . ' set configuration_value = "' . trim($_POST['CFG_STORE_OWNER_EMAIL_ADDRESS']) . '" where configuration_key = "MODULE_PAYMENT_PAYPAL_EXPRESS_SELLER_ACCOUNT"');
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
          <li class="text-muted">Online Store Settings</li>
          <li class="text-success"><strong>Finished!</strong></li>
        </ol>
      </div>
    </div>
    <div class="progress">
      <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">100%</div>
    </div>
  </div>
</div>

<div class="clearfix"></div>

<div class="row">
  <div class="col-xs-12 col-sm-push-3 col-sm-9">

    <div class="page-header">
      <h2>Finished!</h2>
    </div>
    
    <?php
    $dir_fs_document_root = $_POST['DIR_FS_DOCUMENT_ROOT'];
    if ((substr($dir_fs_document_root, -1) != '\\') && (substr($dir_fs_document_root, -1) != '/')) {
      if (strrpos($dir_fs_document_root, '\\') !== false) {
        $dir_fs_document_root .= '\\';
      } else {
        $dir_fs_document_root .= '/';
      }
    }

    osc_db_query('update ' . TABLE_CONFIGURATION . ' set configuration_value = "' . $dir_fs_document_root . 'includes/work/" where configuration_key = "DIR_FS_CACHE"');
    osc_db_query('update ' . TABLE_CONFIGURATION . ' set configuration_value = "' . $dir_fs_document_root . 'includes/work/" where configuration_key = "SESSION_WRITE_DIRECTORY"');

    if ($handle = opendir($dir_fs_document_root . 'includes/work/')) {
      while (false !== ($filename = readdir($handle))) {
        if (substr($filename, strrpos($filename, '.')) == '.cache') {
          @unlink($dir_fs_document_root . 'includes/work/' . $filename);
        }
      }

      closedir($handle);
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
    if (isset($_POST['CFG_ADMIN_DIRECTORY']) && !empty($_POST['CFG_ADMIN_DIRECTORY']) && osc_is_writable($dir_fs_document_root) && osc_is_writable($dir_fs_document_root . 'admin')) {
      $admin_folder = preg_replace('/[^a-zA-Z0-9]/', '', trim($_POST['CFG_ADMIN_DIRECTORY']));

      if (empty($admin_folder)) {
        $admin_folder = 'admin';
      }
    }

    $file_contents = '<?php' . "\n" .
                     '  define(\'HTTP_SERVER\', \'' . $http_server . '\');' . "\n" .
                     '  define(\'HTTPS_SERVER\', \'' . $http_server . '\');' . "\n" .
                     '  define(\'ENABLE_SSL\', false);' . "\n" .
                     '  define(\'HTTP_COOKIE_DOMAIN\', \'\');' . "\n" .
                     '  define(\'HTTPS_COOKIE_DOMAIN\', \'\');' . "\n" .
                     '  define(\'HTTP_COOKIE_PATH\', \'' . $http_catalog . '\');' . "\n" .
                     '  define(\'HTTPS_COOKIE_PATH\', \'' . $http_catalog . '\');' . "\n" .
                     '  define(\'DIR_WS_HTTP_CATALOG\', \'' . $http_catalog . '\');' . "\n" .
                     '  define(\'DIR_WS_HTTPS_CATALOG\', \'' . $http_catalog . '\');' . "\n" .
                     '  define(\'DIR_WS_IMAGES\', \'images/\');' . "\n" .
                     '  define(\'DIR_WS_ICONS\', DIR_WS_IMAGES . \'icons/\');' . "\n" .
                     '  define(\'DIR_WS_INCLUDES\', \'includes/\');' . "\n" .
                     '  define(\'DIR_WS_FUNCTIONS\', DIR_WS_INCLUDES . \'functions/\');' . "\n" .
                     '  define(\'DIR_WS_CLASSES\', DIR_WS_INCLUDES . \'classes/\');' . "\n" .
                     '  define(\'DIR_WS_MODULES\', DIR_WS_INCLUDES . \'modules/\');' . "\n" .
                     '  define(\'DIR_WS_LANGUAGES\', DIR_WS_INCLUDES . \'languages/\');' . "\n\n" .
                     '  define(\'DIR_WS_DOWNLOAD_PUBLIC\', \'pub/\');' . "\n" .
                     '  define(\'DIR_FS_CATALOG\', \'' . $dir_fs_document_root . '\');' . "\n" .
                     '  define(\'DIR_FS_DOWNLOAD\', DIR_FS_CATALOG . \'download/\');' . "\n" .
                     '  define(\'DIR_FS_DOWNLOAD_PUBLIC\', DIR_FS_CATALOG . \'pub/\');' . "\n\n" .
                     '  define(\'DB_SERVER\', \'' . trim($_POST['DB_SERVER']) . '\');' . "\n" .
                     '  define(\'DB_SERVER_USERNAME\', \'' . trim($_POST['DB_SERVER_USERNAME']) . '\');' . "\n" .
                     '  define(\'DB_SERVER_PASSWORD\', \'' . trim($_POST['DB_SERVER_PASSWORD']) . '\');' . "\n" .
                     '  define(\'DB_DATABASE\', \'' . trim($_POST['DB_DATABASE']) . '\');' . "\n" .
                     '  define(\'USE_PCONNECT\', \'false\');' . "\n" .
                     '  define(\'STORE_SESSIONS\', \'mysql\');' . "\n";

    if (isset($_POST['CFG_TIME_ZONE'])) {
      $file_contents .= '  define(\'CFG_TIME_ZONE\', \'' . trim($_POST['CFG_TIME_ZONE']) . '\');' . "\n";
    }

    $file_contents .= '?>';

    $fp = fopen($dir_fs_document_root . 'includes/configure.php', 'w');
    fputs($fp, $file_contents);
    fclose($fp);

    @chmod($dir_fs_document_root . 'includes/configure.php', 0644);

    $file_contents = '<?php' . "\n" .
                     '  define(\'HTTP_SERVER\', \'' . $http_server . '\');' . "\n" .
                     '  define(\'HTTPS_SERVER\', \'' . $http_server . '\');' . "\n" .
                     '  define(\'ENABLE_SSL\', false);' . "\n" .
                     '  define(\'HTTP_COOKIE_DOMAIN\', \'\');' . "\n" .
                     '  define(\'HTTPS_COOKIE_DOMAIN\', \'\');' . "\n" .
                     '  define(\'HTTP_COOKIE_PATH\', \'' . $http_catalog . $admin_folder . '\');' . "\n" .
                     '  define(\'HTTPS_COOKIE_PATH\', \'' . $http_catalog . $admin_folder . '\');' . "\n" .
                     '  define(\'HTTP_CATALOG_SERVER\', \'' . $http_server . '\');' . "\n" .
                     '  define(\'HTTPS_CATALOG_SERVER\', \'' . $http_server . '\');' . "\n" .
                     '  define(\'ENABLE_SSL_CATALOG\', \'false\');' . "\n" .
                     '  define(\'DIR_FS_DOCUMENT_ROOT\', \'' . $dir_fs_document_root . '\');' . "\n" .
                     '  define(\'DIR_WS_ADMIN\', \'' . $http_catalog .  $admin_folder . '/\');' . "\n" .
                     '  define(\'DIR_WS_HTTPS_ADMIN\', \'' . $http_catalog .  $admin_folder . '/\');' . "\n" .
                     '  define(\'DIR_FS_ADMIN\', \'' . $dir_fs_document_root .  $admin_folder . '/\');' . "\n" .
                     '  define(\'DIR_WS_CATALOG\', \'' . $http_catalog . '\');' . "\n" .
                     '  define(\'DIR_WS_HTTPS_CATALOG\', \'' . $http_catalog . '\');' . "\n" .
                     '  define(\'DIR_FS_CATALOG\', \'' . $dir_fs_document_root . '\');' . "\n" .
                     '  define(\'DIR_WS_IMAGES\', \'images/\');' . "\n" .
                     '  define(\'DIR_WS_ICONS\', DIR_WS_IMAGES . \'icons/\');' . "\n" .
                     '  define(\'DIR_WS_CATALOG_IMAGES\', DIR_WS_CATALOG . \'images/\');' . "\n" .
                     '  define(\'DIR_WS_INCLUDES\', \'includes/\');' . "\n" .
                     '  define(\'DIR_WS_BOXES\', DIR_WS_INCLUDES . \'boxes/\');' . "\n" .
                     '  define(\'DIR_WS_FUNCTIONS\', DIR_WS_INCLUDES . \'functions/\');' . "\n" .
                     '  define(\'DIR_WS_CLASSES\', DIR_WS_INCLUDES . \'classes/\');' . "\n" .
                     '  define(\'DIR_WS_MODULES\', DIR_WS_INCLUDES . \'modules/\');' . "\n" .
                     '  define(\'DIR_WS_LANGUAGES\', DIR_WS_INCLUDES . \'languages/\');' . "\n" .
                     '  define(\'DIR_WS_CATALOG_LANGUAGES\', DIR_WS_CATALOG . \'includes/languages/\');' . "\n" .
                     '  define(\'DIR_FS_CATALOG_LANGUAGES\', DIR_FS_CATALOG . \'includes/languages/\');' . "\n" .
                     '  define(\'DIR_FS_CATALOG_IMAGES\', DIR_FS_CATALOG . \'images/\');' . "\n" .
                     '  define(\'DIR_FS_CATALOG_MODULES\', DIR_FS_CATALOG . \'includes/modules/\');' . "\n" .
                     '  define(\'DIR_FS_BACKUP\', DIR_FS_ADMIN . \'backups/\');' . "\n" .
                     '  define(\'DIR_FS_DOWNLOAD\', DIR_FS_CATALOG . \'download/\');' . "\n" .
                     '  define(\'DIR_FS_DOWNLOAD_PUBLIC\', DIR_FS_CATALOG . \'pub/\');' . "\n\n" .
                     '  define(\'DB_SERVER\', \'' . trim($_POST['DB_SERVER']) . '\');' . "\n" .
                     '  define(\'DB_SERVER_USERNAME\', \'' . trim($_POST['DB_SERVER_USERNAME']) . '\');' . "\n" .
                     '  define(\'DB_SERVER_PASSWORD\', \'' . trim($_POST['DB_SERVER_PASSWORD']) . '\');' . "\n" .
                     '  define(\'DB_DATABASE\', \'' . trim($_POST['DB_DATABASE']) . '\');' . "\n" .
                     '  define(\'USE_PCONNECT\', \'false\');' . "\n" .
                     '  define(\'STORE_SESSIONS\', \'mysql\');' . "\n";

    if (isset($_POST['CFG_TIME_ZONE'])) {
      $file_contents .= '  define(\'CFG_TIME_ZONE\', \'' . trim($_POST['CFG_TIME_ZONE']) . '\');' . "\n";
    }

    $file_contents .= '?>';

    $fp = fopen($dir_fs_document_root . 'admin/includes/configure.php', 'w');
    fputs($fp, $file_contents);
    fclose($fp);

    @chmod($dir_fs_document_root . 'admin/includes/configure.php', 0644);

    if ($admin_folder != 'admin') {
      @rename($dir_fs_document_root . 'admin', $dir_fs_document_root . $admin_folder);
    }
    ?>

    <div class="alert alert-success">The installation of your online store was successful! Click on either button to start your online selling experience:</div>

    <br />

    <div class="row">
      <div class="col-sm-6"><?php echo osc_draw_button('Online Store (Frontend)', 'cart', $http_server . $http_catalog . 'index.php', 'primary', array('newwindow' => 1), 'btn-success btn-block'); ?></div>
      <div class="col-sm-6"><?php echo osc_draw_button('Administration Tool (Backend)', 'locked', $http_server . $http_catalog . $admin_folder . '/index.php', 'primary', array('newwindow' => 1), 'btn-info btn-block'); ?></div>
    </div>
  </div>
  <div class="col-xs-12 col-sm-pull-9 col-sm-3">
    <div class="panel panel-success">
      <div class="panel-heading">
        Step 4: Finished!
      </div>
      <div class="panel-body">
        <p>Congratulations on installing and configuring osCommerce Online Merchant as your online store solution!</p>
        <p>We wish you all the best with the success of your online store and welcome you to join and participate in our community.</p>
      </div>
      <div class="panel-footer">
        <p class="text-right">- <a href="http://www.oscommerce.com/Us&Team" target="_blank">The osCommerce Team</a></p>
      </div>
    </div>
  </div>
  
</div>
