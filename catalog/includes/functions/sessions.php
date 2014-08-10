<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

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
        return tep_db_query("update " . TABLE_SESSIONS . " set expiry = '" . tep_db_input(time()) . "', value = '" . tep_db_input($value) . "' where sesskey = '" . tep_db_input($key) . "'");
      } else {
        return tep_db_query("insert into " . TABLE_SESSIONS . " values ('" . tep_db_input($key) . "', '" . tep_db_input(time()) . "', '" . tep_db_input($value) . "')");
      }
    }

    function _sess_destroy($key) {
      return tep_db_query("delete from " . TABLE_SESSIONS . " where sesskey = '" . tep_db_input($key) . "'");
    }

    function _sess_gc($maxlifetime) {
      return tep_db_query("delete from " . TABLE_SESSIONS . " where expiry < '" . (time() - $maxlifetime) . "'");
    }

    session_set_save_handler('_sess_open', '_sess_close', '_sess_read', '_sess_write', '_sess_destroy', '_sess_gc');
  }

  function tep_session_start() {
    $sane_session_id = true;

    if ( isset($_GET[session_name()]) ) {
      if ( (SESSION_FORCE_COOKIE_USE == 'True') || (preg_match('/^[a-zA-Z0-9,-]+$/', $_GET[session_name()]) == false) ) {
        unset($_GET[session_name()]);

        $sane_session_id = false;
      }
    }

    if ( isset($_POST[session_name()]) ) {
      if ( (SESSION_FORCE_COOKIE_USE == 'True') || (preg_match('/^[a-zA-Z0-9,-]+$/', $_POST[session_name()]) == false) ) {
        unset($_POST[session_name()]);

        $sane_session_id = false;
      }
    }

    if ( isset($_COOKIE[session_name()]) ) {
      if ( preg_match('/^[a-zA-Z0-9,-]+$/', $_COOKIE[session_name()]) == false ) {
        $session_data = session_get_cookie_params();

        setcookie(session_name(), '', time()-42000, $session_data['path'], $session_data['domain']);
        unset($_COOKIE[session_name()]);

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
      if (!isset($GLOBALS[$variable])) {
        $GLOBALS[$variable] = null;
      }
      $_SESSION[$variable] =& $GLOBALS[$variable];
    }
    return false;
  }

  function tep_session_destroy() {
    if ( isset($_COOKIE[session_name()]) ) {
      $session_data = session_get_cookie_params();

      setcookie(session_name(), '', time()-42000, $session_data['path'], $session_data['domain']);
      unset($_COOKIE[session_name()]);
    }

    return session_destroy();
  }

  function tep_session_recreate() {
    global $SID;

      $old_id = session_id();

      session_regenerate_id(true);

      if (!empty($SID)) {
        $SID = session_name() . '=' . session_id();
      }

      tep_whos_online_update_session_id($old_id, session_id());
  }
?>
