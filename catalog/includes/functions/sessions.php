<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  if ( (PHP_VERSION >= 4.3) && ((bool)ini_get('register_globals') == false) ) {
    @ini_set('session.bug_compat_42', 1);
    @ini_set('session.bug_compat_warn', 0);
  }

  if (STORE_SESSIONS == 'mysql') {
    function _sess_open($save_path, $session_name) {
      return true;
    }

    function _sess_close() {
      return true;
    }

    function _sess_read($key) {
      $value_query = tep_db_query("select value from " . TABLE_SESSIONS . " where sesskey = '" . tep_db_input($key) . "'");
      $value = tep_db_fetch_array($value_query);

      if (isset($value['value'])) {
        return $value['value'];
      }

      return '';
    }

    function _sess_write($key, $value) {
      $check_query = tep_db_query("select 1 from " . TABLE_SESSIONS . " where sesskey = '" . tep_db_input($key) . "'");

      if ( tep_db_num_rows($check_query) > 0 ) {
        $result = tep_db_query("update " . TABLE_SESSIONS . " set expiry = '" . tep_db_input(time()) . "', value = '" . tep_db_input($value) . "' where sesskey = '" . tep_db_input($key) . "'");
      } else {
        $result = tep_db_query("insert into " . TABLE_SESSIONS . " values ('" . tep_db_input($key) . "', '" . tep_db_input(time()) . "', '" . tep_db_input($value) . "')");
      }

      return $result !== false;
    }

    function _sess_destroy($key) {
      $result = tep_db_query("delete from " . TABLE_SESSIONS . " where sesskey = '" . tep_db_input($key) . "'");

      return $result !== false;
    }

    function _sess_gc($maxlifetime) {
      $result = tep_db_query("delete from " . TABLE_SESSIONS . " where expiry < '" . (time() - $maxlifetime) . "'");

      return $result !== false;
    }

    session_set_save_handler('_sess_open', '_sess_close', '_sess_read', '_sess_write', '_sess_destroy', '_sess_gc');
  }

  function tep_session_start() {
    global $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS;

    $sane_session_id = true;

    if ( isset($HTTP_GET_VARS[tep_session_name()]) ) {
      if ( (SESSION_FORCE_COOKIE_USE == 'True') || (preg_match('/^[a-zA-Z0-9,-]+$/', $HTTP_GET_VARS[tep_session_name()]) == false) ) {
        unset($HTTP_GET_VARS[tep_session_name()]);

        $sane_session_id = false;
      }
    }

    if ( isset($HTTP_POST_VARS[tep_session_name()]) ) {
      if ( (SESSION_FORCE_COOKIE_USE == 'True') || (preg_match('/^[a-zA-Z0-9,-]+$/', $HTTP_POST_VARS[tep_session_name()]) == false) ) {
        unset($HTTP_POST_VARS[tep_session_name()]);

        $sane_session_id = false;
      }
    }

    if ( isset($HTTP_COOKIE_VARS[tep_session_name()]) ) {
      if ( preg_match('/^[a-zA-Z0-9,-]+$/', $HTTP_COOKIE_VARS[tep_session_name()]) == false ) {
        $session_data = session_get_cookie_params();

        setcookie(tep_session_name(), '', time()-42000, $session_data['path'], $session_data['domain']);
        unset($HTTP_COOKIE_VARS[tep_session_name()]);

        $sane_session_id = false;
      }
    }

    if ($sane_session_id == false) {
      tep_redirect(tep_href_link(FILENAME_DEFAULT, '', 'NONSSL', false));
    }

    register_shutdown_function('session_write_close');

    return session_start();
  }

  function tep_session_register($variable) {
    global $session_started;

    if ($session_started == true) {
      if (PHP_VERSION < 4.3) {
        return session_register($variable);
      } else {
        if (!isset($GLOBALS[$variable])) {
          $GLOBALS[$variable] = null;
        }

        $_SESSION[$variable] =& $GLOBALS[$variable];
      }
    }

    return false;
  }

  function tep_session_is_registered($variable) {
    if (PHP_VERSION < 4.3) {
      return session_is_registered($variable);
    } else {
      return isset($_SESSION) && array_key_exists($variable, $_SESSION);
    }
  }

  function tep_session_unregister($variable) {
    if (PHP_VERSION < 4.3) {
      return session_unregister($variable);
    } else {
      unset($_SESSION[$variable]);
    }
  }

  function tep_session_id($sessid = '') {
    if (!empty($sessid)) {
      return session_id($sessid);
    } else {
      return session_id();
    }
  }

  function tep_session_name($name = '') {
    if (!empty($name)) {
      return session_name($name);
    } else {
      return session_name();
    }
  }

  function tep_session_close() {
    if (PHP_VERSION >= '4.0.4') {
      return session_write_close();
    } elseif (function_exists('session_close')) {
      return session_close();
    }
  }

  function tep_session_destroy() {
    global $HTTP_COOKIE_VARS;

    if ( isset($HTTP_COOKIE_VARS[tep_session_name()]) ) {
      $session_data = session_get_cookie_params();

      setcookie(tep_session_name(), '', time()-42000, $session_data['path'], $session_data['domain']);
      unset($HTTP_COOKIE_VARS[tep_session_name()]);
    }

    return session_destroy();
  }

  function tep_session_save_path($path = '') {
    if (!empty($path)) {
      return session_save_path($path);
    } else {
      return session_save_path();
    }
  }

  function tep_session_recreate() {
    global $SID;

    if (PHP_VERSION >= 5.1) {
      $old_id = session_id();

      session_regenerate_id(true);

      if (!empty($SID)) {
        $SID = tep_session_name() . '=' . tep_session_id();
      }

      tep_whos_online_update_session_id($old_id, tep_session_id());
    }
  }
?>
