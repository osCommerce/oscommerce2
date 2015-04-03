<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

class HTML
{
    public static function output($string, $translate = null)
    {
        if (!isset($translate)) {
            $translate = [
                '"' => '&quot;'
            ];
        }

        return strtr(trim($string), $translate);
    }

    public static function outputProtected($string)
    {
        return htmlspecialchars(trim($string));
    }

    public static function sanitize($string)
    {
        $patterns = [
            '/ +/',
            '/[<>]/'
        ];

        $replace = [
            ' ',
            '_'
        ];

        return preg_replace($patterns, $replace, trim($string));
    }
}
