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

class ErrorHandler
{
    public static function initialize()
    {
        ini_set('display_errors', false);
        ini_set('html_errors', false);
        ini_set('ignore_repeated_errors', true);

        if (FileSystem::isWritable(OSCOM::BASE_DIR . 'Work', true)) {
            if (!is_dir(OSCOM::BASE_DIR . 'Work/Logs')) {
                mkdir(OSCOM::BASE_DIR . 'Work/Logs', 0777, true);
            }

            ini_set('log_errors', true);
            ini_set('error_log', OSCOM::BASE_DIR . 'Work/Logs/errors-' . date('Ymd') . '.txt');
        }
    }

    public static function getDirectory()
    {
        return OSCOM::BASE_DIR . 'Work/Logs/';
    }
}
