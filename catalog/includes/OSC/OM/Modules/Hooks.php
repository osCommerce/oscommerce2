<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\OM\Modules;

use OSC\OM\Apps;

class Hooks extends \OSC\OM\ModulesAbstract
{
    public function getInfo($app, $key, $data)
    {
        $result = [];

        foreach ($data as $code => $class) {
            $class = $this->ns . $app . '\\' . $class;

            if (is_subclass_of($class, 'OSC\OM\Modules\\' . $this->code . 'Interface')) {
                $result[$app . '\\' . $key . '\\' . $code] = $class;
            }
        }

        return $result;
    }

    public function getClass($module)
    {
        if (strpos($module, '/') === false) { // TODO core hook compatibility; to remove
            return $module;
        }

        list($vendor, $app, $group, $code) = explode('\\', $module, 4);

        $info = Apps::getInfo($vendor . '\\' . $app);

        if (isset($info['modules'][$this->code][$group][$code])) {
            return $this->ns . $vendor . '\\' . $app . '\\' . $info['modules'][$this->code][$group][$code];
        }
    }

    public function filter($modules, $filter)
    {
        $result = [];

        foreach ($modules as $key => $data) {
            if (($key == $filter['site'] . '/' . $filter['group']) && isset($data[$filter['hook']])) {
                $result[$key] = $data;
            }
        }

        return $result;
    }
}
