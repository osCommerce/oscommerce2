<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Db;

class MySQL extends \OSC\OM\Db
{
    public function __construct($server, $username, $password, $database, $port, $driver_options)
    {
        $this->server = $server;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->port = $port;
        $this->driver_options = $driver_options;

        return $this->connect();
    }

    public function connect()
    {
        $dsn_array = [];

        if (!empty($this->database)) {
            $dsn_array[] = 'dbname=' . $this->database;
        }

        if ((strpos($this->server, '/') !== false) || (strpos($this->server, '\\') !== false)) {
            $dsn_array[] = 'unix_socket=' . $this->server;
        } else {
            $dsn_array[] = 'host=' . $this->server;

            if (!empty($this->port)) {
                $dsn_array[] = 'port=' . $this->port;
            }
        }

        $dsn_array[] = 'charset=utf8';

        $dsn = 'mysql:' . implode(';', $dsn_array);

        $this->connected = true;

        $dbh = parent::__construct($dsn, $this->username, $this->password, $this->driver_options);

        return $dbh;
    }
}
