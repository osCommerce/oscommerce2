<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\Hash;
  use OSC\OM\OSCOM;

  include('includes/application_top.php');

  if (!isset($_SESSION['customer_id'])) die;

// Check download.php was called with proper GET parameters
  if ((isset($_GET['order']) && !is_numeric($_GET['order'])) || (isset($_GET['id']) && !is_numeric($_GET['id'])) ) {
    die;
  }

// Check that order_id, customer_id and filename match
  $Qdownload = $OSCOM_Db->prepare('select date_format(o.date_purchased, "%Y-%m-%d") as date_purchased_day, opd.download_maxdays, opd.download_count, opd.download_maxdays, opd.orders_products_filename from :table_orders o, :table_orders_products op, :table_orders_products_download opd, :table_orders_status os where o.orders_id = :orders_id and o.customers_id = :customers_id and o.orders_id = op.orders_id and op.orders_products_id = opd.orders_products_id and opd.orders_products_download_id = :orders_products_download_id and opd.orders_products_filename != "" and o.orders_status = os.orders_status_id and os.downloads_flag = "1" and os.language_id = :language_id');
  $Qdownload->bindInt(':orders_id', $_GET['order']);
  $Qdownload->bindInt(':customers_id', $_SESSION['customer_id']);
  $Qdownload->bindInt(':orders_products_download_id', $_GET['id']);
  $Qdownload->bindInt(':language_id', $OSCOM_Language->getId());
  $Qdownload->execute();

  if ($Qdownload->fetch() === false) die;

// MySQL 3.22 does not have INTERVAL
  list($dt_year, $dt_month, $dt_day) = explode('-', $Qdownload->value('date_purchased_day'));
  $download_timestamp = mktime(23, 59, 59, $dt_month, $dt_day + $Qdownload->valueInt('download_maxdays'), $dt_year);

// Die if time expired (maxdays = 0 means no time limit)
  if (($Qdownload->valueInt('download_maxdays') != 0) && ($download_timestamp <= time())) die;
// Die if remaining count is <=0
  if ($Qdownload->valueInt('download_count') <= 0) die;
// Die if file is not there
  if (!is_file(OSCOM::getConfig('dir_root') . 'download/' . $Qdownload->value('orders_products_filename'))) die;

// Now decrement counter
  $Qupdate = $OSCOM_Db->prepare('update :table_orders_products_download set download_count = download_count-1 where orders_products_download_id = :orders_products_download_id');
  $Qupdate->bindInt(':orders_products_download_id', $_GET['id']);
  $Qupdate->execute();

// Returns a random name, 16 to 20 characters long
// There are more than 10^28 combinations
// The directory is "hidden", i.e. starts with '.'
function tep_random_name()
{
  $letters = 'abcdefghijklmnopqrstuvwxyz';
  $dirname = '.';
  $length = floor(Hash::getRandomInt(16, 20));
  for ($i = 1; $i <= $length; $i++) {
   $q = floor(Hash::getRandomInt(1, 26));
   $dirname .= $letters[$q];
  }
  return $dirname;
}

// Unlinks all subdirectories and files in $dir
// Works only on one subdir level, will not recurse
function tep_unlink_temp_dir($dir)
{
  $h1 = opendir($dir);
  while ($subdir = readdir($h1)) {
// Ignore non directories
    if (!is_dir($dir . $subdir)) continue;
// Ignore . and .. and CVS
    if ($subdir == '.' || $subdir == '..' || $subdir == 'CVS') continue;
// Loop and unlink files in subdirectory
    $h2 = opendir($dir . $subdir);
    while ($file = readdir($h2)) {
      if ($file == '.' || $file == '..') continue;
      @unlink($dir . $subdir . '/' . $file);
    }
    closedir($h2);
    @rmdir($dir . $subdir);
  }
  closedir($h1);
}


// Now send the file with header() magic
  header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
  header("Last-Modified: " . gmdate("D,d M Y H:i:s") . " GMT");
  header("Cache-Control: no-cache, must-revalidate");
  header("Pragma: no-cache");
  header("Content-Type: Application/octet-stream");
  header("Content-disposition: attachment; filename=" . $Qdownload->value('orders_products_filename'));

  if (DOWNLOAD_BY_REDIRECT == 'true') {
// This will work only on Unix/Linux hosts
    tep_unlink_temp_dir(OSCOM::getConfig('dir_root') . 'pub/');
    $tempdir = tep_random_name();
    umask(0000);
    mkdir(OSCOM::getConfig('dir_root') . 'pub/' . $tempdir, 0777);
    symlink(OSCOM::getConfig('dir_root') . 'download/' . $Qdownload->value('orders_products_filename'), OSCOM::getConfig('dir_root', 'Shop') . 'pub/' . $tempdir . '/' . $Qdownload->value('orders_products_filename'));
    if (is_file(OSCOM::getConfig('dir_root') . 'pub/' . $tempdir . '/' . $Qdownload->value('orders_products_filename'))) {
      OSCOM::redirect('pub/' . $tempdir . '/' . $Qdownload->value('orders_products_filename'));
    }
  }

// Fallback to readfile() delivery method. This will work on all systems, but will need considerable resources
  readfile(OSCOM::getConfig('dir_root') . 'download/' . $Qdownload->value('orders_products_filename'));
?>
