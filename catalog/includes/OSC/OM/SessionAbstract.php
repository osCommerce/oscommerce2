<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\OM;

use OSC\OM\OSCOM;
use OSC\OM\Registry;

abstract class SessionAbstract
{
    protected $name;
    protected $force_cookies = true;

/**
 * Checks if a session exists
 *
 * @param string $session_id The ID of the session
 */

    abstract public function exists($session_id);

/**
 * Verify an existing session ID and create or resume the session if the existing session ID is valid
 *
 * @return boolean
 */

    public function start()
    {
        $OSCOM_Cookies = Registry::get('Cookies');

// this class handles session.use_strict_mode already
        if ((int)ini_get('session.use_strict_mode') === 1) {
            ini_set('session.use_strict_mode', 0);
        }

        if (parse_url(OSCOM::getConfig('http_server'), PHP_URL_SCHEME) == 'https') {
            if ((int)ini_get('session.cookie_secure') === 0) {
                ini_set('session.cookie_secure', 1);
            }
        }

        if ((int)ini_get('session.cookie_httponly') === 0) {
            ini_set('session.cookie_httponly', 1);
        }

        if ((int)ini_get('session.use_only_cookies') !== 1) {
            ini_set('session.use_only_cookies', 1);
        }

        $session_can_start = true;

        Registry::get('Hooks')->call('Session', 'StartBefore', [
            'can_start' => &$session_can_start
        ]);

        session_set_cookie_params(0, $OSCOM_Cookies->getPath(), $OSCOM_Cookies->getDomain(), (bool)ini_get('session.cookie_secure'), (bool)ini_get('session.cookie_httponly'));

        if (isset($_GET[$this->name]) && ($this->force_cookies || !(bool)preg_match('/^[a-zA-Z0-9,-]+$/', $_GET[$this->name]) || !$this->exists($_GET[$this->name]))) {
            unset($_GET[$this->name]);
        }

        if (isset($_POST[$this->name]) && ($this->force_cookies || !(bool)preg_match('/^[a-zA-Z0-9,-]+$/', $_POST[$this->name]) || !$this->exists($_POST[$this->name]))) {
            unset($_POST[$this->name]);
        }

        if (isset($_COOKIE[$this->name]) && (!(bool)preg_match('/^[a-zA-Z0-9,-]+$/', $_COOKIE[$this->name]) || !$this->exists($_COOKIE[$this->name]))) {
            $OSCOM_Cookies->del($this->name, $OSCOM_Cookies->getPath(), $OSCOM_Cookies->getDomain(), (bool)ini_get('session.cookie_secure'), (bool)ini_get('session.cookie_httponly'));
        }

        if ($this->force_cookies === false) {
            if (isset($_GET[$this->name]) && (!isset($_COOKIE[$this->name]) || ($_COOKIE[$this->name] != $_GET[$this->name]))) {
                session_id($_GET[$this->name]);
            } elseif (isset($_POST[$this->name]) && (!isset($_COOKIE[$this->name]) || ($_COOKIE[$this->name] != $_POST[$this->name]))) {
                session_id($_POST[$this->name]);
            }
        }

        if (($session_can_start === true) && session_start()) {
            Registry::get('Hooks')->call('Session', 'StartAfter');

            return true;
        }

        return false;
    }

    public function setForceCookies($force_cookies)
    {
        $this->force_cookies = $force_cookies;
    }

    public function isForceCookies()
    {
        return $this->force_cookies;
    }

/**
 * Checks if the session has been started or not
 *
 * @return boolean
 */

    public function hasStarted() {
      return session_status() === PHP_SESSION_ACTIVE;
    }

/**
 * Deletes an existing session
 */

    public function kill()
    {
        $OSCOM_Cookies = Registry::get('Cookies');

        $result = true;

        if (isset($_COOKIE[$this->name])) {
            $OSCOM_Cookies->del($this->name, $OSCOM_Cookies->getPath(), $OSCOM_Cookies->getDomain(), (bool)ini_get('session.cookie_secure'), (bool)ini_get('session.cookie_httponly'));
        }

        if ($this->hasStarted()) {
            $_SESSION = [];

            $result = session_destroy();
        }

        return $result;
    }

/**
 * Delete an existing session and move the session data to a new session with a new session ID
 */

    public function recreate()
    {
        $delete_flag = true;

        if (!$this->exists(session_id())) {
            $delete_flag = false;
        }

        $session_old_id = session_id();

        $result = session_regenerate_id($delete_flag);

        if ($result === true) {
            Registry::get('Hooks')->call('Session', 'Recreated', [
                'old_id' => $session_old_id
            ]);

            return true;
        }

        return false;
    }

/**
 * Sets the name of the session
 *
 * @param string $name The name of the session
 */

    public function setName($name)
    {
        return session_name($name);
    }

/**
 * Sets the life time of the session (in seconds)
 *
 * @param int $time The life time of the session (in seconds)
 */

    public function setLifeTime($time)
    {
        return ini_set('session.gc_maxlifetime', $time);
    }
}
