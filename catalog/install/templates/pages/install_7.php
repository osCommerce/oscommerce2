<?php
/*
  $Id: install_7.php,v 1.1 2003/07/09 01:11:06 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  $dir_fs_document_root = $HTTP_POST_VARS['DIR_FS_DOCUMENT_ROOT'];
  if ((substr($dir_fs_document_root, -1) != '/') && (substr($dir_fs_document_root, -1) != '/')) {
    $where = strrpos($dir_fs_document_root, '\\');
    if (is_string($where) && !$where) {
      $dir_fs_document_root .= '/';
    } else {
      $dir_fs_document_root .= '\\';
    }
  }
?>

<p class="pageTitle">New Installation</p>

<p><b>osCommerce Configuration</b></p>

<?php
  $db = array();
  $db['DB_SERVER'] = trim(stripslashes($HTTP_POST_VARS['DB_SERVER']));
  $db['DB_SERVER_USERNAME'] = trim(stripslashes($HTTP_POST_VARS['DB_SERVER_USERNAME']));
  $db['DB_SERVER_PASSWORD'] = trim(stripslashes($HTTP_POST_VARS['DB_SERVER_PASSWORD']));
  $db['DB_DATABASE'] = trim(stripslashes($HTTP_POST_VARS['DB_DATABASE']));

  $db_error = false;
  osc_db_connect($db['DB_SERVER'], $db['DB_SERVER_USERNAME'], $db['DB_SERVER_PASSWORD']);

  if ($db_error == false) {
    osc_db_test_connection($db['DB_DATABASE']);
  }

  if ($db_error != false) {
?>
<form name="install" action="install.php?step=6" method="post">

<table width="95%" border="0" cellpadding="2" class="formPage">
  <tr>
    <td>
      <p>A test connection made to the database was <b>NOT</b> successful.</p>
      <p>The error message returned is:</p>
      <p class="boxme"><?php echo $db_error; ?></p>
      <p>Please click on the <i>Back</i> button below to review your database server settings.</p>
      <p>If you require help with your database server settings, please consult your hosting company.</p>
    </td>
  </tr>
</table>

<p>&nbsp;</p>

<table border="0" width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td align="center"><a href="index.php"><img src="images/button_cancel.gif" border="0" alt="Cancel"></a></td>
    <td align="center"><input type="image" src="images/button_back.gif" border="0" alt="Back"></td>
  </tr>
</table>

<?php
    reset($HTTP_POST_VARS);
    while (list($key, $value) = each($HTTP_POST_VARS)) {
      if ($key != 'x' && $key != 'y') {
        if (is_array($value)) {
          for ($i=0; $i<sizeof($value); $i++) {
            echo osc_draw_hidden_field($key . '[]', $value[$i]);
          }
        } else {
          echo osc_draw_hidden_field($key, $value);
        }
      }
    }
?>

</form>

<?php
  } elseif ( ( (file_exists($dir_fs_document_root . 'includes/configure.php')) && (!is_writeable($dir_fs_document_root . 'includes/configure.php')) ) || ( (file_exists($dir_fs_document_root . '/admin/includes/configure.php')) && (!is_writeable($dir_fs_document_root . '/admin/includes/configure.php')) ) ) {
?>
<form name="install" action="install.php?step=7" method="post">

<table width="95%" border="0" cellpadding="2" class="formPage">
  <tr>
    <td>
      <p>The following error has occurred:</p>
      <p><div class="boxMe"><b>The configuration files do not exist, or permission levels are not set.</b><br><br>Please perform the following actions:
        <ul class="boxMe"><li>cd <?php echo $dir_fs_document_root; ?>includes/</li><li>touch configure.php</li><li>chmod 706 configure.php</li></ul>
        <ul class="boxMe"><li>cd <?php echo $dir_fs_document_root; ?>admin/includes/</li><li>touch configure.php</li><li>chmod 706 configure.php</li></ul></div>
      </p>
      <p class="noteBox">If <i>chmod 706</i> does not work, please try <i>chmod 777</i>.</p>
      <p class="noteBox">If you are running this installation procedure under a Microsoft Windows environment, try renaming the existing configuration file so a new file can be created.</p>
    </td>
  </tr>
</table>

<p>&nbsp;</p>

<table border="0" width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td align="center"><a href="index.php"><img src="images/button_cancel.gif" border="0" alt="Cancel"></a></td>
    <td align="center"><input type="image" src="images/button_retry.gif" border="0" alt="Retry"></td>
  </tr>
</table>

<?php
    reset($HTTP_POST_VARS);
    while (list($key, $value) = each($HTTP_POST_VARS)) {
      if ($key != 'x' && $key != 'y') {
        if (is_array($value)) {
          for ($i=0; $i<sizeof($value); $i++) {
            echo osc_draw_hidden_field($key . '[]', $value[$i]);
          }
        } else {
          echo osc_draw_hidden_field($key, $value);
        }
      }
    }
?>

</form>

<?php
  } else {
    $http_url = parse_url($HTTP_POST_VARS['HTTP_WWW_ADDRESS']);
    $http_server = $http_url['scheme'] . '://' . $http_url['host'];
    $http_catalog = $http_url['path'];
    if (isset($http_url['port']) && !empty($http_url['port'])) {
      $http_server .= ':' . $http_url['port'];
    }

    if (substr($http_catalog, -1) != '/') {
      $http_catalog .= '/';
    }

    $https_server = '';
    $https_catalog = '';
    if (isset($HTTP_POST_VARS['HTTPS_WWW_ADDRESS']) && !empty($HTTP_POST_VARS['HTTPS_WWW_ADDRESS'])) {
      $https_url = parse_url($HTTP_POST_VARS['HTTPS_WWW_ADDRESS']);
      $https_server = $https_url['scheme'] . '://' . $https_url['host'];
      $https_catalog = $https_url['path'];

      if (isset($https_url['port']) && !empty($https_url['port'])) {
        $https_server .= ':' . $https_url['port'];
      }

      if (substr($https_catalog, -1) != '/') {
        $https_catalog .= '/';
      }
    }

    $enable_ssl = (isset($HTTP_POST_VARS['ENABLE_SSL']) && ($HTTP_POST_VARS['ENABLE_SSL'] == 'true') ? 'true' : 'false');
    $http_cookie_domain = $HTTP_POST_VARS['HTTP_COOKIE_DOMAIN'];
    $https_cookie_domain = (isset($HTTP_POST_VARS['HTTPS_COOKIE_DOMAIN']) ? $HTTP_POST_VARS['HTTPS_COOKIE_DOMAIN'] : '');
    $http_cookie_path = $HTTP_POST_VARS['HTTP_COOKIE_PATH'];
    $https_cookie_path = (isset($HTTP_POST_VARS['HTTPS_COOKIE_PATH']) ? $HTTP_POST_VARS['HTTPS_COOKIE_PATH'] : '');

    $file_contents = '<?php' . "\n" .
                     '/*' . "\n" .
                     '  osCommerce, Open Source E-Commerce Solutions' . "\n" .
                     '  http://www.oscommerce.com' . "\n" .
                     '' . "\n" .
                     '  Copyright (c) 2003 osCommerce' . "\n" .
                     '' . "\n" .
                     '  Released under the GNU General Public License' . "\n" .
                     '*/' . "\n" .
                     '' . "\n" .
                     '// Define the webserver and path parameters' . "\n" .
                     '// * DIR_FS_* = Filesystem directories (local/physical)' . "\n" .
                     '// * DIR_WS_* = Webserver directories (virtual/URL)' . "\n" .
                     '  define(\'HTTP_SERVER\', \'' . $http_server . '\'); // eg, http://localhost - should not be empty for productive servers' . "\n" .
                     '  define(\'HTTPS_SERVER\', \'' . $https_server . '\'); // eg, https://localhost - should not be empty for productive servers' . "\n" .
                     '  define(\'ENABLE_SSL\', ' . $enable_ssl . '); // secure webserver for checkout procedure?' . "\n" .
                     '  define(\'HTTP_COOKIE_DOMAIN\', \'' . $http_cookie_domain . '\');' . "\n" .
                     '  define(\'HTTPS_COOKIE_DOMAIN\', \'' . $https_cookie_domain . '\');' . "\n" .
                     '  define(\'HTTP_COOKIE_PATH\', \'' . $http_cookie_path . '\');' . "\n" .
                     '  define(\'HTTPS_COOKIE_PATH\', \'' . $https_cookie_path . '\');' . "\n" .
                     '  define(\'DIR_WS_HTTP_CATALOG\', \'' . $http_catalog . '\');' . "\n" .
                     '  define(\'DIR_WS_HTTPS_CATALOG\', \'' . $https_catalog . '\');' . "\n" .
                     '  define(\'DIR_WS_IMAGES\', \'images/\');' . "\n" .
                     '  define(\'DIR_WS_ICONS\', DIR_WS_IMAGES . \'icons/\');' . "\n" .
                     '  define(\'DIR_WS_INCLUDES\', \'includes/\');' . "\n" .
                     '  define(\'DIR_WS_BOXES\', DIR_WS_INCLUDES . \'boxes/\');' . "\n" .
                     '  define(\'DIR_WS_FUNCTIONS\', DIR_WS_INCLUDES . \'functions/\');' . "\n" .
                     '  define(\'DIR_WS_CLASSES\', DIR_WS_INCLUDES . \'classes/\');' . "\n" .
                     '  define(\'DIR_WS_MODULES\', DIR_WS_INCLUDES . \'modules/\');' . "\n" .
                     '  define(\'DIR_WS_LANGUAGES\', DIR_WS_INCLUDES . \'languages/\');' . "\n" .
                     '' . "\n" .
                     '  define(\'DIR_WS_DOWNLOAD_PUBLIC\', \'pub/\');' . "\n" .
                     '  define(\'DIR_FS_CATALOG\', \'' . $dir_fs_document_root . '\');' . "\n" .
                     '  define(\'DIR_FS_DOWNLOAD\', DIR_FS_CATALOG . \'download/\');' . "\n" .
                     '  define(\'DIR_FS_DOWNLOAD_PUBLIC\', DIR_FS_CATALOG . \'pub/\');' . "\n" .
                     '' . "\n" .
                     '// define our database connection' . "\n" .
                     '  define(\'DB_SERVER\', \'' . $HTTP_POST_VARS['DB_SERVER'] . '\'); // eg, localhost - should not be empty for productive servers' . "\n" .
                     '  define(\'DB_SERVER_USERNAME\', \'' . $HTTP_POST_VARS['DB_SERVER_USERNAME'] . '\');' . "\n" .
                     '  define(\'DB_SERVER_PASSWORD\', \'' . $HTTP_POST_VARS['DB_SERVER_PASSWORD']. '\');' . "\n" .
                     '  define(\'DB_DATABASE\', \'' . $HTTP_POST_VARS['DB_DATABASE']. '\');' . "\n" .
                     '  define(\'USE_PCONNECT\', \'' . (($HTTP_POST_VARS['USE_PCONNECT'] == 'true') ? 'true' : 'false') . '\'); // use persistent connections?' . "\n" .
                     '  define(\'STORE_SESSIONS\', \'' . (($HTTP_POST_VARS['STORE_SESSIONS'] == 'files') ? '' : 'mysql') . '\'); // leave empty \'\' for default handler or set to \'mysql\'' . "\n" .
                     '?>';

    $fp = fopen($dir_fs_document_root . 'includes/configure.php', 'w');
    fputs($fp, $file_contents);
    fclose($fp);

    $file_contents = '<?php' . "\n" .
                     '/*' . "\n" .
                     '  osCommerce, Open Source E-Commerce Solutions' . "\n" .
                     '  http://www.oscommerce.com' . "\n" .
                     '' . "\n" .
                     '  Copyright (c) 2003 osCommerce' . "\n" .
                     '' . "\n" .
                     '  Released under the GNU General Public License' . "\n" .
                     '*/' . "\n" .
                     '' . "\n" .
                     '// Define the webserver and path parameters' . "\n" .
                     '// * DIR_FS_* = Filesystem directories (local/physical)' . "\n" .
                     '// * DIR_WS_* = Webserver directories (virtual/URL)' . "\n" .
                     '  define(\'HTTP_SERVER\', \'' . $http_server . '\'); // eg, http://localhost - should not be empty for productive servers' . "\n" .
                     '  define(\'HTTP_CATALOG_SERVER\', \'' . $http_server . '\');' . "\n" .
                     '  define(\'HTTPS_CATALOG_SERVER\', \'' . $https_server . '\');' . "\n" .
                     '  define(\'ENABLE_SSL_CATALOG\', \'' . $enable_ssl . '\'); // secure webserver for catalog module' . "\n" .
                     '  define(\'DIR_FS_DOCUMENT_ROOT\', \'' . $dir_fs_document_root . '\'); // where the pages are located on the server' . "\n" .
                     '  define(\'DIR_WS_ADMIN\', \'' . $http_catalog . 'admin/\'); // absolute path required' . "\n" .
                     '  define(\'DIR_FS_ADMIN\', \'' . $dir_fs_document_root . 'admin/\'); // absolute pate required' . "\n" .
                     '  define(\'DIR_WS_CATALOG\', \'' . $http_catalog . '\'); // absolute path required' . "\n" .
                     '  define(\'DIR_FS_CATALOG\', \'' . $dir_fs_document_root . '\'); // absolute path required' . "\n" .
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
                     '' . "\n" .
                     '// define our database connection' . "\n" .
                     '  define(\'DB_SERVER\', \'' . $HTTP_POST_VARS['DB_SERVER'] . '\'); // eg, localhost - should not be empty for productive servers' . "\n" .
                     '  define(\'DB_SERVER_USERNAME\', \'' . $HTTP_POST_VARS['DB_SERVER_USERNAME'] . '\');' . "\n" .
                     '  define(\'DB_SERVER_PASSWORD\', \'' . $HTTP_POST_VARS['DB_SERVER_PASSWORD']. '\');' . "\n" .
                     '  define(\'DB_DATABASE\', \'' . $HTTP_POST_VARS['DB_DATABASE']. '\');' . "\n" .
                     '  define(\'USE_PCONNECT\', \'' . (($HTTP_POST_VARS['USE_PCONNECT'] == 'true') ? 'true' : 'false') . '\'); // use persisstent connections?' . "\n" .
                     '  define(\'STORE_SESSIONS\', \'' . (($HTTP_POST_VARS['STORE_SESSIONS'] == 'files') ? '' : 'mysql') . '\'); // leave empty \'\' for default handler or set to \'mysql\'' . "\n" .
                     '?>';

    $fp = fopen($dir_fs_document_root . 'admin/includes/configure.php', 'w');
    fputs($fp, $file_contents);
    fclose($fp);
?>

<table width="95%" border="0" cellpadding="2" class="formPage">
  <tr>
    <td>The configuration was successful!</td>
 </tr>
</table>

<p>&nbsp;</p>

<table border="0" width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td align="center"><a href="<?php echo $http_server . $http_catalog . 'index.php'; ?>" target="_blank"><img src="images/button_catalog.gif" border="0" alt="Catalog"></a></td>
    <td align="center"><a href="<?php echo $http_server . $http_catalog . 'admin/index.php'; ?>" target="_blank"><img src="images/button_administration_tool.gif" border="0" alt="Administration Tool"></a></td>
  </tr>
</table>

<?php
  }
?>
