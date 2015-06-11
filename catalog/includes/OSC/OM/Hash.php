<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

use OSC\OM\OSCOM;

class Hash
{
    public static function encrypt($plain)
    {
        if (!class_exists('PasswordHash', false)) {
            include(OSCOM::BASE_DIR . 'classes/passwordhash.php');
        }

        $hasher = new \PasswordHash(10, true);

        return $hasher->HashPassword($plain);
    }
}
