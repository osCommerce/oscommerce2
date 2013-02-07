<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class mysql_standard extends db {
    protected $_has_native_fk = false;
    protected $_fkeys = array();

    public function __construct($server, $username, $password, $database, $port, $driver_options) {
      $this->_server = $server;
      $this->_username = $username;
      $this->_password = $password;
      $this->_database = $database;
      $this->_port = $port;
      $this->_driver_options = $driver_options;

// Override ATTR_STATEMENT_CLASS to automatically handle foreign key constraints
      if ( $this->_has_native_fk === false ) {
        include('mysql_standard_statement.php');
        $this->_driver_options[self::ATTR_STATEMENT_CLASS] = array('mysql_standard_statement', array($this));
      }

      $this->_driver_options[self::MYSQL_ATTR_INIT_COMMAND] = 'set names utf8';

      return $this->connect();
    }

    public function connect() {
      $dsn_array = array();

      if ( !empty($this->_database) ) {
        $dsn_array[] = 'dbname=' . $this->_database;
      }

      if ( (strpos($this->_server, '/') !== false) || (strpos($this->_server, '\\') !== false) ) {
        $dsn_array[] = 'unix_socket=' . $this->_server;
      } else {
        $dsn_array[] = 'host=' . $this->_server;

        if ( !empty($this->_port) ) {
          $dsn_array[] = 'port=' . $this->_port;
        }
      }

      $dsn = 'mysql:' . implode(';', $dsn_array);

      $this->_connected = true;

      $dbh = parent::__construct($dsn, $this->_username, $this->_password, $this->_driver_options);

      if ( !defined('OSCOM_SETUP') && $this->_has_native_fk === false ) {
//        $this->setupForeignKeys();
      }

      return $dbh;
    }

    public function getForeignKeys($table = null) {
      if ( isset($table) ) {
        return $this->_fkeys[$table];
      }

      return $this->_fkeys;
    }

    public function setupForeignKeys() {
      $Qfk = $this->query('select * from :table_fk_relationships');
      $Qfk->setCache('fk_relationships');
      $Qfk->execute();

      while ( $Qfk->fetch() ) {
        $this->_fkeys[$Qfk->value('to_table')][] = array('from_table' => $Qfk->value('from_table'),
                                                         'from_field' => $Qfk->value('from_field'),
                                                         'to_field' => $Qfk->value('to_field'),
                                                         'on_update' => $Qfk->value('on_update'),
                                                         'on_delete' => $Qfk->value('on_delete'));
      }
    }

    public function hasForeignKey($table) {
      return isset($this->_fkeys[$table]);
    }
  }
?>
