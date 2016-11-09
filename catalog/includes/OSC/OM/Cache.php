<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\OM;

use OSC\OM\FileSystem;
use OSC\OM\OSCOM;

class Cache
{
    protected static $path;

    protected $key;
    protected $data;

    public function __construct($key)
    {
        $this->setPath();

        $this->setKey($key);
    }

    public function setKey($key)
    {
        if (!$this->hasSafeName($key)) {
            trigger_error('OSC\\OM\\Cache: Invalid key name (\'' . $key . '\'). Valid characters are a-zA-Z0-9-_');

            return false;
        }

        $this->key = $key;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function save($data)
    {
        if (FileSystem::isWritable(static::$path)) {
            return file_put_contents(static::$path . $this->key . '.cache', serialize($data), LOCK_EX) !== false;
        }

        return false;
    }

    public function exists($expire = null)
    {
        $filename = static::$path . $this->key . '.cache';

        if (is_file($filename)) {
            if (!isset($expire)) {
                return true;
            }

            $difference = floor((time() - filemtime($filename)) / 60);

            if (is_numeric($expire) && ($difference < $expire)) {
                return true;
            }
        }

        return false;
    }

    public function get()
    {
        $filename = static::$path . $this->key . '.cache';

        if (is_file($filename)) {
            $this->data = unserialize(file_get_contents($filename));
        }

        return $this->data;
    }

    public static function hasSafeName($key)
    {
        return preg_match('/^[a-zA-Z0-9-_]+$/', $key) === 1;
    }

    public function getTime()
    {
        $filename = static::$path . $this->key . '.cache';

        if (is_file($filename)) {
            return filemtime($filename);
        }

        return false;
    }

    public static function find($key, $strict = true)
    {
        if (!static::hasSafeName($key)) {
            trigger_error('OSC\\OM\\Cache::find(): Invalid key name (\'' . $key . '\'). Valid characters are a-zA-Z0-9-_');

            return false;
        }

        if (is_file(static::$path . $key . '.cache')) {
            return true;
        }

        if ($strict === false) {
            $key_length = strlen($key);

            $d = dir(static::$path);

            while (($entry = $d->read()) !== false) {
                if ((strlen($entry) >= $key_length) && (substr($entry, 0, $key_length) == $key)) {
                    $d->close();

                    return true;
                }
            }
        }

        return false;
    }

    public static function setPath()
    {
        static::$path = OSCOM::BASE_DIR . 'Work/Cache/';
    }

    public static function getPath()
    {
        if (!isset(static::$path)) {
            static::setPath();
        }

        return static::$path;
    }

    public static function clear($key)
    {
        if (!static::hasSafeName($key)) {
            trigger_error('OSC\\OM\\Cache::clear(): Invalid key name (\'' . $key . '\'). Valid characters are a-zA-Z0-9-_');

            return false;
        }

        if (FileSystem::isWritable(static::$path)) {
            foreach (glob(static::$path . $key . '*.cache') as $c) {
                unlink($c);
            }
        }
    }

    public static function clearAll()
    {
        if (FileSystem::isWritable(static::$path)) {
            foreach (glob(static::$path . '*.cache') as $c) {
                unlink($c);
            }
        }
    }
}
