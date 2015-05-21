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

    public static function inputField($name, $value = '', $parameters = '', $type = 'text', $reinsert_value = true, $class = 'form-control')
    {
        $field = '<input type="' . static::output($type) . '" name="' . static::output($name) . '"';

        if (($reinsert_value == true) && ((isset($_GET[$name]) && is_string($_GET[$name])) || (isset($_POST[$name]) && is_string($_POST[$name])))) {
            if (isset($_GET[$name]) && is_string($_GET[$name])) {
                $value = $_GET[$name];
            } elseif (isset($_POST[$name]) && is_string($_POST[$name])) {
                $value = $_POST[$name];
            }
        }

        if (!empty($value)) {
            $field .= ' value="' . static::output($value) . '"';
        }

        if (!empty($parameters)) {
            $field .= ' ' . $parameters;
        }

        if (!empty($class)) {
            $field .= ' class="' . $class . '"';
        }

        $field .= ' />';

        return $field;
    }

    public static function passwordField($name, $value = '', $parameters = 'maxlength="40"')
    {
        return static::inputField($name, $value, $parameters, 'password', false);
    }
}
