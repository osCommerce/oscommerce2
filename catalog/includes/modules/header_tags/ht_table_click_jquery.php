<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class ht_table_click_jquery {
    var $code = 'ht_table_click_jquery';
    var $group = 'footer_scripts';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->title = OSCOM::getDef('module_header_tags_table_click_jquery_title');
      $this->description = OSCOM::getDef('module_header_tags_table_click_jquery_description');

      if ( defined('MODULE_HEADER_TAGS_TABLE_CLICK_JQUERY_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_TABLE_CLICK_JQUERY_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_TABLE_CLICK_JQUERY_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF, $oscTemplate;

      if (tep_not_null(MODULE_HEADER_TAGS_TABLE_CLICK_JQUERY_PAGES)) {
        $pages_array = array();

        foreach (explode(';', MODULE_HEADER_TAGS_TABLE_CLICK_JQUERY_PAGES) as $page) {
          $page = trim($page);

          if (!empty($page)) {
            $pages_array[] = $page;
          }
        }

        if (in_array(basename($PHP_SELF), $pages_array)) {
          $oscTemplate->addBlock('<script>$(\'.table tr.table-selection\').click(function() { $(\'.table tr\').removeClass(\'success\').find(\'input\').prop(\'checked\', false); $(this).addClass(\'success\').find(\'input\').prop(\'checked\', true); });</script>' . "\n", $this->group);
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_TABLE_CLICK_JQUERY_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Clickable Table Rows Module',
        'configuration_key' => 'MODULE_HEADER_TAGS_TABLE_CLICK_JQUERY_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to enable the Clickable Table Rows module?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Pages',
        'configuration_key' => 'MODULE_HEADER_TAGS_TABLE_CLICK_JQUERY_PAGES',
        'configuration_value' => implode(';', $this->get_default_pages()),
        'configuration_description' => 'The pages to add the jQuery Scripts to.',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'use_function' => 'ht_table_click_jquery_show_pages',
        'set_function' => 'ht_table_click_jquery_edit_pages(',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_HEADER_TAGS_TABLE_CLICK_JQUERY_SORT_ORDER',
        'configuration_value' => '0',
        'configuration_description' => 'Sort order of display. Lowest is displayed first.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);
    }

    function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }

    function keys() {
      return array('MODULE_HEADER_TAGS_TABLE_CLICK_JQUERY_STATUS', 'MODULE_HEADER_TAGS_TABLE_CLICK_JQUERY_PAGES', 'MODULE_HEADER_TAGS_TABLE_CLICK_JQUERY_SORT_ORDER');
    }

    function get_default_pages() {
      return array('checkout_shipping.php',
                   'checkout_payment.php');
    }
  }

  function ht_table_click_jquery_show_pages($text) {
    return nl2br(implode("\n", explode(';', $text)));
  }

  function ht_table_click_jquery_edit_pages($values, $key) {
    global $PHP_SELF;

    $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
    $files_array = array();
	  if ($dir = @dir(OSCOM::getConfig('dir_root', 'Shop'))) {
	    while ($file = $dir->read()) {
	      if (!is_dir(OSCOM::getConfig('dir_root', 'Shop') . $file)) {
	        if (substr($file, strrpos($file, '.')) == $file_extension) {
            $files_array[] = $file;
          }
        }
      }
      sort($files_array);
      $dir->close();
    }

    $values_array = explode(';', $values);

    $output = '';
    foreach ($files_array as $file) {
      $output .= HTML::checkboxField('ht_table_click_jquery_file[]', $file, in_array($file, $values_array)) . '&nbsp;' . HTML::output($file) . '<br />';
    }

    if (!empty($output)) {
      $output = '<br />' . substr($output, 0, -6);
    }

    $output .= HTML::hiddenField('configuration[' . $key . ']', '', 'id="htrn_files"');

    $output .= '<script>
                function htrn_update_cfg_value() {
                  var htrn_selected_files = \'\';

                  if ($(\'input[name="ht_table_click_jquery_file[]"]\').length > 0) {
                    $(\'input[name="ht_table_click_jquery_file[]"]:checked\').each(function() {
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

                  if ($(\'input[name="ht_table_click_jquery_file[]"]\').length > 0) {
                    $(\'input[name="ht_table_click_jquery_file[]"]\').change(function() {
                      htrn_update_cfg_value();
                    });
                  }
                });
                </script>';

    return $output;
  }
?>
