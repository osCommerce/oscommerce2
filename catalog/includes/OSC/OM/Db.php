<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\OM;

use OSC\OM\HTML;
use OSC\OM\OSCOM;

class Db extends \PDO
{
    protected $connected = false;
    protected $server;
    protected $username;
    protected $password;
    protected $database;
    protected $table_prefix;
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
            $server = OSCOM::getConfig('db_server');
        }

        if (!isset($username)) {
            $username = OSCOM::getConfig('db_server_username');
        }

        if (!isset($password)) {
            $password = OSCOM::getConfig('db_server_password');
        }

        if (!isset($database)) {
            $database = OSCOM::getConfig('db_database');
        }

        if (!isset($driver_options[\PDO::ATTR_ERRMODE])) {
            $driver_options[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
        }

        if (!isset($driver_options[\PDO::ATTR_DEFAULT_FETCH_MODE])) {
            $driver_options[\PDO::ATTR_DEFAULT_FETCH_MODE] = \PDO::FETCH_ASSOC;
        }

        if (!isset($driver_options[\PDO::ATTR_STATEMENT_CLASS])) {
            $driver_options[\PDO::ATTR_STATEMENT_CLASS] = array('OSC\OM\DbStatement');
        }

        $class = 'OSC\OM\Db\MySQL';
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

        if ($DbStatement !== false) {
            $DbStatement->setQueryCall('query');
            $DbStatement->setPDO($this);
        }

        return $DbStatement;
    }

    public function get($table, $fields, array $where = null, $order = null, $limit = null, $cache = null, array $options = null)
    {
        if (!is_array($table)) {
            $table = [ $table ];
        }

        if (!isset($options['prefix_tables']) || ($options['prefix_tables'] === true)) {
            array_walk($table, function(&$v, &$k) {
                if ((strlen($v) < 7) || (substr($v, 0, 7) != ':table_')) {
                    $v = ':table_' . $v;
                }
            });
        }

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

            $counter = 0;

            $it_where = new \CachingIterator(new \ArrayIterator($where), \CachingIterator::TOSTRING_USE_CURRENT);

            foreach ($it_where as $key => $value) {
                if (is_array($value)) {
                    if (isset($value['val'])) {
                        $statement .= $key . ' ' . (isset($value['op']) ? $value['op'] : '=') . ' :cond_' . $counter;
                    }

                    if (isset($value['rel'])) {
                        if (isset($value['val'])) {
                            $statement .= ' and ';
                        }

                        if (is_array($value['rel'])) {
                            $it_rel = new \CachingIterator(new \ArrayIterator($value['rel']), \CachingIterator::TOSTRING_USE_CURRENT);

                            foreach ($it_rel as $rel) {
                                $statement .= $key . ' = ' . $rel;

                                if ($it_rel->hasNext()) {
                                    $statement .= ' and ';
                                }
                            }
                        } else {
                            $statement .= $key . ' = ' . $value['rel'];
                        }
                    }
                } else {
                    $statement .= $key . ' = :cond_' . $counter;
                }

                if ($it_where->hasNext()) {
                    $statement .= ' and ';
                }

                $counter++;
            }
        }

        if (isset($order)) {
            $statement .= ' order by ' . implode(', ', $order);
        }

        if (isset($limit)) {
            $statement .= ' limit ' . $limit;
        }

        $Q = $this->prepare($statement);

        if (isset($where)) {
            $counter = 0;

            foreach ($it_where as $value) {
                if (is_array($value)) {
                    if (isset($value['val'])) {
                        $Q->bindValue(':cond_' . $counter, $value['val']);
                    }
                } else {
                    $Q->bindValue(':cond_' . $counter, $value);
                }

                $counter++;
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
                if (is_null($v)) {
                    $v = 'null';
                }

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
                if ($v != 'now()' && $v != 'null' && !is_null($v)) {
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
                if (is_null($v)) {
                    $v = 'null';
                }

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
                    if ($v != 'now()' && $v != 'null' && !is_null($v)) {
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

    public function delete($table, array $where_condition = [])
    {
        if ((strlen($table) < 7) || (substr($table, 0, 7) != ':table_')) {
            $table = ':table_' . $table;
        }

        $statement = 'delete from ' . $table;

        if (empty($where_condition)) {
            return $this->exec($statement);
        }

        $statement .= ' where ';

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
        if (is_file($sql_file)) {
            $import_queries = file_get_contents($sql_file);
        } else {
            trigger_error('OSC\OM\Db::importSQL(): SQL file does not exist: ' . $sql_file);

            return false;
        }

        set_time_limit(0);

        $sql_queries = array();
        $sql_length = strlen($import_queries);
        $pos = strpos($import_queries, ';');

        for ($i = $pos; $i < $sql_length; $i++) {
// remove comments
            if ((substr($import_queries, 0, 1) == '#') || (substr($import_queries, 0, 2) == '--')) {
                $import_queries = ltrim(substr($import_queries, strpos($import_queries, "\n")));
                $sql_length = strlen($import_queries);
                $i = strpos($import_queries, ';') - 1;
                continue;
            }

            if (substr($import_queries, $i + 1, 1) == "\n") {
                $next = '';

                for ($j = ($i+2); $j < $sql_length; $j++) {
                    if (!empty(substr($import_queries, $j, 1))) {
                        $next = substr($import_queries, $j, 6);

                        if ((substr($next, 0, 1) == '#') || (substr($next, 0, 2) == '--')) {
// find out where the break position is so we can remove this line (#comment line)
                            for ($k = $j; $k < $sql_length; $k++) {
                                if (substr($import_queries, $k, 1) == "\n") {
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

                    if (isset($table_prefix) && !empty($table_prefix)) {
                        if (strtoupper(substr($sql_query, 0, 20)) == 'DROP TABLE IF EXISTS') {
                            $sql_query = 'DROP TABLE IF EXISTS ' . $table_prefix . substr($sql_query, 21);
                        } elseif (strtoupper(substr($sql_query, 0, 12)) == 'CREATE TABLE') {
                            $sql_query = 'CREATE TABLE ' . $table_prefix . substr($sql_query, 13);
                        } elseif (strtoupper(substr($sql_query, 0, 11)) == 'INSERT INTO') {
                            $sql_query = 'INSERT INTO ' . $table_prefix . substr($sql_query, 12);
                        } elseif (strtoupper(substr($sql_query, 0, 12)) == 'CREATE INDEX') {
                            $sql_query = substr($sql_query, 0, stripos($sql_query, ' on ')) .
                                         ' on ' .
                                         $table_prefix .
                                         substr($sql_query, stripos($sql_query, ' on ') + 4);
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

    public static function getSchemaFromFile($file)
    {
        $table = substr(basename($file), 0, strrpos(basename($file), '.'));

        $schema = [
            'name' => $table
        ];

        $is_index = $is_foreign = $is_property = false;

        foreach (file($file) as $row) {
            $row = trim($row);

            if (!empty($row)) {
                if ($row == '--') {
                    $is_index = true;
                    $is_foreign = $is_property = false;

                    continue;
                } elseif ($row == '==') {
                    $is_foreign = true;
                    $is_index = $is_property = false;

                    continue;
                } elseif ($row == '##') {
                    $is_property = true;
                    $is_index = $is_foreign = false;

                    continue;
                }

                $details = str_getcsv($row, ' ');

                $field_name = array_shift($details);

                if ($is_index === true) {
                    $schema['index'][$field_name] = $details;

                    continue;
                } elseif ($is_foreign === true) {
                    foreach ($details as $d) {
                        if (strpos($d, '(') === false) {
                            $schema['foreign'][$field_name]['col'][] = $d;

                            continue;
                        }

                        if (preg_match('/(.*)\((.*)\)/', $d, $info)) {
                            switch ($info[1]) {
                                case 'ref_table':
                                case 'on_delete':
                                case 'on_update':
                                case 'prefix':
                                    $schema['foreign'][$field_name][$info[1]] = $info[2];

                                    break;

                                case 'ref_col':
                                    $schema['foreign'][$field_name]['ref_col'] = explode(' ', $info[2]);
                                    break;
                            }
                        }
                    }

                    continue;
                } elseif ($is_property === true) {
                    switch ($field_name) {
                        case 'engine':
                            $schema['property']['engine'] = implode(' ', $details);
                            break;

                        case 'character_set':
                            $schema['property']['character_set'] = implode(' ', $details);
                            break;

                        case 'collate':
                            $schema['property']['collate'] = implode(' ', $details);
                            break;
                    }

                    continue;
                }

                $field_type = array_shift($details);

                if (preg_match('/(.*)\((.*)\)/', $field_type, $type_details)) {
                    $schema['col'][$field_name]['type'] = $type_details[1];
                    $schema['col'][$field_name]['length'] = $type_details[2];
                } else {
                    $schema['col'][$field_name]['type'] = $field_type;
                }

                if (preg_match('/default\((.*)\)/', implode(' ', $details), $type_default)) {
                    $schema['col'][$field_name]['default'] = $type_default[1];

                    $default_pos = array_search('default(' . $type_default[1] . ')', $details);
                    array_splice($details, $default_pos, 1);
                }

                $is_binary = array_search('binary', $details);

                if (is_integer($is_binary)) {
                    array_splice($details, $is_binary, 1);
                    $schema['col'][$field_name]['binary'] = true;
                }

                $is_unsigned = array_search('unsigned', $details);

                if (is_integer($is_unsigned)) {
                    array_splice($details, $is_unsigned, 1);
                    $schema['col'][$field_name]['unsigned'] = true;
                }

                $is_not_null = array_search('not_null', $details);

                if (is_integer($is_not_null)) {
                    array_splice($details, $is_not_null, 1);
                    $schema['col'][$field_name]['not_null'] = true;
                }

                $is_auto_increment = array_search('auto_increment', $details);

                if (is_integer($is_auto_increment)) {
                    array_splice($details, $is_auto_increment, 1);
                    $schema['col'][$field_name]['auto_increment'] = true;
                }

                if (!empty($details)) {
                    $schema['col'][$field_name]['other'] = implode(' ', $details);
                }
            }
        }

        return $schema;
    }

    public static function getSqlFromSchema($schema, $prefix = null)
    {
        $sql = 'CREATE TABLE ' . (isset($prefix) ? $prefix : '') . $schema['name'] . ' (' . "\n";

        $rows = [];

        foreach ($schema['col'] as $name => $fields) {
            $row = '  ' . $name . ' ' . $fields['type'];

            if (isset($fields['length'])) {
                $row .= '(' . $fields['length'] . ')';
            }

            if (isset($fields['binary']) && ($fields['binary'] === true)) {
                $row .= ' binary';
            }

            if (isset($fields['unsigned']) && ($fields['unsigned'] === true)) {
                $row .= ' unsigned';
            }

            if (isset($fields['default'])) {
                $row .= ' DEFAULT ' . $fields['default'];
            }

            if (isset($fields['not_null']) && ($fields['not_null'] === true)) {
                $row .= ' NOT NULL';
            }

            if (isset($fields['auto_increment']) && ($fields['auto_increment'] === true)) {
                $row .= ' auto_increment';
            }

            $rows[] = $row;
        }

        if (isset($schema['index'])) {
            foreach ($schema['index'] as $name => $fields) {
                if ($name == 'primary') {
                    $name = 'PRIMARY KEY';
                } else {
                    $name = 'KEY ' . $name;
                }

                $row = '  ' . $name . ' (' . implode(', ', $fields) . ')';

                $rows[] = $row;
            }
        }

        if (isset($schema['foreign'])) {
            foreach ($schema['foreign'] as $name => $fields) {
                $row = '  FOREIGN KEY ' . $name . ' (' . implode(', ', $fields['col']) . ') REFERENCES ' . (isset($prefix) && (!isset($fields['prefix']) || ($fields['prefix'] != 'false')) ? $prefix : '') . $fields['ref_table'] . '(' . implode(', ', $fields['ref_col']) . ')';

                if (isset($fields['on_update'])) {
                    $row .= ' ON UPDATE ' . strtoupper($fields['on_update']);
                }

                if (isset($fields['on_delete'])) {
                    $row .= ' ON DELETE ' . strtoupper($fields['on_delete']);
                }

                $rows[] = $row;
            }
        }

        $sql .= implode(',' . "\n", $rows) . "\n" . ')';

        if (isset($schema['property'])) {
            if (isset($schema['property']['engine'])) {
                $sql .= ' ENGINE ' . $schema['property']['engine'];
            }

            if (isset($schema['property']['character_set'])) {
                $sql .= ' CHARACTER SET ' . $schema['property']['character_set'];
            }

            if (isset($schema['property']['collate'])) {
                $sql .= ' COLLATE ' . $schema['property']['collate'];
            }
        }

        $sql .= ';';

        return $sql;
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

    public function setTablePrefix($prefix)
    {
        $this->table_prefix = $prefix;
    }

    protected function autoPrefixTables($statement)
    {
        $prefix = '';

        if (isset($this->table_prefix)) {
            $prefix = $this->table_prefix;
        } elseif (OSCOM::configExists('db_table_prefix')) {
            $prefix = OSCOM::getConfig('db_table_prefix');
        }

        $statement = str_replace(':table_', $prefix, $statement);

        return $statement;
    }
}
