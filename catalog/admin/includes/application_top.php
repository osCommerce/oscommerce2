<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

// Start the clock for the page parse time log
  define('PAGE_PARSE_START_TIME', microtime());

// Set the level of error reporting
  error_reporting(E_ALL & ~E_NOTICE);

  if (defined('E_DEPRECATED')) {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
  }

// check support for register_globals
  if (function_exists('ini_get') && (ini_get('register_globals') == false) && (PHP_VERSION < 4.3) ) {
    exit('Server Requirement Error: register_globals is disabled in your PHP configuration. This can be enabled in your php.ini configuration file or in the .htaccess file in your catalog directory. Please use PHP 4.3+ if register_globals cannot be enabled on the server.');
  }

// load server configuration parameters
  if (file_exists('includes/local/configure.php')) { // for developers
    include('includes/local/configure.php');
  } else {
    include('includes/configure.php');
  }

// Define the project version --- obsolete, now retrieved with tep_get_version()
  define('PROJECT_VERSION', 'osCommerce Online Merchant v2.3');

// some code to solve compatibility issues
  require(DIR_WS_FUNCTIONS . 'compatibility.php');

// set the type of request (secure or not)
  $request_type = (getenv('HTTPS') == 'on') ? 'SSL' : 'NONSSL';

// set php_self in the local scope
  $req = parse_url($HTTP_SERVER_VARS['SCRIPT_NAME']);
  $PHP_SELF = substr($req['path'], ($request_type == 'SSL') ? strlen(DIR_WS_HTTPS_ADMIN) : strlen(DIR_WS_ADMIN));

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
  tep_db_connect() or die('Unable to connect to database server!');

// set application wide parameters
  $configuration_query = tep_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_CONFIGURATION);
  while ($configuration = tep_db_fetch_array($configuration_query)) {
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

// set the cookie domain
  $cookie_domain = (($request_type == 'NONSSL') ? HTTP_COOKIE_DOMAIN : HTTPS_COOKIE_DOMAIN);
  $cookie_path = (($request_type == 'NONSSL') ? HTTP_COOKIE_PATH : HTTPS_COOKIE_PATH);

// set the session name and save path
  tep_session_name('osCAdminID');
  tep_session_save_path(SESSION_WRITE_DIRECTORY);

// set the session cookie parameters
   if (function_exists('session_set_cookie_params')) {
    session_set_cookie_params(0, $cookie_path, $cookie_domain);
  } elseif (function_exists('ini_set')) {
    ini_set('session.cookie_lifetime', '0');
    ini_set('session.cookie_path', $cookie_path);
    ini_set('session.cookie_domain', $cookie_domain);
  }

  @ini_set('session.use_only_cookies', (SESSION_FORCE_COOKIE_USE == 'True') ? 1 : 0);

// lets start our session
  tep_session_start();

  if ( (PHP_VERSION >= 4.3) && function_exists('ini_get') && (ini_get('register_globals') == false) ) {
    extract($_SESSION, EXTR_OVERWRITE+EXTR_REFS);
  }

// set the language
  if (!tep_session_is_registered('language') || isset($HTTP_GET_VARS['language'])) {
    if (!tep_session_is_registered('language')) {
      tep_session_register('language');
      tep_session_register('languages_id');
    }

    include(DIR_WS_CLASSES . 'language.php');
    $lng = new language();

    if (isset($HTTP_GET_VARS['language']) && tep_not_null($HTTP_GET_VARS['language'])) {
      $lng->set_language($HTTP_GET_VARS['language']);
    } else {
      $lng->get_browser_language();
    }

    $language = $lng->language['directory'];
    $languages_id = $lng->language['id'];
  }

// redirect to login page if administrator is not yet logged in
  if (!tep_session_is_registered('admin')) {
    $redirect = false;

    $current_page = $PHP_SELF;

// if the first page request is to the login page, set the current page to the index page
// so the redirection on a successful login is not made to the login page again
    if ( ($current_page == FILENAME_LOGIN) && !tep_session_is_registered('redirect_origin') ) {
      $current_page = FILENAME_DEFAULT;
      $HTTP_GET_VARS = array();
    }

    if ($current_page != FILENAME_LOGIN) {
      if (!tep_session_is_registered('redirect_origin')) {
        tep_session_register('redirect_origin');

        $redirect_origin = array('page' => $current_page,
                                 'get' => $HTTP_GET_VARS);
      }

// try to automatically login with the HTTP Authentication values if it exists
      if (!tep_session_is_registered('auth_ignore')) {
        if (isset($HTTP_SERVER_VARS['PHP_AUTH_USER']) && !empty($HTTP_SERVER_VARS['PHP_AUTH_USER']) && isset($HTTP_SERVER_VARS['PHP_AUTH_PW']) && !empty($HTTP_SERVER_VARS['PHP_AUTH_PW'])) {
          $redirect_origin['auth_user'] = $HTTP_SERVER_VARS['PHP_AUTH_USER'];
          $redirect_origin['auth_pw'] = $HTTP_SERVER_VARS['PHP_AUTH_PW'];
        }
      }

      $redirect = true;
    }

    if (!isset($login_request) || isset($HTTP_GET_VARS['login_request']) || isset($HTTP_POST_VARS['login_request']) || isset($HTTP_COOKIE_VARS['login_request']) || isset($HTTP_SESSION_VARS['login_request']) || isset($HTTP_POST_FILES['login_request']) || isset($HTTP_SERVER_VARS['login_request'])) {
      $redirect = true;
    }

    if ($redirect == true) {
      tep_redirect(tep_href_link(FILENAME_LOGIN, (isset($redirect_origin['auth_user']) ? 'action=process' : '')));
    }

    unset($redirect);
  }

// include the language translations
  $_system_locale_numeric = setlocale(LC_NUMERIC, 0);
  require(DIR_WS_LANGUAGES . $language . '.php');
  setlocale(LC_NUMERIC, $_system_locale_numeric); // Prevent LC_ALL from setting LC_NUMERIC to a locale with 1,0 float/decimal values instead of 1.0 (see bug #634)

  $current_page = basename($PHP_SELF);
  if (file_exists(DIR_WS_LANGUAGES . $language . '/' . $current_page)) {
    include(DIR_WS_LANGUAGES . $language . '/' . $current_page);
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
  if (isset($HTTP_GET_VARS['cPath'])) {
    $cPath = $HTTP_GET_VARS['cPath'];
  } else {
    $cPath = '';
  }

  if (tep_not_null($cPath)) {
    $cPath_array = tep_parse_category_path($cPath);
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
