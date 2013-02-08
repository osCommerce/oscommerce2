<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  header('Cache-Control: no-cache, must-revalidate');
  header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

  require('includes/application.php');

  $dir_fs_www_root = dirname(__FILE__);

  if (isset($HTTP_GET_VARS['action']) && !empty($HTTP_GET_VARS['action'])) {
    switch ($HTTP_GET_VARS['action']) {
      case 'dbCheck':
        $db = array('DB_SERVER' => trim(rawurldecode($HTTP_GET_VARS['server'])),
                    'DB_SERVER_USERNAME' => trim(rawurldecode($HTTP_GET_VARS['username'])),
                    'DB_SERVER_PASSWORD' => trim(rawurldecode($HTTP_GET_VARS['password'])),
                    'DB_DATABASE' => trim(rawurldecode($HTTP_GET_VARS['name'])),
                    'DB_DATABASE_CHARSET' => trim(rawurldecode($HTTP_GET_VARS['charset']))
                   );

        $db_error = false;
        osc_db_connect($db['DB_SERVER'], $db['DB_SERVER_USERNAME'], $db['DB_SERVER_PASSWORD']);

        if ($db_error == false) {
          if (!@osc_db_select_db($db['DB_DATABASE'])) {
            $db_error = mysqli_error();
          }
          if ($db_error == false) {
            if (!@osc_db_query('SET CHARACTER SET "' . $db['DB_DATABASE_CHARSET'] . '"')) {
              $db_error = mysql_error();
            }
          }
          if ($db_error == false) {
            if (!@osc_db_query('DROP TABLE IF EXISTS oscommerce_test_table')) {
              $db_error = mysql_error();
            }
          }
          if ($db_error == false ) {
            if (!@osc_db_query('CREATE TABLE oscommerce_test_table ( table_id INT NOT NULL ,
                                                                     text VARCHAR(45) NULL ,
                                                                     PRIMARY KEY (table_id) )')) {
              $db_error = mysql_error();
            }
          }
          if ($db_error == false ) {
            // well done, the user has all privileges to setup database
            $result_query = osc_db_query('SHOW CREATE TABLE `oscommerce_test_table`');
            $result = osc_db_fetch_array($result_query);

            $pos =  strpos($result['Create Table'], 'DEFAULT CHARSET=' . $db['DB_DATABASE_CHARSET'] . '');

            if ($pos === false) {
              $db_error = $result['Create Table'];
              $db_error .= '<br /><br />' . 'Charset is not compatible! Look at create database tool in extras.';
              $db_error .= '<br /><br />' . 'Recommended SQL command :<br />ALTER DATABASE ' . $db['DB_DATABASE'] . ' DEFAULT CHARACTER SET ' . $db['DB_DATABASE_CHARSET'] . ';';
            }
          }
        }

        if ($db_error != false) {
          echo '[[0|' . $db_error . ']]';
        } else {
          echo '[[1]]';
        }

        exit;
        break;
      case 'dbCreate':
        $db = array('DB_SERVER' => trim(rawurldecode($HTTP_GET_VARS['server'])),
                    'DB_SERVER_USERNAME' => trim(rawurldecode($HTTP_GET_VARS['username'])),
                    'DB_SERVER_PASSWORD' => trim(rawurldecode($HTTP_GET_VARS['password'])),
                    'DB_DATABASE' => trim(rawurldecode($HTTP_GET_VARS['name'])),
                    'DB_DATABASE_CHARSET' => trim(rawurldecode($HTTP_GET_VARS['charset']))
                   );

        $db_error = false;
        osc_db_connect($db['DB_SERVER'], $db['DB_SERVER_USERNAME'], $db['DB_SERVER_PASSWORD']);

        if (!@osc_db_select_db($db[DB_DATABASE])) {
          if (@osc_db_query('create database ' . $db[DB_DATABASE] . ' default character set ' . $db[DB_DATABASE_CHARSET])) {
            osc_db_select_db($database);
          } else {
            $db_error = mysql_error();
          }
        } else {
          // Recreate database is not allowed in install process. Use 'ALTER' instead.
          if ($db_error == false) {
            if (!@osc_db_query('SET CHARACTER SET "' . $db['DB_DATABASE_CHARSET'] . '"')) {
              $db_error = mysql_error();
            }
          }
          if ($db_error == false) {
            if (!@osc_db_query('DROP TABLE IF EXISTS oscommerce_test_table')) {
              $db_error = mysql_error();
            }
          }
          if ($db_error == false ) {
            if (!@osc_db_query('CREATE TABLE oscommerce_test_table ( table_id INT NOT NULL ,
                                                                     text VARCHAR(45) NULL ,
                                                                     PRIMARY KEY (table_id) )')) {
              $db_error = mysql_error();
            }
          }
          if ($db_error == false ) {
            // well done, the user has all privileges to setup database
            $result_query = osc_db_query('SHOW CREATE TABLE `oscommerce_test_table`');
            $result = osc_db_fetch_array($result_query);

            $pos =  strpos($result['Create Table'], 'DEFAULT CHARSET=' . $db['DB_DATABASE_CHARSET'] . '');

            if ($pos === false) {
              $db_error = $result['Create Table'];
              $db_error .= '<br /><br />' . 'Charset is not compatible! Look at create database tool in extras.';
              $db_error .= '<br /><br />' . 'Recommended SQL command :<br />ALTER DATABASE ' . $db['DB_DATABASE'] . ' DEFAULT CHARACTER SET ' . $db['DB_DATABASE_CHARSET'] . ';';
            }
          }
        }

        if ($db_error != false) {
          echo '[[0|' . $db_error . ']]';
        } else {
          echo '[[1]]';
        }

        exit;
        break;
      case 'dbImport':
        $db = array('DB_SERVER' => trim(rawurldecode($HTTP_GET_VARS['server'])),
                    'DB_SERVER_USERNAME' => trim(rawurldecode($HTTP_GET_VARS['username'])),
                    'DB_SERVER_PASSWORD' => trim(rawurldecode($HTTP_GET_VARS['password'])),
                    'DB_DATABASE' => trim(rawurldecode($HTTP_GET_VARS['name'])),
                    'DB_DATABASE_CHARSET' => trim(rawurldecode($HTTP_GET_VARS['charset']))
                   );

        osc_db_connect($db['DB_SERVER'], $db['DB_SERVER_USERNAME'], $db['DB_SERVER_PASSWORD']);
        osc_db_query('SET CHARACTER SET "' . $db['DB_DATABASE_CHARSET'] . '"');

        $db_error = false;
        $sql_file = $dir_fs_www_root . '/oscommerce.sql';

        osc_set_time_limit(0);
        osc_db_install($db['DB_DATABASE'], $db['DB_DATABASE_CHARSET'], $sql_file);

        if ($db_error != false) {
          echo '[[0|' . $db_error . ']]';
        } else {
          echo '[[1]]';
        }

        exit;
        break;
    }
  }

  echo '[[-100|noActionError]]';
?>
