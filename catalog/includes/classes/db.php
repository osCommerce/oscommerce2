<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class db extends PDO {
    protected $_connected = false;
    protected $_server;
    protected $_username;
    protected $_password;
    protected $_database;
    protected $_port;
    protected $_driver;
    protected $_driver_options = array();
    protected $_driver_parent;

    public static function initialize($server = null, $username = null, $password = null, $database = null, $port = null, $driver = null, $driver_options = array()) {
      if ( !isset($server) && defined('DB_SERVER') ) {
        $server = DB_SERVER;
      }

      if ( !isset($username) && defined('DB_SERVER_USERNAME') ) {
        $username = DB_SERVER_USERNAME;
      }

      if ( !isset($password) && defined('DB_SERVER_PASSWORD') ) {
        $password = DB_SERVER_PASSWORD;
      }

      if ( !isset($database) && defined('DB_DATABASE') ) {
        $database = DB_DATABASE;
      }

      if ( !isset($port) && defined('DB_SERVER_PORT') ) {
        $port = DB_SERVER_PORT;
      }

      if ( !isset($driver) && defined('DB_DRIVER') ) {
        $driver = DB_DRIVER;
      }

      if ( !isset($driver_options[self::ATTR_ERRMODE]) ) {
        $driver_options[self::ATTR_ERRMODE] = self::ERRMODE_WARNING;
      }

      if ( !isset($driver_options[self::ATTR_DEFAULT_FETCH_MODE]) ) {
        $driver_options[self::ATTR_DEFAULT_FETCH_MODE] = self::FETCH_ASSOC;
      }

      if ( !isset($driver_options[self::ATTR_STATEMENT_CLASS]) ) {
        include('db_statement.php');
        $driver_options[self::ATTR_STATEMENT_CLASS] = array('db_statement');
      }

      include('db/' . $driver . '.php');
      $object = new $driver($server, $username, $password, $database, $port, $driver_options);

      $object->_driver = $driver;

      return $object;
    }

    public function exec($statement) {
      $statement = $this->_autoPrefixTables($statement);

      return parent::exec($statement);
    }

    public function prepare($statement, $driver_options = array()) {
      $statement = $this->_autoPrefixTables($statement);

      return parent::prepare($statement, $driver_options);
    }

    public function query($statement) {
      $statement = $this->_autoPrefixTables($statement);

      $args = func_get_args();

      if ( count($args) > 1 ) {
        return call_user_func_array(array($this, 'parent::query'), $args);
      } else {
        return parent::query($statement);
      }
    }

    public function perform($table, $data, $where_condition = null) {
      if ( empty($data) || !is_array($data) ) {
        return false;
      }

      if ( (strlen($table) < 7) || (substr($table, 0, 7) != ':table') ) {
        $table = ':table_' . $table;
      }

      if ( isset($where_condition) ) {
        $statement = 'update ' . $table . ' set ';

        foreach ( $data as $c => $v ) {
          if ( $v == 'now()' || $v == 'null' ) {
            $statement .= $c . ' = ' . $v . ', ';
          } else {
            $statement .= $c . ' = :new_' . $c . ', ';
          }
        }

        $statement = substr($statement, 0, -2) . ' where ';

        foreach ( array_keys($where_condition) as $c ) {
          $statement .= $c . ' = :cond_' . $c . ' and ';
        }

        $statement = substr($statement, 0, -5);

        $Q = $this->prepare($statement);

        foreach ( $data as $c => $v ) {
          if ( $v != 'now()' && $v != 'null' ) {
            $Q->bindValue(':new_' . $c, $v);
          }
        }

        foreach ( $where_condition as $c => $v ) {
          $Q->bindValue(':cond_' . $c, $v);
        }

        $Q->execute();

        return $Q->rowCount();
      } else {
        $is_prepared = false;

        $statement = 'insert into ' . $table . ' (' . implode(', ', array_keys($data)) . ') values (';

        foreach ( $data as $c => $v ) {
          if ( $v == 'now()' || $v == 'null' ) {
            $statement .= $v . ', ';
          } else {
            if ( $is_prepared === false ) {
              $is_prepared = true;
            }

            $statement .= ':' . $c . ', ';
          }
        }

        $statement = substr($statement, 0, -2) . ')';

        if ( $is_prepared === true ) {
          $Q = $this->prepare($statement);

          foreach ( $data as $c => $v ) {
            if ( $v != 'now()' && $v != 'null' ) {
              $Q->bindValue(':' . $c, $v);
            }
          }

          $Q->execute();

          return $Q->rowCount();
        } else {
          return $this->exec($statement);
        }
      }

      return false;
    }

    public function getBatchFrom($pageset, $max_results) {
      return max(($pageset * $max_results) - $max_results, 0);
    }

    public function getDriver() {
      return $this->_driver;
    }

    public function getDriverParent() {
      return $this->_driver_parent;
    }

    public function hasDriverParent() {
      return isset($this->_driver_parent);
    }

    public function importSQL($sql_file, $table_prefix = null) {
      if ( file_exists($sql_file) ) {
        $import_queries = file_get_contents($sql_file);
      } else {
        trigger_error(sprintf(ERROR_SQL_FILE_NONEXISTENT, $sql_file));

        return false;
      }

      set_time_limit(0);

      $sql_queries = array();
      $sql_length = strlen($import_queries);
      $pos = strpos($import_queries, ';');

      for ( $i=$pos; $i<$sql_length; $i++ ) {
// remove comments
        if ( ($import_queries[0] == '#') || (substr($import_queries, 0, 2) == '--') ) {
          $import_queries = ltrim(substr($import_queries, strpos($import_queries, "\n")));
          $sql_length = strlen($import_queries);
          $i = strpos($import_queries, ';')-1;
          continue;
        }

        if ( $import_queries[($i+1)] == "\n" ) {
          $next = '';

          for ( $j=($i+2); $j<$sql_length; $j++ ) {
            if ( !empty($import_queries[$j]) ) {
              $next = substr($import_queries, $j, 6);

              if ( ($next[0] == '#') || (substr($next, 0, 2) == '--') ) {
// find out where the break position is so we can remove this line (#comment line)
                for ( $k=$j; $k<$sql_length; $k++ ) {
                  if ( $import_queries[$k] == "\n" ) {
                    break;
                  }
                }

                $query = substr($import_queries, 0, $i+1);

                $import_queries = substr($import_queries, $k);

// join the query before the comment appeared, with the rest of the dump
                $import_queries = $query . $import_queries;
                $sql_length = strlen($import_queries);
                $i = strpos($import_queries, ';')-1;
                continue 2;
              }

              break;
            }
          }

          if ( empty($next) ) { // get the last insert query
            $next = 'insert';
          }

          if ( (strtoupper($next) == 'DROP T') || (strtoupper($next) == 'CREATE') || (strtoupper($next) == 'INSERT') || (strtoupper($next) == 'ALTER ') || (strtoupper($next) == 'SET FO') ) {
            $next = '';

            $sql_query = substr($import_queries, 0, $i);

            if ( isset($table_prefix) ) {
              if ( strtoupper(substr($sql_query, 0, 25)) == 'DROP TABLE IF EXISTS OSC_' ) {
                $sql_query = 'DROP TABLE IF EXISTS ' . $table_prefix . substr($sql_query, 25);
              } elseif ( strtoupper(substr($sql_query, 0, 17)) == 'CREATE TABLE OSC_' ) {
                $sql_query = 'CREATE TABLE ' . $table_prefix . substr($sql_query, 17);
              } elseif ( strtoupper(substr($sql_query, 0, 16)) == 'INSERT INTO OSC_' ) {
                $sql_query = 'INSERT INTO ' . $table_prefix . substr($sql_query, 16);
              } elseif ( strtoupper(substr($sql_query, 0, 12)) == 'CREATE INDEX' ) {
                $sql_query = substr($sql_query, 0, stripos($sql_query, ' on osc_')) . ' on ' . $table_prefix . substr($sql_query, stripos($sql_query, ' on osc_') + 8);
              }
            }

            $sql_queries[] = trim($sql_query);

            $import_queries = ltrim(substr($import_queries, $i+1));
            $sql_length = strlen($import_queries);
            $i = strpos($import_queries, ';')-1;
          }
        }
      }

      $error = false;

      foreach ( $sql_queries as $q ) {
        if ( $this->exec($q) === false ) {
          $error = true;

          break;
        }
      }

      return !$error;
    }

    protected function _autoPrefixTables($statement) {
      if ( defined('DB_TABLE_PREFIX') ) {
        $statement = str_replace(':table_', DB_TABLE_PREFIX, $statement);
      }

      return $statement;
    }
  }
?>
