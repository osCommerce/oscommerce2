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
?>
