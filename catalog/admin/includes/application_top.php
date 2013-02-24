<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2008 osCommerce

  Released under the GNU General Public License
*/

// Start the clock for the page parse time log
  define('PAGE_PARSE_START_TIME', microtime());

// Set the level of error reporting
  error_reporting(E_ALL & ~E_NOTICE);

// load server configuration parameters
  if (file_exists('includes/local/configure.php')) { // for developers
    include('includes/local/configure.php');
  } else {
    include('includes/configure.php');
  }

// Define the project version --- obsolete, now retrieved with osc_get_version()
  define('PROJECT_VERSION', 'osCommerce Online Merchant v2.3');

// some code to solve compatibility issues
  require(DIR_WS_FUNCTIONS . 'compatibility.php');

// set php_self in the local scope
  $PHP_SELF = (((strlen(ini_get('cgi.fix_pathinfo')) > 0) && ((bool)ini_get('cgi.fix_pathinfo') == false)) || !isset($_SERVER['SCRIPT_NAME'])) ? basename($_SERVER['PHP_SELF']) : basename($_SERVER['SCRIPT_NAME']);

// Used in the "Backup Manager" to compress backups
  define('LOCAL_EXE_GZIP', 'gzip');
  define('LOCAL_EXE_GUNZIP', 'gunzip');
  define('LOCAL_EXE_ZIP', 'zip');
  define('LOCAL_EXE_UNZIP', 'unzip');

// include the list of project filenames
  require(DIR_WS_INCLUDES . 'filenames.php');

// include the list of project database tables
  require(DIR_WS_INCLUDES . 'database_tables.php');

// Define how do we update currency exchange rates
// Possible values are 'oanda' 'xe' or ''
  define('CURRENCY_SERVER_PRIMARY', 'oanda');
  define('CURRENCY_SERVER_BACKUP', 'xe');

// include the database functions
  require(DIR_WS_FUNCTIONS . 'database.php');

// make a connection to the database... now
  osc_db_connect() or die('Unable to connect to database server!');

// set application wide parameters
  $configuration_query = osc_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_CONFIGURATION);
  while ($configuration = osc_db_fetch_array($configuration_query)) {
    define($configuration['cfgKey'], $configuration['cfgValue']);
  }

// define our general functions used application-wide
  require(DIR_WS_FUNCTIONS . 'general.php');
  require(DIR_WS_FUNCTIONS . 'html_output.php');

// initialize the logger class
  require(DIR_WS_CLASSES . 'logger.php');

// include shopping cart class
  require(DIR_WS_CLASSES . 'shopping_cart.php');

// define how the session functions will be used
  require(DIR_WS_FUNCTIONS . 'sessions.php');

// set the session name and save path
  session_name('osCAdminID');
  session_save_path(SESSION_WRITE_DIRECTORY);

// set the session cookie parameters
  session_set_cookie_params(0, DIR_WS_ADMIN);

  @ini_set('session.use_only_cookies', (SESSION_FORCE_COOKIE_USE == 'True') ? 1 : 0);

// lets start our session
  osc_session_start();

// set the language
  if (!isset($_SESSION['language']) || isset($_GET['language'])) {
    include(DIR_WS_CLASSES . 'language.php');
    $lng = new language();

    if (isset($_GET['language']) && osc_not_null($_GET['language'])) {
      $lng->set_language($_GET['language']);
    } else {
      $lng->get_browser_language();
    }

    $_SESSION['language'] = $lng->language['directory'];
    $_SESSION['languages_id'] = $lng->language['id'];
  }

// redirect to login page if administrator is not yet logged in
  if (!isset($_SESSION['admin'])) {
    $redirect = false;

    $current_page = basename($PHP_SELF);

// if the first page request is to the login page, set the current page to the index page
// so the redirection on a successful login is not made to the login page again
    if ( ($current_page == FILENAME_LOGIN) && !isset($_SESSION['redirect_origin']) ) {
      $current_page = FILENAME_DEFAULT;
      $_GET = array();
    }

    if ($current_page != FILENAME_LOGIN) {
      if (!isset($_SESSION['redirect_origin'])) {
        $_SESSION['redirect_origin'] = array('page' => $current_page,
                                             'get' => $_GET);
      }

// try to automatically login with the HTTP Authentication values if it exists
      if (!isset($_SESSION['auth_ignore'])) {
        if (isset($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) && !empty($_SERVER['PHP_AUTH_PW'])) {
          $_SESSION['redirect_origin']['auth_user'] = $_SERVER['PHP_AUTH_USER'];
          $_SESSION['redirect_origin']['auth_pw'] = $_SERVER['PHP_AUTH_PW'];
        }
      }

      $redirect = true;
    }

    if (!isset($login_request) || isset($_GET['login_request']) || isset($_POST['login_request']) || isset($_COOKIE['login_request']) || isset($_SESSION['login_request']) || isset($_FILES['login_request']) || isset($_SERVER['login_request'])) {
      $redirect = true;
    }

    if ($redirect == true) {
      osc_redirect(osc_href_link(FILENAME_LOGIN, (isset($_SESSION['redirect_origin']['auth_user']) ? 'action=process' : '')));
    }

    unset($redirect);
  }

// include the language translations
  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '.php');
  $current_page = basename($PHP_SELF);
  if (file_exists(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . $current_page)) {
    include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . $current_page);
  }

// define our localization functions
  require(DIR_WS_FUNCTIONS . 'localization.php');

// Include validation functions (right now only email address)
  require(DIR_WS_FUNCTIONS . 'validations.php');

// setup our boxes
  require(DIR_WS_CLASSES . 'table_block.php');
  require(DIR_WS_CLASSES . 'box.php');

// initialize the message stack for output messages
  require(DIR_WS_CLASSES . 'message_stack.php');
  $messageStack = new messageStack;

// split-page-results
  require(DIR_WS_CLASSES . 'split_page_results.php');

// entry/item info classes
  require(DIR_WS_CLASSES . 'object_info.php');

// email classes
  require(DIR_WS_CLASSES . 'mime.php');
  require(DIR_WS_CLASSES . 'email.php');

// file uploading class
  require(DIR_WS_CLASSES . 'upload.php');

// action recorder
  require(DIR_WS_CLASSES . 'action_recorder.php');

// calculate category path
  if (isset($_GET['cPath'])) {
    $cPath = $_GET['cPath'];
  } else {
    $cPath = '';
  }

  if (osc_not_null($cPath)) {
    $cPath_array = osc_parse_category_path($cPath);
    $cPath = implode('_', $cPath_array);
    $current_category_id = $cPath_array[(sizeof($cPath_array)-1)];
  } else {
    $current_category_id = 0;
  }

// initialize configuration modules
  require(DIR_WS_CLASSES . 'cfg_modules.php');
  $cfgModules = new cfg_modules();

// the following cache blocks are used in the Tools->Cache section
// ('language' in the filename is automatically replaced by available languages)
  $cache_blocks = array(array('title' => TEXT_CACHE_CATEGORIES, 'code' => 'categories', 'file' => 'categories_box-language.cache', 'multiple' => true),
                        array('title' => TEXT_CACHE_MANUFACTURERS, 'code' => 'manufacturers', 'file' => 'manufacturers_box-language.cache', 'multiple' => true),
                        array('title' => TEXT_CACHE_ALSO_PURCHASED, 'code' => 'also_purchased', 'file' => 'also_purchased-language.cache', 'multiple' => true)
                       );
?>
