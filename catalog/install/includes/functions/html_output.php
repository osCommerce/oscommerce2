<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  function osc_draw_input_field($name, $value = null, $parameters = null, $override = true, $type = 'text') {
    $field = '<input type="' . osc_output_string($type) . '" name="' . osc_output_string($name) . '" id="' . osc_output_string($name) . '"';
    if ( ($key = $GLOBALS[$name]) || ($key = $GLOBALS['HTTP_GET_VARS'][$name]) || ($key = $GLOBALS['HTTP_POST_VARS'][$name]) || ($key = $GLOBALS['HTTP_SESSION_VARS'][$name]) && ($override) ) {
      $field .= ' value="' . osc_output_string($key) . '"';
    } elseif ($value != '') {
      $field .= ' value="' . osc_output_string($value) . '"';
    }
    if ($parameters) $field.= ' ' . $parameters;
    $field .= '>';

    return $field;
  }

  function osc_draw_password_field($name, $parameters = null) {
    return osc_draw_input_field($name, null, $parameters, false, 'password');
  }

  function osc_draw_hidden_field($name, $value) {
    return '<input type="hidden" name="' . osc_output_string($name) . '" value="' . osc_output_string($value) . '">';
  }

  function osc_draw_select_menu($name, $values, $default = null, $parameters = null) {
    global $HTTP_GET_VARS, $HTTP_POST_VARS;

    $group = false;

    if ( isset($HTTP_GET_VARS[$name]) ) {
      $default = $HTTP_GET_VARS[$name];
    } elseif ( isset($HTTP_POST_VARS[$name]) ) {
      $default = $HTTP_POST_VARS[$name];
    }

    $field = '<select name="' . osc_output_string($name) . '"';

    if ( strpos($parameters, 'id=') === false ) {
      $field .= ' id="' . osc_output_string($name) . '"';
    }

    if ( !empty($parameters) ) {
      $field .= ' ' . $parameters;
    }

    $field .= '>';

    for ( $i=0, $n=count($values); $i<$n; $i++ ) {
      if ( isset($values[$i]['group']) ) {
        if ( $group != $values[$i]['group'] ) {
          $group = $values[$i]['group'];

          $field .= '<optgroup label="' . osc_output_string($values[$i]['group']) . '">';
        }
      }

      $field .= '<option value="' . osc_output_string($values[$i]['id']) . '"';

      if ( isset($default) && ((!is_array($default) && ((string)$default == (string)$values[$i]['id'])) || (is_array($default) && in_array($values[$i]['id'], $default))) ) {
        $field .= ' selected="selected"';
      }

      if ( isset($values[$i]['params']) ) {
        $field .= ' ' . $values[$i]['params'];
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

  function osc_draw_time_zone_select_menu($name, $default = null) {
    if ( !isset($default) ) {
      $default = date_default_timezone_get();
    }

    $time_zones_array = array();

    foreach ( timezone_identifiers_list() as $id ) {
      $tz_string = str_replace('_', ' ', $id);

      $id_array = explode('/', $tz_string, 2);

      $time_zones_array[$id_array[0]][$id] = isset($id_array[1]) ? $id_array[1] : $id_array[0];
    }

    $result = array();

    foreach ( $time_zones_array as $zone => $zones_array ) {
      foreach ( $zones_array as $key => $value ) {
        $result[] = array('id' => $key,
                          'text' => $value,
                          'group' => $zone);
      }
    }

    return osc_draw_select_menu($name, $result, $default);
  }

////
// Output a jQuery UI Button
  function osc_draw_button($title = null, $icon = null, $link = null, $priority = null, $params = null) {
    static $button_counter = 1;

    $types = array('submit', 'button', 'reset');

    if ( !isset($params['type']) ) {
      $params['type'] = 'submit';
    }

    if ( !in_array($params['type'], $types) ) {
      $params['type'] = 'submit';
    }

    if ( ($params['type'] == 'submit') && isset($link) ) {
      $params['type'] = 'button';
    }

    if (!isset($priority)) {
      $priority = 'secondary';
    }

    $button = '';

    if ( ($params['type'] == 'button') && isset($link) ) {
      $button .= '<a id="tdb' . $button_counter . '" href="' . $link . '"';

      if ( isset($params['newwindow']) ) {
        $button .= ' target="_blank"';
      }
    } else {
      $button .= '<button id="tdb' . $button_counter . '" type="' . osc_output_string($params['type']) . '"';
    }

    if ( isset($params['params']) ) {
      $button .= ' ' . $params['params'];
    }

    $button .= '>' . $title;

    if ( ($params['type'] == 'button') && isset($link) ) {
      $button .= '</a>';
    } else {
      $button .= '</button>';
    }

    $button .= '<script>$("#tdb' . $button_counter . '").button(';

    $args = array();

    if ( isset($icon) ) {
      if ( !isset($params['iconpos']) ) {
        $params['iconpos'] = 'left';
      }

      if ( $params['iconpos'] == 'left' ) {
        $args[] = 'icons:{primary:"ui-icon-' . $icon . '"}';
      } else {
        $args[] = 'icons:{secondary:"ui-icon-' . $icon . '"}';
      }
    }

    if (empty($title)) {
      $args[] = 'text:false';
    }

    if (!empty($args)) {
      $button .= '{' . implode(',', $args) . '}';
    }

    $button .= ').addClass("ui-priority-' . $priority . '");</script>';

    $button_counter++;

    return $button;
  }
?>
