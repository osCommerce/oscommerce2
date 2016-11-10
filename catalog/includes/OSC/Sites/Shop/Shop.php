<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Sites\Shop;

use OSC\OM\Apps;
use OSC\OM\Cookies;
use OSC\OM\Db;
use OSC\OM\Hooks;
use OSC\OM\HTML;
use OSC\OM\Language;
use OSC\OM\OSCOM;
use OSC\OM\Registry;
use OSC\OM\Session;

class Shop extends \OSC\OM\SitesAbstract
{
    protected function init()
    {
        global $PHP_SELF, $currencies, $messageStack, $oscTemplate, $breadcrumb;

        $OSCOM_Cookies = new Cookies();
        Registry::set('Cookies', $OSCOM_Cookies);

        $OSCOM_Db = Db::initialize();
        Registry::set('Db', $OSCOM_Db);

        Registry::set('Hooks', new Hooks());

// set the application parameters
        $Qcfg = $OSCOM_Db->get('configuration', [
            'configuration_key as k',
            'configuration_value as v'
        ]);//, null, null, null, 'configuration'); // TODO add cache when supported by admin

        while ($Qcfg->fetch()) {
            define($Qcfg->value('k'), $Qcfg->value('v'));
        }

// set php_self in the global scope
        $req = parse_url($_SERVER['SCRIPT_NAME']);
        $PHP_SELF = substr($req['path'], strlen(OSCOM::getConfig('http_path', 'Shop')));

        $OSCOM_Session = Session::load();
        Registry::set('Session', $OSCOM_Session);

// start the session
        $OSCOM_Session->start();

        $this->ignored_actions[] = session_name();

        $OSCOM_Language = new Language();
//        $OSCOM_Language->setUseCache(true);
        Registry::set('Language', $OSCOM_Language);

// create the shopping cart
        if (!isset($_SESSION['cart']) || !is_object($_SESSION['cart']) || (get_class($_SESSION['cart']) != 'shoppingCart')) {
            $_SESSION['cart'] = new \shoppingCart();
        }

// include currencies class and create an instance
        $currencies = new \currencies();

// set the language
        if (!isset($_SESSION['language']) || isset($_GET['language'])) {
            if (isset($_GET['language']) && !empty($_GET['language']) && $OSCOM_Language->exists($_GET['language'])) {
                $OSCOM_Language->set($_GET['language']);
            }

            $_SESSION['language'] = $OSCOM_Language->get('code');
        }

// include the language translations
        $OSCOM_Language->loadDefinitions('main');

// Prevent LC_ALL from setting LC_NUMERIC to a locale with 1,0 float/decimal values instead of 1.0 (see bug #634)
        $system_locale_numeric = setlocale(LC_NUMERIC, 0);
        setlocale(LC_ALL, explode(';', OSCOM::getDef('system_locale')));
        setlocale(LC_NUMERIC, $system_locale_numeric);

// currency
        if (!isset($_SESSION['currency']) || isset($_GET['currency']) || ((USE_DEFAULT_LANGUAGE_CURRENCY == 'true') && (OSCOM::getDef('language_currency') != $_SESSION['currency']))) {
            if (isset($_GET['currency']) && $currencies->is_set($_GET['currency'])) {
                $_SESSION['currency'] = $_GET['currency'];
            } else {
                $_SESSION['currency'] = ((USE_DEFAULT_LANGUAGE_CURRENCY == 'true') && $currencies->is_set(OSCOM::getDef('language_currency'))) ? OSCOM::getDef('language_currency') : DEFAULT_CURRENCY;
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

        $breadcrumb->add(OSCOM::getDef('header_title_top'), OSCOM::getConfig('http_server', 'Shop'));
        $breadcrumb->add(OSCOM::getDef('header_title_catalog'), OSCOM::link('index.php'));
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
