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

    public static function form($name, $action, $method = 'post', $parameters = '', $tokenize = false)
    {
        $form = '<form name="' . static::output($name) . '" action="' . static::output($action) . '" method="' . static::output($method) . '"';

        if (!empty($parameters)) {
            $form .= ' ' . $parameters;
        }

        $form .= '>';

        if (($tokenize == true) && isset($_SESSION['sessiontoken'])) {
            $form .= static::hiddenField('formid', $_SESSION['sessiontoken']);
        }

        return $form;
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

    protected static function selectionField($name, $type, $value = '', $checked = false, $parameters = '')
    {
        $selection = '<input type="' . static::output($type) . '" name="' . static::output($name) . '"';

        if (!empty($value)) {
            $selection .= ' value="' . static::output($value) . '"';
        }

        if (($checked == true) || (isset($_GET[$name]) && is_string($_GET[$name]) && (($_GET[$name] == 'on') || ($_GET[$name] == $value))) || (isset($_POST[$name]) && is_string($_POST[$name]) && (($_POST[$name] == 'on') || ($_POST[$name] == $value)))) {
            $selection .= ' checked="checked"';
        }

        if (!empty($parameters)) {
            $selection .= ' ' . $parameters;
        }

        $selection .= ' />';

        return $selection;
    }

    public static function checkboxField($name, $value = '', $checked = false, $parameters = '')
    {
        return static::selectionField($name, 'checkbox', $value, $checked, $parameters);
    }

    public static function radioField($name, $value = '', $checked = false, $parameters = '')
    {
        return static::selectionField($name, 'radio', $value, $checked, $parameters);
    }

    public static function textareaField($name, $width, $height, $text = '', $parameters = '', $reinsert_value = true, $class = 'form-control')
    {
        $field = '<textarea name="' . static::output($name) . '" cols="' . static::output($width) . '" rows="' . static::output($height) . '"';

        if (!empty($parameters)) {
            $field .= ' ' . $parameters;
        }

        if (!empty($class)) {
            $field .= ' class="' . $class . '"';
        }

        $field .= '>';

        if (($reinsert_value == true) && ((isset($_GET[$name]) && is_string($_GET[$name])) || (isset($_POST[$name]) && is_string($_POST[$name])))) {
            if (isset($_GET[$name]) && is_string($_GET[$name])) {
                $field .= static::outputProtected($_GET[$name]);
            } elseif (isset($_POST[$name]) && is_string($_POST[$name])) {
                $field .= static::outputProtected($_POST[$name]);
            }
        } elseif (!empty($text)) {
            $field .= static::outputProtected($text);
        }

        $field .= '</textarea>';

        return $field;
    }

    public static function hiddenField($name, $value = '', $parameters = '')
    {
        $field = '<input type="hidden" name="' . static::output($name) . '"';

        if (!empty($value)) {
            $field .= ' value="' . static::output($value) . '"';
        } elseif ((isset($_GET[$name]) && is_string($_GET[$name])) || (isset($_POST[$name]) && is_string($_POST[$name]))) {
            if (isset($_GET[$name]) && is_string($_GET[$name])) {
                $field .= ' value="' . static::output($_GET[$name]) . '"';
            } elseif (isset($_POST[$name]) && is_string($_POST[$name])) {
                $field .= ' value="' . static::output($_POST[$name]) . '"';
            }
        }

        if (!empty($parameters)) {
            $field .= ' ' . $parameters;
        }

        $field .= ' />';

        return $field;
    }
}
