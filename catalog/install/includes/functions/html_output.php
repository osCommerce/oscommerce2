<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2007 osCommerce

  Released under the GNU General Public License
*/

  function osc_draw_input_field($name, $value = null, $parameters = null, $override = true, $type = 'text') {
    $field = '<input type="' . $type . '" name="' . $name . '" id="' . $name . '"';
    if ( ($key = $GLOBALS[$name]) || ($key = $GLOBALS['HTTP_GET_VARS'][$name]) || ($key = $GLOBALS['HTTP_POST_VARS'][$name]) || ($key = $GLOBALS['HTTP_SESSION_VARS'][$name]) && ($override) ) {
      $field .= ' value="' . $key . '"';
    } elseif ($value != '') {
      $field .= ' value="' . $value . '"';
    }
    if ($parameters) $field.= ' ' . $parameters;
    $field .= '>';

    return $field;
  }

  function osc_draw_password_field($name, $parameters = null) {
    return osc_draw_input_field($name, null, $parameters, false, 'password');
  }

  function osc_draw_hidden_field($name, $value) {
    return '<input type="hidden" name="' . $name . '" value="' . $value . '">';
  }

 /**
 * Outputs a form pull down menu field
 *
 * @param string $name The name of the pull down menu field
 * @param array $values Defined values for the pull down menu field
 * @param string $default The default value for the pull down menu field
 * @param string $parameters Additional parameters for the pull down menu field
 * @access public
 */
  function osc_draw_pull_down_menu($name, $values, $default = null, $parameters = null) {
    $group = false;
    if (isset($_GET[$name])) {
      $default = $_GET[$name];
    } elseif (isset($_POST[$name])) {
      $default = $_POST[$name];
    }
    $field = '<select name="' . osc_output_string($name) . '"';
    if (strpos($parameters, 'id=') === false) {
      $field .= ' id="' . osc_output_string($name) . '"';
    }
    if (!empty($parameters)) {
      $field .= ' ' . $parameters;
    }
    $field .= '>';
    for ($i=0, $n=sizeof($values); $i<$n; $i++) {
      if (isset($values[$i]['group'])) {
        if ($group != $values[$i]['group']) {
          $group = $values[$i]['group'];
          $field .= '<optgroup label="' . osc_output_string($values[$i]['group']) . '">';
        }
      }
      $field .= '<option value="' . osc_output_string($values[$i]['id']) . '"';
      if ( (!is_null($default) && !is_array($default) && ((string)$default == (string)$values[$i]['id'])) || (is_array($default) && in_array($values[$i]['id'], $default)) ) {
        $field .= ' selected="selected"';
      }

      $field .= '>' . osc_output_string($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';

      if ( ($group !== false) && (($group != $values[$i]['group']) || !isset($values[$i+1])) ) {
        $group = false;

        $field .= '</optgroup>';
      }
    }

    $field .= '</select>';

    return $field;
  }

 /**
 * Parse and output a user submited value
 *
 * @param string $string The string to parse and output
 * @param array $translate An array containing the characters to parse
 * @access public
 */

  function osc_output_string($string, $translate = null) {
    if (empty($translate)) {
      $translate = array('"' => '&quot;');
    }

    return strtr(trim($string), $translate);
  }
?>