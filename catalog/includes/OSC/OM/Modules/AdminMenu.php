<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\OM\Modules;

use OSC\OM\Apps;

class AdminMenu extends \OSC\OM\ModulesAbstract
{
    public function getInfo($app, $key, $data)
    {
        $result = [];

        $class = $this->ns . $app . '\\' . $data;

        if (is_subclass_of($class, 'OSC\OM\Modules\\' . $this->code . 'Interface')) {
            $result[$app . '\\' . $key] = $class;
        }

        return $result;
    }

    public function getClass($module)
    {
        list($vendor, $app, $code) = explode('\\', $module, 3);

        $info = Apps::getInfo($vendor . '\\' . $app);

        if (isset($info['modules'][$this->code][$code])) {
            return $this->ns . $vendor . '\\' . $app . '\\' . $info['modules'][$this->code][$code];
        }
    }
}
