<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Modules;

interface ContentInterface
{
    public function execute();
    public function isEnabled();
    public function check();
    public function install();
    public function remove();
    public function keys();
}
