<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  if (STORE_SESSIONS == 'mysql') {
    if (!$SESS_LIFE = get_cfg_var('session.gc_maxlifetime')) {
      $SESS_LIFE = 1440;
    }

    function _sess_open($save_path, $session_name) {
      return true;
    }

    function _sess_close() {
      return true;
    }

    function _sess_read($key) {
      $value_query = osc_db_query("select value from " . TABLE_SESSIONS . " where sesskey = '" . osc_db_input($key) . "' and expiry > '" . time() . "'");
      $value = osc_db_fetch_array($value_query);

      if (isset($value['value'])) {
        return $value['value'];
      }

      return '';
    }

    function _sess_write($key, $val) {
      global $SESS_LIFE;

      $expiry = time() + $SESS_LIFE;
      $value = $val;

      $check_query = osc_db_query("select count(*) as total from " . TABLE_SESSIONS . " where sesskey = '" . osc_db_input($key) . "'");
      $check = osc_db_fetch_array($check_query);

      if ($check['total'] > 0) {
        return osc_db_query("update " . TABLE_SESSIONS . " set expiry = '" . osc_db_input($expiry) . "', value = '" . osc_db_input($value) . "' where sesskey = '" . osc_db_input($key) . "'");
      } else {
        return osc_db_query("insert into " . TABLE_SESSIONS . " values ('" . osc_db_input($key) . "', '" . osc_db_input($expiry) . "', '" . osc_db_input($value) . "')");
      }
    }

    function _sess_destroy($key) {
      return osc_db_query("delete from " . TABLE_SESSIONS . " where sesskey = '" . osc_db_input($key) . "'");
    }

    function _sess_gc($maxlifetime) {
      osc_db_query("delete from " . TABLE_SESSIONS . " where expiry < '" . time() . "'");

      return true;
    }

    session_set_save_handler('_sess_open', '_sess_close', '_sess_read', '_sess_write', '_sess_destroy', '_sess_gc');
  }

  function osc_session_start() {
    $sane_session_id = true;

    if (isset($_GET[session_name()])) {
      if (preg_match('/^[a-zA-Z0-9]+$/', $_GET[session_name()]) == false) {
        unset($_GET[session_name()]);

        $sane_session_id = false;
      }
    } elseif (isset($_POST[session_name()])) {
      if (preg_match('/^[a-zA-Z0-9]+$/', $_POST[session_name()]) == false) {
        unset($_POST[session_name()]);

        $sane_session_id = false;
      }
    } elseif (isset($_COOKIE[session_name()])) {
      if (preg_match('/^[a-zA-Z0-9]+$/', $_COOKIE[session_name()]) == false) {
        $session_data = session_get_cookie_params();

        setcookie(session_name(), '', time()-42000, $session_data['path'], $session_data['domain']);

        $sane_session_id = false;
      }
    }

    if ($sane_session_id == false) {
      osc_redirect(osc_href_link(FILENAME_DEFAULT, '', 'NONSSL', false));
    }

    register_shutdown_function('session_write_close');

    register_shutdown_function('session_write_close');

    return session_start();
  }
?>
