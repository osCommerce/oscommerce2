<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

use OSC\OM\FileSystem;
use OSC\OM\OSCOM;

class Cache
{
    private $data;
    private $key;

    public function write($data, $key = null)
    {
        if (!isset($key)) {
            $key = $this->key;
        }

        $key = basename($key);

        if (!static::hasSafeName($key)) {
            trigger_error('OSCOM_Cache::write(): Invalid key name (\'' . $key . '\'). Valid characters are a-zA-Z0-9-_');

            return false;
        }

        if (FileSystem::isWritable(OSCOM::BASE_DIR . 'Work/Cache')) {
            return file_put_contents(OSCOM::BASE_DIR . 'Work/Cache/' . $key . '.cache', serialize($data), LOCK_EX) !== false;
        }

        return false;
    }

    public function read($key, $expire = null)
    {
        $key = basename($key);

        if (!static::hasSafeName($key)) {
            trigger_error('OSCOM_Cache::read(): Invalid key name (\'' . $key . '\'). Valid characters are a-zA-Z0-9-_');

            return false;
        }

        $this->key = $key;

        $filename = OSCOM::BASE_DIR . 'Work/Cache/' . $key . '.cache';

        if (is_file($filename)) {
            $difference = floor((time() - filemtime($filename)) / 60);

            if (empty($expire) || (is_numeric($expire) && ($difference < $expire))) {
                $this->data = unserialize(file_get_contents($filename));

                return true;
            }
        }

        return false;
    }

    public function getCache()
    {
        return $this->data;
    }

    public static function hasSafeName($key)
    {
        return preg_match('/^[a-zA-Z0-9-_]+$/', $key) === 1;
    }

    public function startBuffer()
    {
        ob_start();
    }

    public function stopBuffer()
    {
        $this->data = ob_get_contents();

        ob_end_clean();

        $this->write($this->data);
    }

    public function getTime($key)
    {
        $key = basename($key);

        if (!static::hasSafeName($key)) {
            trigger_error('OSCOM_Cache::getTime(): Invalid key name (\'' . $key . '\'). Valid characters are a-zA-Z0-9-_');

            return false;
        }

        $filename = OSCOM::BASE_DIR . 'Work/Cache/' . $key . '.cache';

        if (is_file($filename)) {
            return filemtime($filename);
        }

        return false;
    }

    public static function exists($key, $strict = true)
    {
        $key = basename($key);

        if (!static::hasSafeName($key)) {
            trigger_error('OSCOM_Cache::exists(): Invalid key name (\'' . $key . '\'). Valid characters are a-zA-Z0-9-_');

            return false;
        }

        if (is_file(OSCOM::BASE_DIR . 'Work/Cache/' . $key . '.cache')) {
            return true;
        }

        if ($strict === false) {
            $key_length = strlen($key);

            $d = dir(OSCOM::BASE_DIR . 'Work/Cache/');

            while (($entry = $d->read()) !== false) {
                if ((strlen($entry) >= $key_length) && (substr($entry, 0, $key_length) == $key)) {
                    $d->close();

                    return true;
                }
            }
        }

        return false;
    }

    public static function clear($key)
    {
        $key = basename($key);

        if (!static::hasSafeName($key)) {
            trigger_error('OSCOM_Cache::clear(): Invalid key name (\'' . $key . '\'). Valid characters are a-zA-Z0-9-_');

            return false;
        }

        if (FileSystem::isWritable(OSCOM::BASE_DIR . 'Work/Cache')) {
            $key_length = strlen($key);

            $d = dir(OSCOM::BASE_DIR . 'Work/Cache');

            while (($entry = $d->read()) !== false) {
                if ((strlen($entry) >= $key_length) && (substr($entry, 0, $key_length) == $key)) {
                    @unlink(OSCOM::BASE_DIR . 'Work/Cache/' . $entry);
                }
            }

            $d->close();
        }
    }
}
