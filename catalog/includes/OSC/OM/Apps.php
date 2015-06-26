<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

use OSC\OM\OSCOM;

class Apps
{
    protected static $modules_map = [
        'adminDashboard' => 'OSC\OM\ModuleAdminDashboardInterface',
        'adminMenu' => 'OSC\OM\ModuleAdminMenuInterface'
    ];

    public static function getModules($type, $app = null)
    {
        $result = [];

        $directory = OSCOM::BASE_DIR . 'apps';

        if (file_exists($directory)) {
            if ($dir = new \DirectoryIterator($directory)) {
                foreach ($dir as $file) {
                    if (!$file->isDot() && $file->isDir() && (!isset($app) || ($file->getFilename() == $app)) && static::exists($file->getFilename()) && (($json = static::getInfo($file->getFilename())) !== false)) {
                        if (isset($json['modules'][$type])) {
                            foreach ($json['modules'][$type] as $k => $v) {
                                $class = 'OSC\OM\Apps\\' . $file->getFilename() . '\\' . $v;

                                if (!isset(static::$modules_map[$type]) || is_subclass_of($class, static::$modules_map[$type])) {
                                    $result[$file->getFilename() . '\\' . $k] = 'OSC\OM\Apps\\' . $file->getFilename() . '\\' . $v;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    public static function exists($app)
    {
        $app = basename($app);

        if (file_exists(OSCOM::BASE_DIR . 'apps/' . $app . '/' . $app . '.php')) {
            if (is_subclass_of('OSC\OM\Apps\\' . $app . '\\' . $app, 'OSC\OM\AppAbstract')) {
                return true;
            } else {
                trigger_error('OSC\OM\Apps::exists(): ' . $app . ' - App is not a subclass of OSC\OM\AppAbstract and cannot be loaded.');
            }
        } else {
            trigger_error('OSC\OM\Apps::exists(): ' . $app . ' - App class does not exist.');
        }

        return false;
    }

    public static function getModuleClass($module, $type)
    {
        list($app, $code) = explode('\\', $module, 2);

        $info = static::getInfo($app);

        if (isset($info['modules'][$type])) {
            if (isset($info['modules'][$type][$code])) {
                return 'OSC\OM\Apps\\' . $app . '\\' . $info['modules'][$type][$code];
            }
        }

        return false;
    }

    public static function getInfo($app)
    {
        $app = basename($app);

        if (!file_exists(OSCOM::BASE_DIR . 'apps/' . $app . '/oscommerce.json') || (($json = @json_decode(file_get_contents(OSCOM::BASE_DIR . 'apps/' . $app . '/oscommerce.json'), true)) === null)) {
            trigger_error('OSC\OM\Apps::getInfo(): ' . $app . ' - Could not read App information in ' . OSCOM::BASE_DIR . 'apps/' . $app . '/oscommerce.json.');

            return false;
        }

        return $json;
    }
}
