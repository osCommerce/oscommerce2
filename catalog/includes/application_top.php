<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

// start the timer for the page parse time log
  define('PAGE_PARSE_START_TIME', microtime());

// set the level of error reporting
  error_reporting(E_ALL | E_STRICT);

  ini_set('display_errors', true);

// load server configuration parameters
  if (file_exists('includes/local/configure.php')) { // for developers
    include('includes/local/configure.php');
  } else {
    include('includes/configure.php');
  }

  if (strlen(DB_SERVER) < 1) {
    if (is_dir('install')) {
      header('Location: install/index.php');
    }
  }

// define the project version --- obsolete, now retrieved with osc_get_version()
  define('PROJECT_VERSION', 'osCommerce Online Merchant v2.3');

// some code to solve compatibility issues
  require(DIR_WS_FUNCTIONS . 'compatibility.php');

// set the type of request (secure or not)
  $request_type = (getenv('HTTPS') == 'on') ? 'SSL' : 'NONSSL';

// set php_self in the local scope
  $PHP_SELF = (((strlen(ini_get('cgi.fix_pathinfo')) > 0) && ((bool)ini_get('cgi.fix_pathinfo') == false)) || !isset($_SERVER['SCRIPT_NAME'])) ? basename($_SERVER['PHP_SELF']) : basename($_SERVER['SCRIPT_NAME']);

  if ($request_type == 'NONSSL') {
    define('DIR_WS_CATALOG', DIR_WS_HTTP_CATALOG);
  } else {
    define('DIR_WS_CATALOG', DIR_WS_HTTPS_CATALOG);
  }

// include the list of project database tables
  require(DIR_WS_INCLUDES . 'database_tables.php');

// define general functions used application-wide
  require(DIR_WS_FUNCTIONS . 'general.php');
  require(DIR_WS_FUNCTIONS . 'html_output.php');

// include the database functions
  require(DIR_WS_FUNCTIONS . 'database.php');

// make a connection to the database... now
  osc_db_connect() or die('Unable to connect to database server!');

  require(DIR_WS_CLASSES . 'cache.php');
  $OSCOM_Cache = new cache();

  require(DIR_WS_CLASSES . 'db.php');
  $OSCOM_PDO = db::initialize();

// set the application parameters
  $Qcfg = $OSCOM_PDO->query('select configuration_key as cfgKey, configuration_value as cfgValue from :table_configuration');
//  $Qcfg->setCache('configuration');
  $Qcfg->execute();

  while ( $Qcfg->fetch() ) {
    define($Qcfg->value('cfgKey'), $Qcfg->value('cfgValue'));
  }

// if gzip_compression is enabled, start to buffer the output
  if ( (GZIP_COMPRESSION == 'true') && ($ext_zlib_loaded = extension_loaded('zlib')) && !headers_sent() ) {
    if (($ini_zlib_output_compression = (int)ini_get('zlib.output_compression')) < 1) {
      if (PHP_VERSION < '5.4' || PHP_VERSION > '5.4.5') { // see PHP bug 55544
        ob_start('ob_gzhandler');
      }
    } elseif (function_exists('ini_set')) {
      ini_set('zlib.output_compression_level', GZIP_LEVEL);
    }
  }

// set the HTTP GET parameters manually if search_engine_friendly_urls is enabled
  if (SEARCH_ENGINE_FRIENDLY_URLS == 'true') {
    if (strlen(getenv('PATH_INFO')) > 1) {
      $GET_array = array();
      $PHP_SELF = str_replace(getenv('PATH_INFO'), '', $PHP_SELF);
      $vars = explode('/', substr(getenv('PATH_INFO'), 1));
      for ($i=0, $n=sizeof($vars); $i<$n; $i++) {
        if (strpos($vars[$i], '[]')) {
          $GET_array[substr($vars[$i], 0, -2)][] = $vars[$i+1];
        } else {
          $_GET[$vars[$i]] = $vars[$i+1];
        }
        $i++;
      }

      if (sizeof($GET_array) > 0) {
        while (list($key, $value) = each($GET_array)) {
          $_GET[$key] = $value;
        }
      }
    }
  }

// set the cookie domain
  $cookie_domain = (($request_type == 'NONSSL') ? HTTP_COOKIE_DOMAIN : HTTPS_COOKIE_DOMAIN);
  $cookie_path = (($request_type == 'NONSSL') ? HTTP_COOKIE_PATH : HTTPS_COOKIE_PATH);

// include cache functions if enabled
  if (USE_CACHE == 'true') include(DIR_WS_FUNCTIONS . 'cache.php');

// include shopping cart class
  require(DIR_WS_CLASSES . 'shopping_cart.php');

// include navigation history class
  require(DIR_WS_CLASSES . 'navigation_history.php');

// define how the session functions will be used
  require(DIR_WS_FUNCTIONS . 'sessions.php');

// set the session name and save path
  session_name('osCsid');
  session_save_path(SESSION_WRITE_DIRECTORY);

// set the session cookie parameters
  session_set_cookie_params(0, $cookie_path, $cookie_domain);

  @ini_set('session.use_only_cookies', (SESSION_FORCE_COOKIE_USE == 'True') ? 1 : 0);

// set the session ID if it exists
   if (isset($_POST[session_name()])) {
     session_id($_POST[session_name()]);
   } elseif ( ($request_type == 'SSL') && isset($_GET[session_name()]) ) {
     session_id($_GET[session_name()]);
   }

// start the session
  $session_started = false;
  if (SESSION_FORCE_COOKIE_USE == 'True') {
    osc_setcookie('cookie_test', 'please_accept_for_session', time()+60*60*24*30, $cookie_path, $cookie_domain);

    if (isset($_COOKIE['cookie_test'])) {
      osc_session_start();
      $session_started = true;
    }
  } elseif (SESSION_BLOCK_SPIDERS == 'True') {
    $user_agent = strtolower(getenv('HTTP_USER_AGENT'));
    $spider_flag = false;

    if (osc_not_null($user_agent)) {
      $spiders = file(DIR_WS_INCLUDES . 'spiders.txt');

      for ($i=0, $n=sizeof($spiders); $i<$n; $i++) {
        if (osc_not_null($spiders[$i])) {
          if (is_integer(strpos($user_agent, trim($spiders[$i])))) {
            $spider_flag = true;
            break;
          }
        }
      }
    }

    if ($spider_flag == false) {
      osc_session_start();
      $session_started = true;
    }
  } else {
    osc_session_start();
    $session_started = true;
  }

// initialize a session token
  if (!isset($_SESSION['sessiontoken'])) {
    $_SESSION['sessiontoken'] = md5(osc_rand() . osc_rand() . osc_rand() . osc_rand());
  }

// set SID once, even if empty
  $SID = (defined('SID') ? SID : '');

// verify the ssl_session_id if the feature is enabled
  if ( ($request_type == 'SSL') && (SESSION_CHECK_SSL_SESSION_ID == 'True') && (ENABLE_SSL == true) && ($session_started == true) ) {
    $ssl_session_id = $_SERVER['SSL_SESSION_ID'];
    if (!isset($_SESSION['SSL_SESSION_ID'])) {
      $_SESSION['SESSION_SSL_ID'] = $ssl_session_id;
    }

    if ($_SESSION['SESSION_SSL_ID'] != $ssl_session_id) {
      session_destroy();
      osc_redirect(osc_href_link('info', 'ssl_check'));
    }
  }

// verify the browser user agent if the feature is enabled
  if (SESSION_CHECK_USER_AGENT == 'True') {
    if (!isset($_SESSION['SESSION_USER_AGENT'])) {
      $_SESSION['SESSION_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
    }

    if ($_SESSION['SESSION_USER_AGENT'] != $_SERVER['HTTP_USER_AGENT']) {
      session_destroy();
      osc_redirect(osc_href_link('account', 'login', 'SSL'));
    }
  }

// verify the IP address if the feature is enabled
  if (SESSION_CHECK_IP_ADDRESS == 'True') {
    $ip_address = osc_get_ip_address();
    if (!isset($_SESSION['SESSION_IP_ADDRESS'])) {
      $_SESSION['SESSION_IP_ADDRESS'] = $ip_address;
    }

    if ($_SESSION['SESSION_IP_ADDRESS'] != $ip_address) {
      session_destroy();
      osc_redirect(osc_href_link('account', 'login', 'SSL'));
    }
  }

// create the shopping cart
  if (!isset($_SESSION['cart']) || !is_object($_SESSION['cart'])) {
    $_SESSION['cart'] = new shoppingCart;
  }

// include currencies class and create an instance
  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

// include the mail classes
  require(DIR_WS_CLASSES . 'mime.php');
  require(DIR_WS_CLASSES . 'email.php');

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

// include the language translations
  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '.php');

// currency
  if (!isset($_SESSION['currency']) || isset($_GET['currency']) || ( (USE_DEFAULT_LANGUAGE_CURRENCY == 'true') && (LANGUAGE_CURRENCY != $_SESSION['currency']) ) ) {
    if (isset($_GET['currency']) && $currencies->is_set($_GET['currency'])) {
      $_SESSION['currency'] = $_GET['currency'];
    } else {
      $_SESSION['currency'] = ((USE_DEFAULT_LANGUAGE_CURRENCY == 'true') && $currencies->is_set(LANGUAGE_CURRENCY)) ? LANGUAGE_CURRENCY : DEFAULT_CURRENCY;
    }
  }

// navigation history
  if (!isset($_SESSION['navigation']) || !is_object($_SESSION['navigation'])) {
    $_SESSION['navigation'] = new navigationHistory;
  }
  $_SESSION['navigation']->add_current_page();

// action recorder
  include('includes/classes/action_recorder.php');

// include the who's online functions
  require(DIR_WS_FUNCTIONS . 'whos_online.php');
  osc_update_whos_online();

// include the password crypto functions
  require(DIR_WS_FUNCTIONS . 'password_funcs.php');

// include validation functions (right now only email address)
  require(DIR_WS_FUNCTIONS . 'validations.php');

// split-page-results
  require(DIR_WS_CLASSES . 'split_page_results.php');

// infobox
  require(DIR_WS_CLASSES . 'boxes.php');

// auto activate and expire banners
  require(DIR_WS_FUNCTIONS . 'banner.php');
  osc_activate_banners();
  osc_expire_banners();

// auto expire special products
  require(DIR_WS_FUNCTIONS . 'specials.php');
  osc_expire_specials();

// calculate category path
  if (isset($_GET['cPath'])) {
    $cPath = $_GET['cPath'];
  } elseif (isset($_GET['products_id']) && !isset($_GET['manufacturers_id'])) {
    $cPath = osc_get_product_path($_GET['products_id']);
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

// include the breadcrumb class and start the breadcrumb trail
  require(DIR_WS_CLASSES . 'breadcrumb.php');
  $breadcrumb = new breadcrumb;

  $breadcrumb->add(HEADER_TITLE_TOP, HTTP_SERVER);
  $breadcrumb->add(HEADER_TITLE_CATALOG, osc_href_link());

// add category names or the manufacturer name to the breadcrumb trail
  if (isset($cPath_array)) {
    for ($i=0, $n=sizeof($cPath_array); $i<$n; $i++) {
      $categories_query = osc_db_query("select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int)$cPath_array[$i] . "' and language_id = '" . (int)$_SESSION['languages_id'] . "'");
      if (osc_db_num_rows($categories_query) > 0) {
        $categories = osc_db_fetch_array($categories_query);
        $breadcrumb->add($categories['categories_name'], osc_href_link(null, 'cPath=' . implode('_', array_slice($cPath_array, 0, ($i+1)))));
      } else {
        break;
      }
    }
  } elseif (isset($_GET['manufacturers_id'])) {
    $manufacturers_query = osc_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "'");
    if (osc_db_num_rows($manufacturers_query)) {
      $manufacturers = osc_db_fetch_array($manufacturers_query);
      $breadcrumb->add($manufacturers['manufacturers_name'], osc_href_link(null, 'manufacturers_id=' . $_GET['manufacturers_id']));
    }
  }

// initialize the message stack for output messages
  require(DIR_WS_CLASSES . 'message_stack.php');
  $messageStack = new messageStack;

  require(DIR_WS_CLASSES . 'app.php');
  $OSCOM_APP = app::initialize();
  $OSCOM_APP->runActions();

  require(DIR_WS_CLASSES . 'osc_template.php');
  $oscTemplate = new oscTemplate();
?>
