<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

use OSC\OM\HTML;

class Db extends \PDO
{
    protected $connected = false;
    protected $server;
    protected $username;
    protected $password;
    protected $database;
    protected $port;
    protected $driver_options = [];

    public static function initialize(
        $server = null,
        $username = null,
        $password = null,
        $database = null,
        $port = null,
        array $driver_options = []
    ) {
        if (!isset($server)) {
            $server = DB_SERVER;
        }

        if (!isset($username)) {
            $username = DB_SERVER_USERNAME;
        }

        if (!isset($password)) {
            $password = DB_SERVER_PASSWORD;
        }

        if (!isset($database)) {
            $database = DB_DATABASE;
        }

        if (!isset($driver_options[\PDO::ATTR_ERRMODE])) {
            $driver_options[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_WARNING;
        }

        if (!isset($driver_options[\PDO::ATTR_DEFAULT_FETCH_MODE])) {
            $driver_options[\PDO::ATTR_DEFAULT_FETCH_MODE] = \PDO::FETCH_ASSOC;
        }

        if (!isset($driver_options[\PDO::ATTR_STATEMENT_CLASS])) {
            $driver_options[\PDO::ATTR_STATEMENT_CLASS] = array('OSC\\OM\\DbStatement');
        }

        $class = 'OSC\\OM\\Db\\MySQL';
        $object = new $class($server, $username, $password, $database, $port, $driver_options);

        return $object;
    }

    public function exec($statement)
    {
        $statement = $this->autoPrefixTables($statement);

        return parent::exec($statement);
    }

    public function prepare($statement, $driver_options = null)
    {
        $statement = $this->autoPrefixTables($statement);

        $DbStatement = parent::prepare($statement, is_array($driver_options) ? $driver_options : []);
        $DbStatement->setQueryCall('prepare');
        $DbStatement->setPDO($this);

        return $DbStatement;
    }

    public function query($statement)
    {
        $statement = $this->autoPrefixTables($statement);

        $args = func_get_args();

        if (count($args) > 1) {
            $DbStatement = call_user_func_array(array($this, 'parent::query'), $args);
        } else {
            $DbStatement = parent::query($statement);
        }

        $DbStatement->setQueryCall('query');
        $DbStatement->setPDO($this);

        return $DbStatement;
    }

    public function get($table, $fields, array $where = null, $order = null, $limit = null, $cache = null)
    {
        if (!is_array($table)) {
            $table = [ $table ];
        }

        array_walk($table, function(&$v, &$k) {
            if ((strlen($v) < 7) || (substr($v, 0, 7) != ':table_')) {
                $v = ':table_' . $v;
            }
        });

        if (!is_array($fields)) {
            $fields = [ $fields ];
        }

        if (isset($order) && !is_array($order)) {
            $order = [ $order ];
        }

        if (isset($limit)) {
            if (is_array($limit) && (count($limit) === 2) && is_numeric($limit[0]) && is_numeric($limit[1])) {
                $limit = implode(', ', $limit);
            } elseif (!is_numeric($limit)) {
                $limit = null;
            }
        }

        $statement = 'select ' . implode(', ', $fields) . ' from ' . implode(', ', $table);

        if (!isset($where) && !isset($cache)) {
            if (isset($order)) {
                $statement .= ' order by ' . implode(', ', $order);
            }

            return $this->query($statement);
        }

        if (isset($where)) {
            $statement .= ' where ';

            foreach (array_keys($where) as $c) {
                $statement .= $c . ' = :cond_' . $c . ' and ';
            }

            $statement = substr($statement, 0, -5);
        }

        if (isset($order)) {
            $statement .= ' order by ' . implode(', ', $order);
        }

        if (isset($limit)) {
            $statement .= ' limit ' . $limit;
        }

        $Q = $this->prepare($statement);

        if (isset($where)) {
            foreach ($where as $c => $v) {
                $Q->bindValue(':cond_' . $c, $v);
            }
        }

        if (isset($cache)) {
            if (!is_array($cache)) {
                $cache = [ $cache ];
            }

            call_user_func_array([$Q, 'setCache'], $cache);
        }

        $Q->execute();

        return $Q;
    }

    public function save($table, array $data, array $where_condition = null)
    {
        if (empty($data)) {
            return false;
        }

        if ((strlen($table) < 7) || (substr($table, 0, 7) != ':table_')) {
            $table = ':table_' . $table;
        }

        if (isset($where_condition)) {
            $statement = 'update ' . $table . ' set ';

            foreach ($data as $c => $v) {
                if ($v == 'now()' || $v == 'null') {
                    $statement .= $c . ' = ' . $v . ', ';
                } else {
                    $statement .= $c . ' = :new_' . $c . ', ';
                }
            }

            $statement = substr($statement, 0, -2) . ' where ';

            foreach (array_keys($where_condition) as $c) {
                $statement .= $c . ' = :cond_' . $c . ' and ';
            }

            $statement = substr($statement, 0, -5);

            $Q = $this->prepare($statement);

            foreach ($data as $c => $v) {
                if ($v != 'now()' && $v != 'null') {
                    $Q->bindValue(':new_' . $c, $v);
                }
            }

            foreach ($where_condition as $c => $v) {
                $Q->bindValue(':cond_' . $c, $v);
            }

            $Q->execute();

            return $Q->rowCount();
        } else {
            $is_prepared = false;

            $statement = 'insert into ' . $table . ' (' . implode(', ', array_keys($data)) . ') values (';

            foreach ($data as $c => $v) {
                if ($v == 'now()' || $v == 'null') {
                    $statement .= $v . ', ';
                } else {
                    if ($is_prepared === false) {
                        $is_prepared = true;
                    }

                    $statement .= ':' . $c . ', ';
                }
            }

            $statement = substr($statement, 0, -2) . ')';

            if ($is_prepared === true) {
                $Q = $this->prepare($statement);

                foreach ($data as $c => $v) {
                    if ($v != 'now()' && $v != 'null') {
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

    public function delete($table, array $where_condition)
    {
        if ((strlen($table) < 7) || (substr($table, 0, 7) != ':table_')) {
            $table = ':table_' . $table;
        }

        $statement = 'delete from ' . $table . ' where ';

        foreach (array_keys($where_condition) as $c) {
            $statement .= $c . ' = :cond_' . $c . ' and ';
        }

        $statement = substr($statement, 0, -5);

        $Q = $this->prepare($statement);

        foreach ($where_condition as $c => $v) {
            $Q->bindValue(':cond_' . $c, $v);
        }

        $Q->execute();

        return $Q->rowCount();
    }

    public function importSQL($sql_file, $table_prefix = null)
    {
        if (file_exists($sql_file)) {
            $import_queries = file_get_contents($sql_file);
        } else {
            trigger_error(sprintf(ERROR_SQL_FILE_NONEXISTENT, $sql_file));

            return false;
        }

        set_time_limit(0);

        $sql_queries = array();
        $sql_length = strlen($import_queries);
        $pos = strpos($import_queries, ';');

        for ($i = $pos; $i < $sql_length; $i++) {
// remove comments
            if (($import_queries[0] == '#') || (substr($import_queries, 0, 2) == '--')) {
                $import_queries = ltrim(substr($import_queries, strpos($import_queries, "\n")));
                $sql_length = strlen($import_queries);
                $i = strpos($import_queries, ';') - 1;
                continue;
            }

            if ($import_queries[($i+1)] == "\n") {
                $next = '';

                for ($j = ($i+2); $j < $sql_length; $j++) {
                    if (!empty($import_queries[$j])) {
                        $next = substr($import_queries, $j, 6);

                        if (($next[0] == '#') || (substr($next, 0, 2) == '--')) {
// find out where the break position is so we can remove this line (#comment line)
                            for ($k = $j; $k < $sql_length; $k++) {
                                if ($import_queries[$k] == "\n") {
                                    break;
                                }
                            }

                            $query = substr($import_queries, 0, $i + 1);

                            $import_queries = substr($import_queries, $k);

// join the query before the comment appeared, with the rest of the dump
                            $import_queries = $query . $import_queries;
                            $sql_length = strlen($import_queries);
                            $i = strpos($import_queries, ';') - 1;
                            continue 2;
                        }

                        break;
                    }
                }

                if (empty($next)) { // get the last insert query
                    $next = 'insert';
                }

                if ((strtoupper($next) == 'DROP T') ||
                (strtoupper($next) == 'CREATE') ||
                (strtoupper($next) == 'INSERT') ||
                (strtoupper($next) == 'ALTER ') ||
                (strtoupper($next) == 'SET FO')) {
                    $next = '';

                    $sql_query = substr($import_queries, 0, $i);

                    if (isset($table_prefix)) {
                        if (strtoupper(substr($sql_query, 0, 25)) == 'DROP TABLE IF EXISTS OSC_') {
                            $sql_query = 'DROP TABLE IF EXISTS ' . $table_prefix . substr($sql_query, 25);
                        } elseif (strtoupper(substr($sql_query, 0, 17)) == 'CREATE TABLE OSC_') {
                            $sql_query = 'CREATE TABLE ' . $table_prefix . substr($sql_query, 17);
                        } elseif (strtoupper(substr($sql_query, 0, 16)) == 'INSERT INTO OSC_') {
                            $sql_query = 'INSERT INTO ' . $table_prefix . substr($sql_query, 16);
                        } elseif (strtoupper(substr($sql_query, 0, 12)) == 'CREATE INDEX') {
                            $sql_query = substr($sql_query, 0, stripos($sql_query, ' on osc_')) .
                                         ' on ' .
                                         $table_prefix .
                                         substr($sql_query, stripos($sql_query, ' on osc_') + 8);
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

        foreach ($sql_queries as $q) {
            if ($this->exec($q) === false) {
                $error = true;

                break;
            }
        }

        return !$error;
    }

    public static function prepareInput($string)
    {
        if (is_string($string)) {
            return HTML::sanitize($string);
        } elseif (is_array($string)) {
            foreach ($string as $k => $v) {
                $string[$k] = static::prepareInput($v);
            }

            return $string;
        } else {
            return $string;
        }
    }

    public static function prepareIdentifier($string)
    {
        return '`' . str_replace('`', '``', $string) . '`';
    }

    protected function autoPrefixTables($statement)
    {
        $prefix = '';

        if (defined('DB_TABLE_PREFIX')) {
            $prefix = DB_TABLE_PREFIX;
        }

        $statement = str_replace(':table_', $prefix, $statement);

        return $statement;
    }
}
