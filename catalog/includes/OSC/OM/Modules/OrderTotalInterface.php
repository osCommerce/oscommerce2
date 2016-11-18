<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\OM\Modules;

interface OrderTotalInterface
{
    public function process();
    public function check();
    public function install();
    public function remove();
    public function keys();
}
