<?php
/*
  $Id: html_output.php,v 1.2 2003/07/09 01:11:05 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  function osc_draw_input_field($name, $text = '', $type = 'text', $parameters = '', $reinsert_value = true) {
    $field = '<input type="' . $type . '" name="' . $name . '"';
    if ( ($key = $GLOBALS[$name]) || ($key = $GLOBALS['HTTP_GET_VARS'][$name]) || ($key = $GLOBALS['HTTP_POST_VARS'][$name]) || ($key = $GLOBALS['HTTP_SESSION_VARS'][$name]) && ($reinsert_value) ) {
      $field .= ' value="' . $key . '"';
    } elseif ($text != '') {
      $field .= ' value="' . $text . '"';
    }
    if ($parameters) $field.= ' ' . $parameters;
    $field .= '>';

    return $field;
  }

  function osc_draw_password_field($name, $text = '') {
    return osc_draw_input_field($name, $text, 'password', '', false);
  }

  function osc_draw_hidden_field($name, $value) {
    return '<input type="hidden" name="' . $name . '" value="' . $value . '">';
  }

  function osc_draw_selection_field($name, $type, $value = '', $checked = false) {
    $selection = '<input type="' . $type . '" name="' . $name . '"';
    if ($value != '') $selection .= ' value="' . $value . '"';
    if ( ($checked == true) || ($GLOBALS[$name] == 'on') || ($value == 'on') || ($value && $GLOBALS[$name] == $value) ) {
      $selection .= ' CHECKED';
    }
    $selection .= '>';

    return $selection;
  }

  function osc_draw_checkbox_field($name, $value = '', $checked = false) {
    return osc_draw_selection_field($name, 'checkbox', $value, $checked);
  }

  function osc_draw_radio_field($name, $value = '', $checked = false) {
    return osc_draw_selection_field($name, 'radio', $value, $checked);
  }
?>
