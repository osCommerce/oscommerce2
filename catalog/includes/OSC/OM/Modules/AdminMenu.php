<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
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
        list($app, $code) = explode('\\', $module, 2);

        $info = Apps::getInfo($app);

        if (isset($info['modules'][$this->code][$code])) {
            return $this->ns . $app . '\\' . $info['modules'][$this->code][$code];
        }
    }
}
