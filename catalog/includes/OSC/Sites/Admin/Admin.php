<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Sites\Admin;

use OSC\OM\Apps;
use OSC\OM\Cache;
use OSC\OM\Db;
use OSC\OM\Hooks;
use OSC\OM\OSCOM;
use OSC\OM\Registry;

class Admin extends \OSC\OM\SitesAbstract
{
    public $default_page = 'Dashboard';

    protected function init()
    {
        global $request_type, $cookie_domain, $cookie_path, $PHP_SELF, $login_request, $messageStack, $cfgModules;

        Registry::set('Cache', new Cache());

        $OSCOM_Db = Db::initialize();
        Registry::set('Db', $OSCOM_Db);

// TODO legacy
        tep_db_connect() or die('Unable to connect to database server!');

// set the application parameters
        $Qcfg = $OSCOM_Db->get('configuration', [
            'configuration_key as k',
            'configuration_value as v'
        ]);//, null, null, null, 'configuration'); // TODO add cache when supported by admin

        while ($Qcfg->fetch()) {
            define($Qcfg->value('k'), $Qcfg->value('v'));
        }

// Used in the "Backup Manager" to compress backups
        define('LOCAL_EXE_GZIP', 'gzip');
        define('LOCAL_EXE_GUNZIP', 'gunzip');
        define('LOCAL_EXE_ZIP', 'zip');
        define('LOCAL_EXE_UNZIP', 'unzip');

// Define how do we update currency exchange rates
// Possible values are 'oanda' 'xe' or ''
        define('CURRENCY_SERVER_PRIMARY', 'oanda');
        define('CURRENCY_SERVER_BACKUP', 'xe');

// set the type of request (secure or not)
        if ((isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on')) || (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == 443))) {
            $request_type =  'SSL';

            $cookie_domain = HTTPS_COOKIE_DOMAIN;
            $cookie_path = HTTPS_COOKIE_PATH;
        } else {
            $request_type =  'NONSSL';

            $cookie_domain = HTTP_COOKIE_DOMAIN;
            $cookie_path = HTTP_COOKIE_PATH;
        }

// set php_self in the global scope
        $req = parse_url($_SERVER['SCRIPT_NAME']);
        $PHP_SELF = substr($req['path'], ($request_type == 'SSL') ? strlen(DIR_WS_HTTPS_ADMIN) : strlen(DIR_WS_ADMIN));

// set the session name and save path
        tep_session_name('oscomadminid');
        tep_session_save_path(SESSION_WRITE_DIRECTORY);

// set the session cookie parameters
// set the session cookie parameters
        session_set_cookie_params(0, $cookie_path, $cookie_domain);

        if (function_exists('ini_set')) {
            ini_set('session.use_only_cookies', (SESSION_FORCE_COOKIE_USE == 'True') ? 1 : 0);
        }

// lets start our session
        tep_session_start();

// TODO remove when no more global sessions exist
        foreach ($_SESSION as $k => $v) {
            $GLOBALS[$k] =& $_SESSION[$k];
        }

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

// redirect to login page if administrator is not yet logged in
        if (!isset($_SESSION['admin'])) {
            $redirect = false;

            $current_page = $PHP_SELF;

// if the first page request is to the login page, set the current page to the index page
// so the redirection on a successful login is not made to the login page again
            if (($current_page == FILENAME_LOGIN) && !isset($_SESSION['redirect_origin'])) {
                $current_page = FILENAME_DEFAULT;
            }

            if ($current_page != FILENAME_LOGIN) {
                if (!isset($_SESSION['redirect_origin'])) {
                    $_SESSION['redirect_origin'] = [
                        'page' => $current_page,
                        'get' => []
                    ];
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
                tep_redirect(tep_href_link(FILENAME_LOGIN, (isset($_SESSION['redirect_origin']['auth_user']) ? 'action=process' : '')));
            }
        }

// include the language translations
        $_system_locale_numeric = setlocale(LC_NUMERIC, 0);
        require(DIR_FS_ADMIN . 'includes/languages/' . $_SESSION['language'] . '.php');
        setlocale(LC_NUMERIC, $_system_locale_numeric); // Prevent LC_ALL from setting LC_NUMERIC to a locale with 1,0 float/decimal values instead of 1.0 (see bug #634)

        $current_page = basename($PHP_SELF);
        if (file_exists(DIR_FS_ADMIN . 'includes/languages/' . $_SESSION['language'] . '/' . $current_page)) {
            include(DIR_FS_ADMIN . 'includes/languages/' . $_SESSION['language'] . '/' . $current_page);
        }

        $messageStack = new \messageStack();

        $cfgModules = new \cfg_modules();

        Registry::set('Hooks', new Hooks());
    }

    public function setPage()
    {
        $page_code = $this->default_page;

        $class = 'OSC\Sites\\' . $this->code . '\Pages\\' . $page_code . '\\' . $page_code;

        if (!empty($_GET)) {
            $req = basename(array_keys($_GET)[0]);

            if (($req == 'A') && (count($_GET) > 1)) {
                $app = basename(array_keys($_GET)[1]);

                if (Apps::exists($app) && ($page = Apps::getRouteDestination(null, $app)) !== null) {
// get controller class name from namespace
                    $page_namespace = explode('\\', $page);
                    $page_code = $page_namespace[count($page_namespace)-1];

                    if (class_exists('OSC\Apps\\' . $app . '\\' . $page . '\\' . $page_code)) {
                        $this->app = $app;
                        $this->route = $app . '\\' . $page;
                        $this->actions_index = 2;

                        $class = 'OSC\Apps\\' . $this->app . '\\' . $page . '\\' . $page_code;
                    }
                }
            } else {
                if (class_exists('OSC\Sites\\' . $this->code . '\Pages\\' . $req . '\\' . $req)) {
                    $page_code = $req;

                    $class = 'OSC\Sites\\' . $this->code . '\Pages\\' . $page_code . '\\' . $page_code;
                }
            }
        }

        if (is_subclass_of($class, 'OSC\OM\PagesInterface')) {
            $this->page = new $class($this);

            $this->page->runActions();
        } else {
            trigger_error('OSC\Sites\Admin\Admin::setPage() - ' . $page_code . ': Page does not implement OSC\OM\PagesInterface and cannot be loaded.');
        }
    }

    public static function resolveRoute(array $route, array $routes)
    {
        return array_values($routes)[0];
    }
}
