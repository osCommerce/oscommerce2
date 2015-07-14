<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Sites\Admin;

use OSC\OM\Apps;

class Admin extends \OSC\OM\SitesAbstract
{
    public $default_page = 'Dashboard';

    protected function init()
    {
    }

    public function setPage()
    {
        $page_code = $this->default_page;

        $class = 'OSC\Sites\\' . $this->code . '\Pages\\' . $page_code . '\\' . $page_code;

        if (!empty($_GET)) {
            $req = basename(array_keys($_GET)[0]);

            if (($req == 'A') && (count($_GET) > 1)) {
                $app = basename(array_keys($_GET)[1]);

                if (Apps::exists($app) && ($page = Apps::getRouteDestination(null, $app)) !== null) {
// get controller class name from namespace
                    $page_namespace = explode('\\', $page);
                    $page_code = $page_namespace[count($page_namespace)-1];

                    if (class_exists('OSC\OM\Apps\\' . $app . '\\' . $page . '\\' . $page_code)) {
                        $this->app = $app;
                        $this->route = $app . '\\' . $page;
                        $this->actions_index = 2;

                        $class = 'OSC\OM\Apps\\' . $this->app . '\\' . $page . '\\' . $page_code;
                    }
                }
            } else {
                if (class_exists('OSC\Sites\\' . $this->code . '\Pages\\' . $req . '\\' . $req)) {
                    $page_code = $req;

                    $class = 'OSC\Sites\\' . $this->code . '\Pages\\' . $page_code . '\\' . $page_code;
                }
            }
        }

        if (is_subclass_of($class, 'OSC\OM\PagesInterface')) {
            $this->page = new $class($this);

            $this->page->runActions();
        } else {
            trigger_error('OSC\Sites\Admin\Admin::setPage() - ' . $page_code . ': Page does not implement OSC\OM\PagesInterface and cannot be loaded.');
        }
    }

    public static function resolveRoute(array $route, array $routes)
    {
        return array_values($routes)[0];
    }
}
