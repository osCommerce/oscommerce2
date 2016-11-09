<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\OM;

use OSC\OM\DateTime;
use OSC\OM\ErrorHandler;
use OSC\OM\FileSystem;
use OSC\OM\HTML;
use OSC\OM\HTTP;
use OSC\OM\Registry;

class OSCOM
{
    const BASE_DIR = OSCOM_BASE_DIR;

    protected static $version;
    protected static $site = 'Shop';
    protected static $cfg = [];

    public static function initialize()
    {
        static::loadConfig();

        DateTime::setTimeZone();

        ErrorHandler::initialize();

        HTTP::setRequestType();
    }

    public static function getVersion()
    {
        if (!isset(static::$version)) {
            $file = static::BASE_DIR . 'version.txt';

            $v = trim(file_get_contents($file));

            if (preg_match('/^(\d+\.)?(\d+\.)?(\d+)$/', $v)) {
                static::$version = $v;
            } else {
                trigger_error('Version number is not numeric. Please verify: ' . $file);
            }
        }

        return static::$version;
    }

    public static function siteExists($site, $strict = true) {
        $class = 'OSC\Sites\\' . $site . '\\' . $site;

        if (class_exists($class)) {
            if (is_subclass_of($class, 'OSC\OM\SitesInterface')) {
                return true;
            } else {
                trigger_error('OSC\OM\OSCOM::siteExists() - ' . $site . ': Site does not implement OSC\OM\SitesInterface and cannot be loaded.');
            }
        } elseif ($strict === true) {
            trigger_error('OSC\OM\OSCOM::siteExists() - ' . $site . ': Site does not exist.');
        }

        return false;
    }

    public static function loadSite($site = null)
    {
        if (!isset($site)) {
            $site = static::$site;
        }

        static::setSite($site);
    }

    public static function setSite($site)
    {
        if (!static::siteExists($site)) {
            $site = static::$site;
        }

        static::$site = $site;

        $class = 'OSC\Sites\\' . $site . '\\' . $site;

        $OSCOM_Site = new $class();
        Registry::set('Site', $OSCOM_Site);

        $OSCOM_Site->setPage();
    }

    public static function getSite()
    {
        return static::$site;
    }

    public static function hasSitePage()
    {
        return Registry::get('Site')->hasPage();
    }

    public static function getSitePageFile()
    {
        return Registry::get('Site')->getPage()->getFile();
    }

    public static function useSiteTemplateWithPageFile()
    {
        return Registry::get('Site')->getPage()->useSiteTemplate();
    }

    public static function isRPC()
    {
        $OSCOM_Site = Registry::get('Site');

        return $OSCOM_Site->hasPage() && $OSCOM_Site->getPage()->isRPC();
    }

    public static function link($page, $parameters = null, $add_session_id = true, $search_engine_safe = true)
    {
        $OSCOM_Session = Registry::get('Session');

        $page = HTML::sanitize($page);

        $site = $req_site = static::$site;

        if ((strpos($page, '/') !== false) && (preg_match('/^([A-Z][A-Za-z0-9-_]*)\/(.*)$/', $page, $matches) === 1) && OSCOM::siteExists($matches[1], false)) {
            $req_site = $matches[1];
            $page = $matches[2];
        }

        if (!is_bool($add_session_id)) {
            $add_session_id = true;
        }

        if (!is_bool($search_engine_safe)) {
            $search_engine_safe = true;
        }

        if (($add_session_id === true) && ($site !== $req_site)) {
            $add_session_id = false;
        }

        $link = static::getConfig('http_server', $req_site) . static::getConfig('http_path', $req_site) . $page;

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
        if (($add_session_id == true) && $OSCOM_Session->hasStarted() && ($OSCOM_Session->isForceCookies() === false)) {
            if ((strlen(SID) > 0) || (((HTTP::getRequestType() == 'NONSSL') && (parse_url(static::getConfig('http_server', $req_site), PHP_URL_SCHEME) == 'https')) || ((HTTP::getRequestType() == 'SSL') && (parse_url(static::getConfig('http_server', $req_site), PHP_URL_SCHEME) == 'http')))) {
                $link .= $separator . HTML::sanitize(session_name() . '=' . session_id());
            }
        }

        while (strpos($link, '&&') !== false) {
            $link = str_replace('&&', '&', $link);
        }

        if ((SEARCH_ENGINE_FRIENDLY_URLS == 'true') && ($search_engine_safe == true)) {
            $link = str_replace(['?', '&', '='], '/', $link);
        }

        return $link;
    }

    public static function linkImage()
    {
        $args = func_get_args();

        if (!isset($args[0])) {
            $args[0] = null;
        }

        if (!isset($args[1])) {
            $args[1] = null;
        }

        $args[2] = false;

        $page = $args[0];
        $req_site = static::$site;

        if ((strpos($page, '/') !== false) && (preg_match('/^([A-Z][A-Za-z0-9-_]*)\/(.*)$/', $page, $matches) === 1) && OSCOM::siteExists($matches[1], false)) {
            $req_site = $matches[1];
            $page = $matches[2];
        }

        $args[0] = $req_site . '/' . static::getConfig('http_images_path', $req_site) . $page;

        $url = forward_static_call_array('static::link', $args);

        return $url;
    }

    public static function linkPublic()
    {
        $args = func_get_args();

        if (!isset($args[0])) {
            $args[0] = null;
        }

        if (!isset($args[1])) {
            $args[1] = null;
        }

        $args[2] = false;

        $page = $args[0];
        $req_site = static::$site;

        if ((strpos($page, '/') !== false) && (preg_match('/^([A-Z][A-Za-z0-9-_]*)\/(.*)$/', $page, $matches) === 1) && OSCOM::siteExists($matches[1], false)) {
            $req_site = $matches[1];
            $page = $matches[2];
        }

        $args[0] = 'Shop/public/Sites/' . $req_site . '/' . $page;

        $url = forward_static_call_array('static::link', $args);

        return $url;
    }

    public static function redirect()
    {
        $args = func_get_args();

        $url = forward_static_call_array('static::link', $args);

        if ((strstr($url, "\n") !== false) || (strstr($url, "\r") !== false)) {
            $url = static::link('index.php', '', false);
        }

        HTTP::redirect($url);
    }

    public static function getDef()
    {
        $OSCOM_Language = Registry::get('Language');

        return call_user_func_array([$OSCOM_Language, 'getDef'], func_get_args());
    }

    public static function hasRoute(array $path)
    {
        return array_slice(array_keys($_GET), 0, count($path)) == $path;
    }

    public static function loadConfig()
    {
        static::loadConfigFile(static::BASE_DIR . 'Conf/global.php', 'global');

        if (is_file(static::BASE_DIR . 'Custom/Conf/global.php')) {
            static::loadConfigFile(static::BASE_DIR . 'Custom/Conf/global.php', 'global');
        }

        foreach (glob(static::BASE_DIR . 'Sites/*', GLOB_ONLYDIR) as $s) {
            $s = basename($s);

            if (static::siteExists($s, false) && is_file(static::BASE_DIR . 'Sites/' . $s . '/site_conf.php')) {
                static::loadConfigFile(static::BASE_DIR . 'Sites/' . $s . '/site_conf.php', $s);

                if (is_file(static::BASE_DIR . 'Custom/Sites/' . $s . '/site_conf.php')) {
                    static::loadConfigFile(static::BASE_DIR . 'Custom/Sites/' . $s . '/site_conf.php', $s);
                }
            }
        }
    }

    public static function loadConfigFile($file, $group)
    {
        $cfg = [];

        if (is_file($file)) {
            include($file);

            if (isset($ini)) {
                $cfg = parse_ini_string($ini);
            }
        }

        if (!empty($cfg)) {
            static::$cfg[$group] = (isset(static::$cfg[$group])) ? array_merge(static::$cfg[$group], $cfg) : $cfg;
        }
    }

    public static function getConfig($key, $group = null)
    {
        if (!isset($group)) {
            $group = static::getSite();
        }

        if (isset(static::$cfg[$group][$key])) {
            return static::$cfg[$group][$key];
        }

        return static::$cfg['global'][$key];
    }

    public static function configExists($key, $group = null)
    {
        if (!isset($group)) {
            $group = static::getSite();
        }

        if (isset(static::$cfg[$group][$key])) {
            return true;
        }

        return isset(static::$cfg['global'][$key]);
    }

    public static function setConfig($key, $value, $group = null)
    {
        if (!isset($group)) {
            $group = 'global';
        }

        static::$cfg[$group][$key] = $value;
    }

    public static function autoload($class)
    {
        $prefix = 'OSC\\';

        if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
            return false;
        }

        if (strncmp($prefix . 'OM\Module\\', $class, strlen($prefix . 'OM\Module\\')) === 0) { // TODO remove and fix namespace
          $file = dirname(OSCOM_BASE_DIR) . '/' . str_replace(['OSC\OM\\', '\\'], ['', '/'], $class) . '.php';
          $custom = dirname(OSCOM_BASE_DIR) . '/' . str_replace(['OSC\OM\\', '\\'], ['OSC\Custom\OM\\', '/'], $class) . '.php';
        } else {
          $file = dirname(OSCOM_BASE_DIR) . '/' . str_replace('\\', '/', $class) . '.php';
          $custom = str_replace('OSC/OM/', 'OSC/Custom/OM/', $file);
        }

        if (is_file($custom)) {
            require($custom);
        } elseif (is_file($file)) {
            require($file);
        }
    }
}
