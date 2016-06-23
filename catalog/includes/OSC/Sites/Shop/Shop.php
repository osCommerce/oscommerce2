<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Sites\Shop;

use OSC\OM\Apps;
use OSC\OM\Cache;
use OSC\OM\Db;
use OSC\OM\Hooks;
use OSC\OM\HTML;
use OSC\OM\OSCOM;
use OSC\OM\Registry;

class Shop extends \OSC\OM\SitesAbstract
{
    protected function init()
    {
        global $request_type, $cookie_domain, $cookie_path, $PHP_SELF, $SID, $currencies, $messageStack, $oscTemplate, $breadcrumb;

        Registry::set('Cache', new Cache());

        $OSCOM_Db = Db::initialize();
        Registry::set('Db', $OSCOM_Db);

// set the application parameters
        $Qcfg = $OSCOM_Db->get('configuration', [
            'configuration_key as k',
            'configuration_value as v'
        ]);//, null, null, null, 'configuration'); // TODO add cache when supported by admin

        while ($Qcfg->fetch()) {
            define($Qcfg->value('k'), $Qcfg->value('v'));
        }

// set the type of request (secure or not)
        if ((isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on')) || (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == 443))) {
            $request_type =  'SSL';
            define('DIR_WS_CATALOG', DIR_WS_HTTPS_CATALOG);

            $cookie_domain = HTTPS_COOKIE_DOMAIN;
            $cookie_path = HTTPS_COOKIE_PATH;
        } else {
            $request_type =  'NONSSL';
            define('DIR_WS_CATALOG', DIR_WS_HTTP_CATALOG);

            $cookie_domain = HTTP_COOKIE_DOMAIN;
            $cookie_path = HTTP_COOKIE_PATH;
        }

// set php_self in the global scope
        $req = parse_url($_SERVER['SCRIPT_NAME']);
        $PHP_SELF = substr($req['path'], ($request_type == 'NONSSL') ? strlen(DIR_WS_HTTP_CATALOG) : strlen(DIR_WS_HTTPS_CATALOG));

// set the session name and save path
        session_name('oscomid');
        session_save_path(SESSION_WRITE_DIRECTORY);

// set the session cookie parameters
        session_set_cookie_params(0, $cookie_path, $cookie_domain);

        if (function_exists('ini_set')) {
            ini_set('session.use_only_cookies', (SESSION_FORCE_COOKIE_USE == 'True') ? 1 : 0);
        }

// set the session ID if it exists
        if (SESSION_FORCE_COOKIE_USE == 'False') {
            if (isset($_GET[session_name()]) && (!isset($_COOKIE[session_name()]) || ($_COOKIE[session_name()] != $_GET[session_name()]))) {
                session_id($_GET[session_name()]);
            } elseif (isset($_POST[session_name()]) && (!isset($_COOKIE[session_name()]) || ($_COOKIE[session_name()] != $_POST[session_name()]))) {
                session_id($_POST[session_name()]);
            }
        }

// start the session
        if (SESSION_FORCE_COOKIE_USE == 'True') {
            tep_setcookie('cookie_test', 'please_accept_for_session', time()+60*60*24*30);

            if (isset($_COOKIE['cookie_test'])) {
                tep_session_start();
            }
        } elseif (SESSION_BLOCK_SPIDERS == 'True') {
            $user_agent = '';

            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
            }

            $spider_flag = false;

            if (!empty($user_agent)) {
                foreach (file(OSCOM::BASE_DIR . 'spiders.txt') as $spider) {
                    if (!empty($spider)) {
                        if (strpos($user_agent, $spider) !== false) {
                            $spider_flag = true;
                            break;
                        }
                    }
                }
            }

            if ($spider_flag === false) {
                tep_session_start();
            }
        } else {
            tep_session_start();
        }

        $this->ignored_actions[] = session_name();

// initialize a session token
        if (!isset($_SESSION['sessiontoken'])) {
            $_SESSION['sessiontoken'] = md5(tep_rand() . tep_rand() . tep_rand() . tep_rand());
        }

// set SID once, even if empty
        $SID = (defined('SID') ? SID : '');

// verify the ssl_session_id if the feature is enabled
        if (($request_type == 'SSL') && (SESSION_CHECK_SSL_SESSION_ID == 'True') && (ENABLE_SSL == true) && (session_status() === PHP_SESSION_ACTIVE)) {
            if (!isset($_SESSION['SSL_SESSION_ID'])) {
                $_SESSION['SESSION_SSL_ID'] = $_SERVER['SSL_SESSION_ID'];
            }

            if ($_SESSION['SESSION_SSL_ID'] != $_SERVER['SSL_SESSION_ID']) {
                tep_session_destroy();

                OSCOM::redirect('ssl_check.php');
            }
        }

// verify the browser user agent if the feature is enabled
        if (SESSION_CHECK_USER_AGENT == 'True') {
            if (!isset($_SESSION['SESSION_USER_AGENT'])) {
                $_SESSION['SESSION_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
            }

            if ($_SESSION['SESSION_USER_AGENT'] != $_SERVER['HTTP_USER_AGENT']) {
                tep_session_destroy();

                OSCOM::redirect('index.php', 'Account&LogIn');
            }
        }

// verify the IP address if the feature is enabled
        if (SESSION_CHECK_IP_ADDRESS == 'True') {
            if (!isset($_SESSION['SESSION_IP_ADDRESS'])) {
                $_SESSION['SESSION_IP_ADDRESS'] = tep_get_ip_address();
            }

            if ($_SESSION['SESSION_IP_ADDRESS'] != tep_get_ip_address()) {
                tep_session_destroy();

                OSCOM::redirect('index.php', 'Account&LogIn');
            }
        }

// create the shopping cart
        if (!isset($_SESSION['cart']) || !is_object($_SESSION['cart']) || (get_class($_SESSION['cart']) != 'shoppingCart')) {
            $_SESSION['cart'] = new \shoppingCart();
        }

// include currencies class and create an instance
        $currencies = new \currencies();

// set the language
        if (!isset($_SESSION['language']) || isset($_GET['language'])) {
            $lng = new \language();

            if (isset($_GET['language']) && !empty($_GET['language'])) {
                $lng->set_language($_GET['language']);
            } else {
                $lng->get_browser_language();
            }

            $_SESSION['language'] = $lng->language['directory'];
            $_SESSION['languages_id'] = $lng->language['id'];
        }

// include the language translations
        $system_locale_numeric = setlocale(LC_NUMERIC, 0);
        include(OSCOM::BASE_DIR . 'languages/' . $_SESSION['language'] . '.php');
        setlocale(LC_NUMERIC, $system_locale_numeric); // Prevent LC_ALL from setting LC_NUMERIC to a locale with 1,0 float/decimal values instead of 1.0 (see bug #634)

// currency
        if (!isset($_SESSION['currency']) || isset($_GET['currency']) || ((USE_DEFAULT_LANGUAGE_CURRENCY == 'true') && (LANGUAGE_CURRENCY != $_SESSION['currency']))) {
            if (isset($_GET['currency']) && $currencies->is_set($_GET['currency'])) {
                $_SESSION['currency'] = $_GET['currency'];
            } else {
                $_SESSION['currency'] = ((USE_DEFAULT_LANGUAGE_CURRENCY == 'true') && $currencies->is_set(LANGUAGE_CURRENCY)) ? LANGUAGE_CURRENCY : DEFAULT_CURRENCY;
            }
        }

// navigation history
        if (!isset($_SESSION['navigation']) || !is_object($_SESSION['navigation']) || (get_class($_SESSION['navigation']) != 'navigationHistory')) {
            $_SESSION['navigation'] = new \navigationHistory();
        }

        $_SESSION['navigation']->add_current_page();

        $messageStack = new \messageStack();

        tep_update_whos_online();

        tep_activate_banners();
        tep_expire_banners();

        tep_expire_specials();

        $oscTemplate = new \oscTemplate();

        $breadcrumb = new \breadcrumb();

        $breadcrumb->add(HEADER_TITLE_TOP, HTTP_SERVER);
        $breadcrumb->add(HEADER_TITLE_CATALOG, OSCOM::link('index.php'));

        Registry::set('Hooks', new Hooks());
    }

    public function setPage()
    {
        if (!empty($_GET)) {
            if (($route = Apps::getRouteDestination()) !== null) {
                $this->route = $route;

                list($vendor_app, $page) = explode('/', $route['destination'], 2);

// get controller class name from namespace
                $page_namespace = explode('\\', $page);
                $page_code = $page_namespace[count($page_namespace)-1];

                if (class_exists('OSC\Apps\\' . $vendor_app . '\\' . $page . '\\' . $page_code)) {
                    $class = 'OSC\Apps\\' . $vendor_app . '\\' . $page . '\\' . $page_code;
                }
            } else {
                $req = basename(array_keys($_GET)[0]);

                if (class_exists('OSC\Sites\\' . $this->code . '\Pages\\' . $req . '\\' . $req)) {
                    $page_code = $req;

                    $class = 'OSC\Sites\\' . $this->code . '\Pages\\' . $page_code . '\\' . $page_code;
                }
            }
        }

        if (isset($class)) {
            if (is_subclass_of($class, 'OSC\OM\PagesInterface')) {
                $this->page = new $class($this);

                $this->page->runActions();
            } else {
                trigger_error('OSC\Sites\Shop\Shop::setPage() - ' . $page_code . ': Page does not implement OSC\OM\PagesInterface and cannot be loaded.');
            }
        }
    }

    public static function resolveRoute(array $route, array $routes)
    {
        $result = [];

        foreach ($routes as $vendor_app => $paths) {
            foreach ($paths as $path => $page) {
                $path_array = explode('&', $path);

                if (count($path_array) <= count($route)) {
                    if ($path_array == array_slice($route, 0, count($path_array))) {
                        $result[] = [
                            'path' => $path,
                            'destination' => $vendor_app . '/' . $page,
                            'score' => count($path_array)
                        ];
                    }
                }
            }
        }

        if (!empty($result)) {
            usort($result, function ($a, $b) {
                if ($a['score'] == $b['score']) {
                    return 0;
                }

                return ($a['score'] < $b['score']) ? 1 : -1; // sort highest to lowest
            });

            return $result[0];
        }
    }
}
