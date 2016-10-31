<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license GPL; https://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Is;

class ip_address
{
    public static function execute($ip)
    {
        $ip = trim($ip);

        return !empty($ip) && filter_var($ip, FILTER_VALIDATE_IP, [
            'flags' => FILTER_FLAG_IPV4
        ]);
    }
}
