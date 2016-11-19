<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\OM;

use OSC\OM\OSCOM;
use OSC\OM\Registry;

class Apps
{
    public static function getAll()
    {
        $result = [];

        $apps_directory = OSCOM::BASE_DIR . 'Apps';

        if ($vdir = new \DirectoryIterator($apps_directory)) {
            foreach ($vdir as $vendor) {
                if (!$vendor->isDot() && $vendor->isDir()) {
                    if ($adir = new \DirectoryIterator($vendor->getPath() . '/' . $vendor->getFilename())) {
                        foreach ($adir as $app) {
                            if (!$app->isDot() && $app->isDir() && static::exists($vendor->getFilename() . '\\' . $app->getFilename())) {
                                if (($json = static::getInfo($vendor->getFilename() . '\\' . $app->getFilename())) !== false) {
                                    $result[] = $json;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    public static function getModules($type, $filter_vendor_app = null, $filter = null)
    {
        $result = [];

        if (!Registry::exists('ModuleType' . $type)) {
            $class = 'OSC\OM\Modules\\' . $type;

            if (!class_exists($class)) {
                trigger_error('OSC\OM\Apps::getModules(): ' . $type . ' module class not found in OSC\OM\Modules\\');

                return $result;
            }

            Registry::set('ModuleType' . $type, new $class());
        }

        $OSCOM_Type = Registry::get('ModuleType' . $type);

        $filter_vendor = $filter_app = null;

        if (isset($filter_vendor_app)) {
            if (strpos($filter_vendor_app, '\\') !== false) {
                list($filter_vendor, $filter_app) = explode('\\', $filter_vendor_app, 2);
            } else {
                $filter_vendor = $filter_vendor_app;
            }
        }

        $vendor_directory = OSCOM::BASE_DIR . 'Apps';

        if (is_dir($vendor_directory)) {
            if ($vdir = new \DirectoryIterator($vendor_directory)) {
                foreach ($vdir as $vendor) {
                    if (!$vendor->isDot() && $vendor->isDir() && (!isset($filter_vendor) || ($vendor->getFilename() == $filter_vendor))) {
                        if ($adir = new \DirectoryIterator($vendor->getPath() . '/' . $vendor->getFilename())) {
                            foreach ($adir as $app) {
                                if (!$app->isDot() && $app->isDir() && (!isset($filter_app) || ($app->getFilename() == $filter_app)) && static::exists($vendor->getFilename() . '\\' . $app->getFilename()) && (($json = static::getInfo($vendor->getFilename() . '\\' . $app->getFilename())) !== false)) {
                                    if (isset($json['modules'][$type])) {
                                        $modules = $json['modules'][$type];

                                        if (isset($filter)) {
                                            $modules = $OSCOM_Type->filter($modules, $filter);
                                        }

                                        foreach ($modules as $key => $data) {
                                            $result = array_merge($result, $OSCOM_Type->getInfo($vendor->getFilename() . '\\' . $app->getFilename(), $key, $data));
                                        }
                                    }
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
        if (strpos($app, '\\') !== false) {
            list($vendor, $app) = explode('\\', $app, 2);

            if (class_exists('OSC\Apps\\' . $vendor . '\\' . $app . '\\' . $app)) {
                if (is_subclass_of('OSC\Apps\\' . $vendor . '\\' . $app . '\\' . $app, 'OSC\OM\AppAbstract')) {
                    return true;
                } else {
                    trigger_error('OSC\OM\Apps::exists(): ' . $vendor . '\\' . $app . ' - App is not a subclass of OSC\OM\AppAbstract and cannot be loaded.');
                }
            }
        } else {
            trigger_error('OSC\OM\Apps::exists(): ' . $app . ' - Invalid format, must be: Vendor\App.');
        }

        return false;
    }

    public static function getModuleClass($module, $type)
    {
        if (!Registry::exists('ModuleType' . $type)) {
            $class = 'OSC\OM\Modules\\' . $type;

            if (!class_exists($class)) {
                trigger_error('OSC\OM\Apps::getModuleClass(): ' . $type . ' module class not found in OSC\OM\Modules\\');

                return false;
            }

            Registry::set('ModuleType' . $type, new $class());
        }

        $OSCOM_Type = Registry::get('ModuleType' . $type);

        return $OSCOM_Type->getClass($module);
    }

    public static function getInfo($app)
    {
        if (strpos($app, '\\') !== false) {
            list($vendor, $app) = explode('\\', $app, 2);

            $metafile = OSCOM::BASE_DIR . 'Apps/' . basename($vendor) . '/' . basename($app) . '/oscommerce.json';

            if (is_file($metafile) && (($json = json_decode(file_get_contents($metafile), true)) !== null)) {
                return $json;
            }

            trigger_error('OSC\OM\Apps::getInfo(): ' . $vendor . '\\' . $app . ' - Could not read App information in ' . $metafile . '.');
        } else {
            trigger_error('OSC\OM\Apps::getInfo(): ' . $app . ' - Invalid format, must be: Vendor\App.');
        }

        return false;
    }

    public static function getRouteDestination($route = null, $filter_vendor_app = null)
    {
        if (empty($route)) {
            $route = array_keys($_GET);
        }

        $result = $routes = [];

        if (empty($route)) {
            return $result;
        }

        $filter_vendor = $filter_app = null;

        if (isset($filter_vendor_app)) {
            if (strpos($filter_vendor_app, '\\') !== false) {
                list($filter_vendor, $filter_app) = explode('\\', $filter_vendor_app, 2);
            } else {
                $filter_vendor = $filter_vendor_app;
            }
        }

        $vendor_directory = OSCOM::BASE_DIR . 'Apps';

        if (is_dir($vendor_directory)) {
            if ($vdir = new \DirectoryIterator($vendor_directory)) {
                foreach ($vdir as $vendor) {
                    if (!$vendor->isDot() && $vendor->isDir() && (!isset($filter_vendor) || ($vendor->getFilename() == $filter_vendor))) {
                        if ($adir = new \DirectoryIterator($vendor->getPath() . '/' . $vendor->getFilename())) {
                            foreach ($adir as $app) {
                                if (!$app->isDot() && $app->isDir() && (!isset($filter_app) || ($app->getFilename() == $filter_app)) && static::exists($vendor->getFilename() . '\\' . $app->getFilename()) && (($json = static::getInfo($vendor->getFilename() . '\\' . $app->getFilename())) !== false)) {
                                    if (isset($json['routes'][OSCOM::getSite()])) {
                                        $routes[$json['vendor'] . '\\' . $json['app']] = $json['routes'][OSCOM::getSite()];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return call_user_func([
            'OSC\Sites\\' . OSCOM::getSite() . '\\' . OSCOM::getSite(),
            'resolveRoute'
        ], $route, $routes);
    }
}
