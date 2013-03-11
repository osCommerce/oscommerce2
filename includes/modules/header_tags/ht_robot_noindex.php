<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce

  Released under the GNU General Public License
*/

  class ht_robot_noindex {
    var $code = 'ht_robot_noindex';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function ht_robot_noindex() {
      $this->title = MODULE_HEADER_TAGS_ROBOT_NOINDEX_TITLE;
      $this->description = MODULE_HEADER_TAGS_ROBOT_NOINDEX_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_ROBOT_NOINDEX_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_ROBOT_NOINDEX_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_ROBOT_NOINDEX_STATUS == 'True');
      }
    }

    function execute() {
      global $OSCOM_APP, $OSCOM_Template;

      if (osc_not_null(MODULE_HEADER_TAGS_ROBOT_NOINDEX_PAGES)) {
        $pages = explode(';', MODULE_HEADER_TAGS_ROBOT_NOINDEX_PAGES);

        $app = isset($OSCOM_APP) ? $OSCOM_APP->getCode() : 'index';

        $action_counter = 0;
        $application_key = null;
        $action = array();

        foreach ( $_GET as $key => $value ) {
          $key = osc_sanitize_string(basename($key));

          if ( preg_match('/^[A-Za-z0-9-_]*$/', $key) === false ) {
            break;
          }

          if ( !isset($application_key) && ($key == $app) ) {
            $application_key = $action_counter;

            $action_counter++;

            continue;
          }

          $action[$key] = $value;

          if ( !file_exists(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'apps/' . $app . '/actions/' . implode('/', array_keys($action)) . '.php') ) {
            array_pop($action);

            break;
          }

          $action_counter++;
        }

        $action_get = implode('/', array_keys($action));

        $page = $app . (!empty($action_get) ? '/' . $action_get : null);

        foreach ( $pages as $p ) {
          if ( strpos($page, $p) === 0 ) {
            $OSCOM_Template->addBlock('<meta name="robots" content="noindex,follow" />' . "\n", $this->group);

            break;
          }
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_ROBOT_NOINDEX_STATUS');
    }

    function install() {
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Robot NoIndex Module', 'MODULE_HEADER_TAGS_ROBOT_NOINDEX_STATUS', 'True', 'Do you want to enable the Robot NoIndex module?', '6', '1', 'osc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Pages', 'MODULE_HEADER_TAGS_ROBOT_NOINDEX_PAGES', '" . implode(';', $this->get_default_pages()) . "', 'The pages to add the meta robot noindex tag to.', '6', '0', 'ht_robot_noindex_show_pages', 'ht_robot_noindex_edit_pages(', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_HEADER_TAGS_ROBOT_NOINDEX_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      osc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_HEADER_TAGS_ROBOT_NOINDEX_STATUS', 'MODULE_HEADER_TAGS_ROBOT_NOINDEX_PAGES', 'MODULE_HEADER_TAGS_ROBOT_NOINDEX_SORT_ORDER');
    }

    function get_default_pages() {
      return array('account',
                   'cart',
                   'checkout',
                   'info/cookie_usage',
                   'info/ssl_check',
                   'products/reviews/new',
                   'products/tell_a_friend');
    }
  }

  function ht_robot_noindex_show_pages($text) {
    return nl2br(implode("\n", explode(';', $text)));
  }

  function ht_robot_noindex_edit_pages($values, $key) {
    $strip = strlen(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'apps/');

    $files_array = array();

    $files = new RegexIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'apps/'), RecursiveIteratorIterator::SELF_FIRST), '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
    foreach ( $files as $name => $object ) {
      $name = substr($name, $strip); // remove full path
      $name = substr($name, 0, -4); // remove file extension

      $banana_split = explode('/', $name);

      if ( isset($banana_split[1]) && isset($banana_split[2]) && ($banana_split[1] == 'actions') ) {
        if ( !in_array($banana_split[0], $files_array) ) {
          $files_array[] = $banana_split[0];
        }

        $files_array[] = $banana_split[0] . '/' . implode('/', array_slice($banana_split, 2));
      }
    }

    $values_array = explode(';', $values);

    $output = '';
    foreach ($files_array as $file) {
      $output .= osc_draw_checkbox_field('ht_robot_noindex_file[]', $file, in_array($file, $values_array)) . '&nbsp;' . osc_output_string($file) . '<br />';
    }

    if (!empty($output)) {
      $output = '<br />' . substr($output, 0, -6);
    }

    $output .= osc_draw_hidden_field('configuration[' . $key . ']', '', 'id="htrn_files"');

    $output .= '<script type="text/javascript">
                function htrn_update_cfg_value() {
                  var htrn_selected_files = \'\';

                  if ($(\'input[name="ht_robot_noindex_file[]"]\').length > 0) {
                    $(\'input[name="ht_robot_noindex_file[]"]:checked\').each(function() {
                      htrn_selected_files += $(this).attr(\'value\') + \';\';
                    });

                    if (htrn_selected_files.length > 0) {
                      htrn_selected_files = htrn_selected_files.substring(0, htrn_selected_files.length - 1);
                    }
                  }

                  $(\'#htrn_files\').val(htrn_selected_files);
                }

                $(function() {
                  htrn_update_cfg_value();

                  if ($(\'input[name="ht_robot_noindex_file[]"]\').length > 0) {
                    $(\'input[name="ht_robot_noindex_file[]"]\').change(function() {
                      htrn_update_cfg_value();
                    });
                  }
                });
                </script>';

    return $output;
  }
?>
