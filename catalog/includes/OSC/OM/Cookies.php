<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license GPL; https://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

use OSC\OM\OSCOM;

class Cookies
{
    protected $domain;
    protected $path;

    public function __construct()
    {
        if ((isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on')) || (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == 443))) {
            $this->domain = defined('HTTPS_COOKIE_DOMAIN') ? HTTPS_COOKIE_DOMAIN : '';
            $this->path = defined('HTTPS_COOKIE_PATH') ? HTTPS_COOKIE_PATH : '';
        } else {
            $this->domain = defined('HTTP_COOKIE_DOMAIN') ? HTTP_COOKIE_DOMAIN : '';
            $this->path = defined('HTTP_COOKIE_PATH') ? HTTP_COOKIE_PATH : '';
        }
    }

    public function set($name, $value = '', $expire = 0, $path = null, $domain = null, $secure = false, $httponly = false)
    {
        return setcookie($name, $value, $expire, isset($path) ? $path : $this->path, isset($domain) ? $domain : $this->domain, $secure, $httponly);
    }

    public function del($name, $path = null, $domain = null, $secure = false, $httponly = false)
    {
        if ($this->set($name, '', time() - 3600, $path, $domain, $secure, $httponly)) {
            if (isset($_COOKIE[$name])) {
                unset($_COOKIE[$name]);
            }

            return true;
        }

        return false;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }
}
