<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  if (STORE_SESSIONS == 'mysql') {
    function _sess_open($save_path, $session_name) {
      return true;
    }

    function _sess_close() {
      return true;
    }

    function _sess_read($key) {
      $OSCOM_Db = Registry::get('Db');

      $Qsession = $OSCOM_Db->prepare('select value from :table_sessions where sesskey = :sesskey');
      $Qsession->bindValue(':sesskey', $key);
      $Qsession->execute();

      if ($Qsession->fetch() !== false) {
        return $Qsession->value('value');
      }

      return '';
    }

    function _sess_write($key, $value) {
      $OSCOM_Db = Registry::get('Db');

      $Qcheck = $OSCOM_Db->prepare('select 1 from :table_sessions where sesskey = :sesskey');
      $Qcheck->bindValue(':sesskey', $key);
      $Qcheck->execute();

      if ($Qcheck->fetch() !== false) {
        $result = $OSCOM_Db->save('sessions', ['expiry' => time(), 'value' => $value], ['sesskey' => $key]);
      } else {
        $result = $OSCOM_Db->save('sessions', ['sesskey' => $key, 'expiry' => time(), 'value' => $value]);
      }

      return $result !== false;
    }

    function _sess_destroy($key) {
      $OSCOM_Db = Registry::get('Db');

      $result = $OSCOM_Db->delete('sessions', ['sesskey' => $key]);

      return $result !== false;
    }

    function _sess_gc($maxlifetime) {
      $OSCOM_Db = Registry::get('Db');

      $Qdel = $OSCOM_Db->prepare('delete from :table_sessions where expiry < :expiry');
      $Qdel->bindValue(':expiry', time() - $maxlifetime);
      $Qdel->execute();

      return $Qdel->isError() === false;
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
      OSCOM::redirect('index.php', '', 'NONSSL', false);
    }

    register_shutdown_function('session_write_close');

    return session_start();
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
