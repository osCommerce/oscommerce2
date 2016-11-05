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

class OnlineUpdate
{
    public static function log($message, $version)
    {
        if (FileSystem::isWritable(OSCOM::BASE_DIR . 'Work/OnlineUpdates/' . $version . '-log.txt')) {
            $message = '[' . date('d-M-Y H:i:s') . '] ' . trim($message) . "\n";

            file_put_contents(OSCOM::BASE_DIR . 'Work/OnlineUpdates/' . $version . '-log.txt', $message, FILE_APPEND);
        }
    }

    public static function resetLog($version)
    {
        if (static::logExists($version) && FileSystem::isWritable(OSCOM::BASE_DIR . 'Work/OnlineUpdates/' . $version . '-log.txt')) {
            unlink(OSCOM::BASE_DIR . 'Work/OnlineUpdates/' . $version . '-log.txt');
        }
    }

    public static function getLog($version)
    {
        $result = '';

        if (static::logExists($version)) {
            $result = file_get_contents(OSCOM::BASE_DIR . 'Work/OnlineUpdates/' . $version . '-log.txt');
        }

        return trim($result);
    }

    public static function logExists($version)
    {
        return is_file(OSCOM::BASE_DIR . 'Work/OnlineUpdates/' . $version . '-log.txt');
    }

    public static function getLogPath($version)
    {
        $result = '';

        if (static::logExists($version)) {
            $result = FileSystem::displayPath(OSCOM::BASE_DIR . 'Work/OnlineUpdates/' . $version . '-log.txt');
        }

        return $result;
    }
}
