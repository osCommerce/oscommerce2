<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

// start the timer for the page parse time log
  define('PAGE_PARSE_START_TIME', microtime());

// set the level of error reporting
  error_reporting(E_ALL | E_STRICT);
  ini_set('display_errors', true); // TODO remove on release

// load server configuration parameters
  if (file_exists('includes/local/configure.php')) { // for developers
    include('includes/local/configure.php');
  } else {
    include('includes/configure.php');
  }

  if (DB_SERVER == '') {
    if (is_dir('install')) {
      header('Location: install/index.php');
      exit;
    }
  }

// set default timezone if none exists (PHP 5.3 throws an E_WARNING)
  date_default_timezone_set(defined('CFG_TIME_ZONE') ? CFG_TIME_ZONE : date_default_timezone_get());

// set the type of request (secure or not)
  if ( (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on')) || (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == 443)) ) {
    $request_type =  'SSL';
    define('DIR_WS_CATALOG', DIR_WS_HTTPS_CATALOG);
// set the cookie domain
    $cookie_domain = HTTPS_COOKIE_DOMAIN;
    $cookie_path = HTTPS_COOKIE_PATH;
  } else {
    $request_type =  'NONSSL';
    define('DIR_WS_CATALOG', DIR_WS_HTTP_CATALOG);
    $cookie_domain = HTTP_COOKIE_DOMAIN;
    $cookie_path = HTTP_COOKIE_PATH;
  }
  
// set php_self in the local scope
  $req = parse_url($_SERVER['SCRIPT_NAME']);
  $PHP_SELF = substr($req['path'], ($request_type == 'NONSSL') ? strlen(DIR_WS_HTTP_CATALOG) : strlen(DIR_WS_HTTPS_CATALOG));

// include the list of project filenames
  require('includes/filenames.php');

// include the list of project database tables
  require('includes/database_tables.php');

// include the database functions
  require('includes/functions/database.php');

// make a connection to the database... now
  tep_db_connect() or die('Unable to connect to database server!');

// set the application parameters
  $configuration_query = tep_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_CONFIGURATION);
  while ($configuration = tep_db_fetch_array($configuration_query)) {
    define($configuration['cfgKey'], $configuration['cfgValue']);
  }

// if gzip_compression is enabled, start to buffer the output
  if ( (GZIP_COMPRESSION == 'true') && extension_loaded('zlib') && !headers_sent() ) {
    if ( (int)ini_get('zlib.output_compression') < 1 ) {
      if ( (PHP_VERSION < '5.4') || (PHP_VERSION > '5.4.5') ) { // see PHP bug 55544
        ob_start('ob_gzhandler');
      }
    } elseif ( function_exists('ini_set') ) {
      ini_set('zlib.output_compression_level', GZIP_LEVEL);
    }
  }

// define general functions used application-wide
  require('includes/functions/general.php');
  require('includes/functions/html_output.php');

// include cache functions if enabled
  if ( USE_CACHE == 'true' ) include('includes/functions/cache.php');

// include shopping cart class
  require('includes/classes/shopping_cart.php');

// include navigation history class
  require('includes/classes/navigation_history.php');

// define how the session functions will be used
  require('includes/functions/sessions.php');

// set the session name and save path
  session_name('osCsid');
  session_save_path(SESSION_WRITE_DIRECTORY);

// set the session cookie parameters
  session_set_cookie_params(0, $cookie_path, $cookie_domain);

  if ( function_exists('ini_set') ) {
    ini_set('session.use_only_cookies', (SESSION_FORCE_COOKIE_USE == 'True') ? 1 : 0);
  }

// set the session ID if it exists
  if ( SESSION_FORCE_COOKIE_USE == 'False' ) {
    if ( isset($_GET[session_name()]) && (!isset($_COOKIE[session_name()]) || ($_COOKIE[session_name()] != $_GET[session_name()])) ) {
      session_id($_GET[session_name()]);
    } elseif ( isset($_POST[session_name()]) && (!isset($_COOKIE[session_name()]) || ($_COOKIE[session_name()] != $_POST[session_name()])) ) {
      session_id($_POST[session_name()]);
    }
  }

// start the session
  $session_started = false;

  if ( SESSION_FORCE_COOKIE_USE == 'True' ) {
    tep_setcookie('cookie_test', 'please_accept_for_session', time()+60*60*24*30);

    if ( isset($_COOKIE['cookie_test']) ) {
      tep_session_start();
      $session_started = true;
    }
  } elseif ( SESSION_BLOCK_SPIDERS == 'True' ) {
    
    $user_agent = '';
    
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
      $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    }

    $spider_flag = false;

    if ( !empty($user_agent) ) {
      foreach ( file('includes/spiders.txt') as $spider ) {
        if ( !empty($spider) ) {
          if ( strpos($user_agent, $spider) !== false ) {
            $spider_flag = true;
            break;
          }
        }
      }
    }

    if ( $spider_flag === false ) {
      tep_session_start();
      $session_started = true;
    }
  } else {
    tep_session_start();
    $session_started = true;
  }

// initialize a session token
  if ( !isset($_SESSION['sessiontoken']) ) {
    $_SESSION['sessiontoken'] = md5(tep_rand() . tep_rand() . tep_rand() . tep_rand());
  }

// set SID once, even if empty
  $SID = (defined('SID') ? SID : '');

// verify the ssl_session_id if the feature is enabled
  if ( ($request_type == 'SSL') && (SESSION_CHECK_SSL_SESSION_ID == 'True') && (ENABLE_SSL == true) && ($session_started === true) ) {
    if ( !isset($_SESSION['SSL_SESSION_ID']) ) {
      $_SESSION['SESSION_SSL_ID'] = $_SERVER['SSL_SESSION_ID'];
    }

    if ( $_SESSION['SESSION_SSL_ID'] != $_SERVER['SSL_SESSION_ID'] ) {
      tep_session_destroy();

      tep_redirect(tep_href_link(FILENAME_SSL_CHECK));
    }
  }

// verify the browser user agent if the feature is enabled
  if ( SESSION_CHECK_USER_AGENT == 'True' ) {
    if ( !isset($_SESSION['SESSION_USER_AGENT']) ) {
      $_SESSION['SESSION_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
    }

    if ( $_SESSION['SESSION_USER_AGENT'] != $_SERVER['HTTP_USER_AGENT'] ) {
      tep_session_destroy();
      tep_redirect(tep_href_link(FILENAME_LOGIN));
    }
  }

// verify the IP address if the feature is enabled
  if ( SESSION_CHECK_IP_ADDRESS == 'True' ) {
    if ( !isset($_SESSION['SESSION_IP_ADDRESS']) ) {
      $_SESSION['SESSION_IP_ADDRESS'] = tep_get_ip_address();
    }

    if ( $_SESSION['SESSION_IP_ADDRESS'] != tep_get_ip_address() ) {
      tep_session_destroy();
      tep_redirect(tep_href_link(FILENAME_LOGIN));
    }
  }

// create the shopping cart
  if ( !isset($_SESSION['cart']) || !is_object($_SESSION['cart']) || (get_class($_SESSION['cart']) != 'shoppingCart') ) {
    $_SESSION['cart'] = new shoppingCart();
  }

// include currencies class and create an instance
  require('includes/classes/currencies.php');
  $currencies = new currencies();

// include the mail classes
  require('includes/classes/mime.php');
  require('includes/classes/email.php');

// set the language
  if ( !isset($_SESSION['language']) || isset($_GET['language']) ) {
    include('includes/classes/language.php');
    $lng = new language();

    if ( isset($_GET['language']) && !empty($_GET['language']) ) {
      $lng->set_language($_GET['language']);
    } else {
      $lng->get_browser_language();
    }

    $_SESSION['language'] = $lng->language['directory'];
    $_SESSION['languages_id'] = $lng->language['id'];
  }

// include the language translations
  $_system_locale_numeric = setlocale(LC_NUMERIC, 0);
  require('includes/languages/' . basename($_SESSION['language']) . '.php');
  setlocale(LC_NUMERIC, $_system_locale_numeric); // Prevent LC_ALL from setting LC_NUMERIC to a locale with 1,0 float/decimal values instead of 1.0 (see bug #634)

// currency
  if ( !isset($_SESSION['currency']) || isset($_GET['currency']) || ((USE_DEFAULT_LANGUAGE_CURRENCY == 'true') && (LANGUAGE_CURRENCY != $_SESSION['currency'])) ) {
    if ( isset($_GET['currency']) && $currencies->is_set($_GET['currency']) ) {
      $_SESSION['currency'] = $_GET['currency'];
    } else {
      $_SESSION['currency'] = ((USE_DEFAULT_LANGUAGE_CURRENCY == 'true') && $currencies->is_set(LANGUAGE_CURRENCY)) ? LANGUAGE_CURRENCY : DEFAULT_CURRENCY;
    }
  }

// navigation history
  if ( !isset($_SESSION['navigation']) || !is_object($_SESSION['navigation']) || (get_class($_SESSION['navigation']) != 'navigationHistory') ) {
    $_SESSION['navigation'] = new navigationHistory();
  }

  $_SESSION['navigation']->add_current_page();

// action recorder
  require('includes/classes/action_recorder.php');
// initialize the message stack for output messages
  require('includes/classes/alertbox.php');
  require('includes/classes/message_stack.php');
  $messageStack = new messageStack();

// Shopping cart actions
  if ( isset($_GET['action']) ) {
// redirect the customer to a friendly cookie-must-be-enabled page if cookies are disabled
    if ( $session_started == false ) {
      tep_redirect(tep_href_link(FILENAME_COOKIE_USAGE));
    }

    if ( DISPLAY_CART == 'true' ) {
      $goto =  FILENAME_SHOPPING_CART;
      $parameters = array('action', 'cPath', 'products_id', 'pid');
    } else {
      $goto = $PHP_SELF;

      if ( $_GET['action'] == 'buy_now') {
        $parameters = array('action', 'pid', 'products_id');
      } else {
        $parameters = array('action', 'pid');
      }
    }

    switch ( $_GET['action'] ) {
      // customer wants to update the product quantity in their shopping cart
      case 'update_product' : for ($i=0, $n=sizeof($_POST['products_id']); $i<$n; $i++) {
                                if (in_array($_POST['products_id'][$i], (is_array($_POST['cart_delete']) ? $_POST['cart_delete'] : array()))) {
                                  $_SESSION['cart']->remove($_POST['products_id'][$i]);
                                  $messageStack->add_session('product_action', sprintf(PRODUCT_REMOVED, tep_get_products_name($_POST['products_id'][$i])), 'warning');
                                } else {
                                  $attributes = ($_POST['id'][$_POST['products_id'][$i]]) ? $_POST['id'][$_POST['products_id'][$i]] : '';
                                  $_SESSION['cart']->add_cart($_POST['products_id'][$i], $_POST['cart_quantity'][$i], $attributes, false);
                                  $messageStack->add_session('product_action', sprintf(PRODUCT_ADDED, tep_get_products_name((int)$_POST['products_id'][$i])), 'success');
                                }
                              }
                              tep_redirect(tep_href_link($goto, tep_get_all_get_params($parameters)));
                              break;
      // customer adds a product from the products page
      case 'add_product' :    if (isset($_POST['products_id']) && is_numeric($_POST['products_id'])) {
                                $attributes = isset($_POST['id']) ? $_POST['id'] : '';
                                $_SESSION['cart']->add_cart($_POST['products_id'], $_SESSION['cart']->get_quantity(tep_get_uprid($_POST['products_id'], $attributes))+1, $attributes);
                                $messageStack->add_session('product_action', sprintf(PRODUCT_ADDED, tep_get_products_name((int)$_POST['products_id'])), 'success');
                              }
                              tep_redirect(tep_href_link($goto, tep_get_all_get_params($parameters)));
                              break;
      // customer removes a product from their shopping cart
      case 'remove_product' : if (isset($_GET['products_id'])) {
                                $_SESSION['cart']->remove($_GET['products_id']);
                                $messageStack->add_session('product_action', sprintf(PRODUCT_REMOVED, tep_get_products_name($_GET['products_id'])), 'warning');
                              }
                              tep_redirect(tep_href_link($goto, tep_get_all_get_params($parameters)));
                              break;
      // performed by the 'buy now' button in product listings and review page
      case 'buy_now' :        if (isset($_GET['products_id'])) {
                                if (tep_has_product_attributes($_GET['products_id'])) {
                                  tep_redirect(tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $_GET['products_id']));
                                } else {
                                  $_SESSION['cart']->add_cart($_GET['products_id'], $_SESSION['cart']->get_quantity($_GET['products_id'])+1);
                                  $messageStack->add_session('product_action', sprintf(PRODUCT_ADDED, tep_get_products_name((int)$_GET['products_id'])), 'success');
                                }
                              }
                              tep_redirect(tep_href_link($goto, tep_get_all_get_params($parameters)));
                              break;
      case 'notify' :         if ( isset($_SESSION['customer_id']) ) {
                                if (isset($_GET['products_id'])) {
                                  $notify = $_GET['products_id'];
                                } elseif (isset($_GET['notify'])) {
                                  $notify = $_GET['notify'];
                                } elseif (isset($_POST['notify'])) {
                                  $notify = $_POST['notify'];
                                } else {
                                  tep_redirect(tep_href_link($PHP_SELF, tep_get_all_get_params(array('action', 'notify'))));
                                }
                                if (!is_array($notify)) $notify = array($notify);
                                for ($i=0, $n=sizeof($notify); $i<$n; $i++) {
                                  $check_query = tep_db_query("select count(*) as count from " . TABLE_PRODUCTS_NOTIFICATIONS . " where products_id = '" . (int)$notify[$i] . "' and customers_id = '" . (int)$_SESSION['customer_id'] . "'");
                                  $check = tep_db_fetch_array($check_query);
                                  if ($check['count'] < 1) {
                                    tep_db_query("insert into " . TABLE_PRODUCTS_NOTIFICATIONS . " (products_id, customers_id, date_added) values ('" . (int)$notify[$i] . "', '" . (int)$_SESSION['customer_id'] . "', now())");
                                    $messageStack->add_session('product_action', sprintf(PRODUCT_SUBSCRIBED, tep_get_products_name((int)$notify[$i])), 'success');
                                  }
                                }
                                tep_redirect(tep_href_link($PHP_SELF, tep_get_all_get_params(array('action', 'notify'))));
                              } else {
                                $_SESSION['navigation']->set_snapshot();
                                tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
                              }
                              break;
      case 'notify_remove' :  if ( isset($_SESSION['customer_id']) && isset($_GET['products_id'])) {
                                $check_query = tep_db_query("select count(*) as count from " . TABLE_PRODUCTS_NOTIFICATIONS . " where products_id = '" . (int)$_GET['products_id'] . "' and customers_id = '" . (int)$_SESSION['customer_id'] . "'");
                                $check = tep_db_fetch_array($check_query);
                                if ($check['count'] > 0) {
                                  tep_db_query("delete from " . TABLE_PRODUCTS_NOTIFICATIONS . " where products_id = '" . (int)$_GET['products_id'] . "' and customers_id = '" . (int)$_SESSION['customer_id'] . "'");
                                  $messageStack->add_session('product_action', sprintf(PRODUCT_UNSUBSCRIBED, tep_get_products_name((int)$_GET['products_id'])), 'warning');
                                }
                                tep_redirect(tep_href_link($PHP_SELF, tep_get_all_get_params(array('action'))));
                              } else {
                                $_SESSION['navigation']->set_snapshot();
                                tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
                              }
                              break;
      case 'cust_order' :     if ( isset($_SESSION['customer_id']) && isset($_GET['pid']) ) {
                                if (tep_has_product_attributes($_GET['pid'])) {
                                  tep_redirect(tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $_GET['pid']));
                                } else {
                                  $_SESSION['cart']->add_cart($_GET['pid'], $_SESSION['cart']->get_quantity($_GET['pid'])+1);
                                }
                              }
                              tep_redirect(tep_href_link($goto, tep_get_all_get_params($parameters)));
                              break;
    }
  }

// include the who's online functions
  require('includes/functions/whos_online.php');
  tep_update_whos_online();

// include the password crypto functions
  require('includes/functions/password_funcs.php');

// include validation functions (right now only email address)
  require('includes/functions/validations.php');

// split-page-results
  require('includes/classes/split_page_results.php');

// infobox
  require('includes/classes/boxes.php');

// auto activate and expire banners
  require('includes/functions/banner.php');
  tep_activate_banners();
  tep_expire_banners();

// auto expire special products
  require('includes/functions/specials.php');
  tep_expire_specials();

  require('includes/classes/osc_template.php');
  $oscTemplate = new oscTemplate();

// calculate category path
  if ( isset($_GET['cPath']) ) {
    $cPath = $_GET['cPath'];
  } elseif ( isset($_GET['products_id']) && !isset($_GET['manufacturers_id']) ) {
    $cPath = tep_get_product_path($_GET['products_id']);
  } else {
    $cPath = '';
  }

  if ( !empty($cPath) ) {
    $cPath_array = tep_parse_category_path($cPath);
    $cPath = implode('_', $cPath_array);
    $current_category_id = $cPath_array[(sizeof($cPath_array)-1)];
  } else {
    $current_category_id = 0;
  }

// include the breadcrumb class and start the breadcrumb trail
  require('includes/classes/breadcrumb.php');
  $breadcrumb = new breadcrumb;

  $breadcrumb->add(HEADER_TITLE_TOP, HTTP_SERVER);
  $breadcrumb->add(HEADER_TITLE_CATALOG, tep_href_link(FILENAME_DEFAULT));

// add category names or the manufacturer name to the breadcrumb trail
  if ( isset($cPath_array) ) {
    for ( $i=0, $n=sizeof($cPath_array); $i<$n; $i++ ) {
      $categories_query = tep_db_query("select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int)$cPath_array[$i] . "' and language_id = '" . (int)$_SESSION['languages_id'] . "'");

      if ( tep_db_num_rows($categories_query) > 0 ) {
        $categories = tep_db_fetch_array($categories_query);

        $breadcrumb->add($categories['categories_name'], tep_href_link(FILENAME_DEFAULT, 'cPath=' . implode('_', array_slice($cPath_array, 0, ($i+1)))));
      } else {
        break;
      }
    }
  } elseif ( isset($_GET['manufacturers_id']) ) {
    $manufacturers_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "'");

    if ( tep_db_num_rows($manufacturers_query) ) {
      $manufacturers = tep_db_fetch_array($manufacturers_query);

      $breadcrumb->add($manufacturers['manufacturers_name'], tep_href_link(FILENAME_DEFAULT, 'manufacturers_id=' . $_GET['manufacturers_id']));
    }
  }

// TODO remove when no more global sessions exist
  if ( $session_started == true ) {
    extract($_SESSION, EXTR_OVERWRITE+EXTR_REFS);
  }
