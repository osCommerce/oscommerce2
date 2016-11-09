<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\OM\Module\Hooks\Shop\Session;

use OSC\OM\Hash;

class Recreated
{
    public function execute($parameters) {
// reset session token
        $_SESSION['sessiontoken'] = md5(Hash::getRandomInt() . Hash::getRandomInt() . Hash::getRandomInt() . Hash::getRandomInt());
    }
}
