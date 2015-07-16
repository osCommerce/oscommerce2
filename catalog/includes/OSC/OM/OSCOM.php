<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

use OSC\OM\DateTime;
use OSC\OM\HTML;
use OSC\OM\HTTP;
use OSC\OM\Registry;

class OSCOM
{
    const BASE_DIR = OSCOM_BASE_DIR;

    protected static $version;
    protected static $site = 'Shop';

    public static function initialize($site = null)
    {
        DateTime::setTimeZone();

        static::setSite($site);
    }

    public static function getVersion()
    {
        if (!isset(static::$version)) {
            $file = static::BASE_DIR . 'version.php';

            $v = trim(file_get_contents($file));

            if (preg_match('/^(\d+\.)?(\d+\.)?(\d+)$/', $v)) {
                static::$version = $v;
            } else {
                trigger_error('Version number is not numeric. Please verify: ' . $file);
            }
        }

        return static::$version;
    }

    public static function setSite($site)
    {
        if (!empty($site)) {
            static::$site = $site;
        }

        $class = 'OSC\Sites\\' . static::$site . '\\' . static::$site;

        if (is_subclass_of($class, 'OSC\OM\SitesInterface')) {
            $OSCOM_Site = new $class();
            Registry::set('Site', $OSCOM_Site);

            $OSCOM_Site->setPage();
        } else {
            trigger_error('OSC\OM\OSCOM::setSite() - ' . $site . ': Site does not implement OSC\OM\SitesInterface and cannot be loaded.');
            exit;
        }
    }

    public static function getSite()
    {
        return static::$site;
    }

    public static function getSitePageFile()
    {
        return Registry::get('Site')->getPage()->getFile();
    }

    public static function isRPC()
    {
        return Registry::get('Site')->getPage()->isRPC();
    }

    public static function link($page, $parameters = null, $connection = 'NONSSL', $add_session_id = true, $search_engine_safe = true)
    {
        global $request_type;

        $page = HTML::sanitize($page);

        if (!in_array($connection, ['NONSSL', 'SSL', 'AUTO'])) {
            $connection = 'NONSSL';
        }

        if (!is_bool($add_session_id)) {
            $add_session_id = true;
        }

        if (!is_bool($search_engine_safe)) {
            $search_engine_safe = true;
        }

        if ($connection == 'AUTO') {
            $connection = ($request_type == 'SSL') ? 'SSL' : 'NONSSL';
        }

        if (($connection == 'SSL') && (ENABLE_SSL !== true)) {
            $connection = 'NONSSL';
        }

        $site = static::$site;

        if (strncmp($page, 'Admin/', 6) === 0) {
            $page = substr($page, 6);

            $site = 'Admin';
        } elseif (strncmp($page, 'Shop/', 5) === 0) {
            $page = substr($page, 5);

            $site = 'Shop';
        }

        if ($site == 'Admin') {
            if ($connection == 'NONSSL') {
                $link = HTTP_SERVER . (defined('DIR_WS_ADMIN') ? DIR_WS_ADMIN : DIR_WS_HTTP_CATALOG . 'admin/');
            } else {
                $link = HTTPS_SERVER . (defined('DIR_WS_HTTPS_ADMIN') ? DIR_WS_HTTPS_ADMIN : DIR_WS_HTTPS_CATALOG . 'admin/');
            }
        } else {
            if ($connection == 'NONSSL') {
                $link = HTTP_SERVER . (defined('DIR_WS_HTTP_CATALOG') ? DIR_WS_HTTP_CATALOG : DIR_WS_CATALOG);
            } else {
                $link = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG;
            }
        }

        $link .= $page;

        if (!empty($parameters)) {
            $link .= '?' . HTML::sanitize($parameters);
            $separator = '&';
        } else {
            $separator = '?';
        }

        while ((substr($link, -1) == '&') || (substr($link, -1) == '?')) {
            $link = substr($link, 0, -1);
        }

// Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
        if (($add_session_id == true) && (session_status() === PHP_SESSION_ACTIVE) && (SESSION_FORCE_COOKIE_USE == 'False')) {
            if (defined('SID') && !empty(SID)) {
                $_sid = SID;
            } elseif ((($request_type == 'NONSSL') && ($connection == 'SSL')) || (($request_type == 'SSL') && ($connection == 'NONSSL'))) {
                if (HTTP_COOKIE_DOMAIN != HTTPS_COOKIE_DOMAIN) {
                    $_sid = session_name() . '=' . session_id();
                }
            }
        }

        if (isset($_sid)) {
            $link .= $separator . HTML::sanitize($_sid);
        }

        while (strpos($link, '&&') !== false) {
            $link = str_replace('&&', '&', $link);
        }

        if ((SEARCH_ENGINE_FRIENDLY_URLS == 'true') && ($search_engine_safe == true)) {
            $link = str_replace(['?', '&', '='], '/', $link);
        }

        return $link;
    }

    public static function redirect()
    {
        global $request_type;

        $url = forward_static_call_array('static::link', func_get_args());

        if ((strstr($url, "\n") !== false) || (strstr($url, "\r") !== false)) {
            $url = static::link('index.php', '', 'NONSSL', false);
        }

        if ((ENABLE_SSL == true) && ($request_type == 'SSL')) { // We are loading an SSL page
            if (substr($url, 0, strlen(HTTP_SERVER . DIR_WS_HTTP_CATALOG)) == HTTP_SERVER . DIR_WS_HTTP_CATALOG) { // NONSSL url
                $url = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . substr($url, strlen(HTTP_SERVER . DIR_WS_HTTP_CATALOG)); // Change it to SSL
            }
        }

        HTTP::redirect($url);
    }

    public static function hasRoute(array $path)
    {
        return array_slice(array_keys($_GET), 0, count($path)) == $path;
    }

    public static function autoload($class)
    {
        $prefix = 'OSC\\';

        if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
            return false;
        }

        if (strncmp($prefix . 'OM\Apps\\', $class, strlen($prefix . 'OM\Apps\\')) === 0) {
          $file = OSCOM_BASE_DIR . str_replace(['OSC\OM\\', '\\'], ['', '/'], $class) . '.php';
          $custom = OSCOM_BASE_DIR . str_replace(['OSC\OM\\', '\\'], ['OSC\Custom\OM\\', '/'], $class) . '.php';
        } elseif (strncmp($prefix . 'OM\Module\\', $class, strlen($prefix . 'OM\Module\\')) === 0) {
          $file = OSCOM_BASE_DIR . str_replace(['OSC\OM\\', '\\'], ['', '/'], $class) . '.php';
          $custom = OSCOM_BASE_DIR . str_replace(['OSC\OM\\', '\\'], ['OSC\Custom\OM\\', '/'], $class) . '.php';
        } else {
          $file = OSCOM_BASE_DIR . str_replace('\\', '/', $class) . '.php';
          $custom = str_replace('OSC/OM/', 'OSC/Custom/OM/', $file);
        }

        if (file_exists($custom)) {
            require($custom);
        } elseif (file_exists($file)) {
            require($file);
        }
    }
}
