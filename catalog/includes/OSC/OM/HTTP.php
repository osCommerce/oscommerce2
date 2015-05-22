<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

class HTTP
{
    public static function redirect($url)
    {
        if ((strstr($url, "\n") === false) && (strstr($url, "\r") === false)) {
            if ( strpos($url, '&amp;') !== false ) {
                $url = str_replace('&amp;', '&', $url);
            }

            header('Location: ' . $url);
        }

        exit;
    }
}
