<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Sites\Admin;

use OSC\OM\Apps;
use OSC\OM\Cookies;
use OSC\OM\Db;
use OSC\OM\Hooks;
use OSC\OM\Language;
use OSC\OM\MessageStack;
use OSC\OM\OSCOM;
use OSC\OM\Registry;
use OSC\OM\Session;

class Admin extends \OSC\OM\SitesAbstract
{
    protected function init()
    {
        global $PHP_SELF, $login_request, $cfgModules, $oscTemplate;

        $OSCOM_Cookies = new Cookies();
        Registry::set('Cookies', $OSCOM_Cookies);

        $OSCOM_Db = Db::initialize();
        Registry::set('Db', $OSCOM_Db);

        Registry::set('Hooks', new Hooks());

        Registry::set('MessageStack', new MessageStack());

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

// set php_self in the global scope
        $req = parse_url($_SERVER['SCRIPT_NAME']);
        $PHP_SELF = substr($req['path'], strlen(OSCOM::getConfig('http_path')));

        $OSCOM_Session = Session::load();
        Registry::set('Session', $OSCOM_Session);

        $OSCOM_Session->start();

        $OSCOM_Language = new Language();
        Registry::set('Language', $OSCOM_Language);

// set the language
        if (!isset($_SESSION['language']) || isset($_GET['language'])) {
            if (isset($_GET['language']) && !empty($_GET['language']) && $OSCOM_Language->exists($_GET['language'])) {
                $OSCOM_Language->set($_GET['language']);
            }

            $_SESSION['language'] = $OSCOM_Language->get('code');
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
                OSCOM::redirect(FILENAME_LOGIN, (isset($_SESSION['redirect_origin']['auth_user']) ? 'action=process' : ''));
            }
        }

// include the language translations
        $OSCOM_Language->loadDefinitions('main');

// Prevent LC_ALL from setting LC_NUMERIC to a locale with 1,0 float/decimal values instead of 1.0 (see bug #634)
        $system_locale_numeric = setlocale(LC_NUMERIC, 0);
        setlocale(LC_ALL, explode(';', OSCOM::getDef('system_locale')));
        setlocale(LC_NUMERIC, $system_locale_numeric);

        $current_page = basename($PHP_SELF);

        if ($OSCOM_Language->definitionsExist(pathinfo($current_page, PATHINFO_FILENAME))) {
            $OSCOM_Language->loadDefinitions(pathinfo($current_page, PATHINFO_FILENAME));
        }

        $oscTemplate = new \oscTemplate();

        $cfgModules = new \cfg_modules();
    }

    public function setPage()
    {
        if (!empty($_GET)) {
            $req = basename(array_keys($_GET)[0]);

            if (($req == 'A') && (count($_GET) > 1)) {
                $app = array_keys($_GET)[1];

                if (strpos($app, '\\') !== false) {
                    list($vendor, $app) = explode('\\', $app);

                    if (Apps::exists($vendor . '\\' . $app) && ($page = Apps::getRouteDestination(null, $vendor . '\\' . $app)) !== null) {
// get controller class name from namespace
                        $page_namespace = explode('\\', $page);
                        $page_code = $page_namespace[count($page_namespace)-1];

                        if (class_exists('OSC\Apps\\' . $vendor . '\\' . $app . '\\' . $page . '\\' . $page_code)) {
                            $this->app = $vendor . '\\' . $app;
                            $this->route = $this->app . '\\' . $page;
                            $this->actions_index = 2;

                            $class = 'OSC\Apps\\' . $this->app . '\\' . $page . '\\' . $page_code;
                        }
                    }
                }
            } else {
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
                trigger_error('OSC\Sites\Admin\Admin::setPage() - ' . $page_code . ': Page does not implement OSC\OM\PagesInterface and cannot be loaded.');
            }
        }
    }

    public static function resolveRoute(array $route, array $routes)
    {
        return array_values($routes)[0];
    }
}
