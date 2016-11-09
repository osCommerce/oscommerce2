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

  class bm_manufacturers {
    var $code = 'bm_manufacturers';
    var $group = 'boxes';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->title = OSCOM::getDef('module_boxes_manufacturers_title');
      $this->description = OSCOM::getDef('module_boxes_manufacturers_description');

      if ( defined('MODULE_BOXES_MANUFACTURERS_STATUS') ) {
        $this->sort_order = MODULE_BOXES_MANUFACTURERS_SORT_ORDER;
        $this->enabled = (MODULE_BOXES_MANUFACTURERS_STATUS == 'True');

        $this->group = ((MODULE_BOXES_MANUFACTURERS_CONTENT_PLACEMENT == 'Left Column') ? 'boxes_column_left' : 'boxes_column_right');
      }
    }

    function getData() {
      global $oscTemplate;

      $OSCOM_Db = Registry::get('Db');

      $data = '';

      $Qmanufacturers = $OSCOM_Db->prepare('select manufacturers_id, manufacturers_name from :table_manufacturers order by manufacturers_name');
      $Qmanufacturers->setCache('manufacturers');
      $Qmanufacturers->execute();

      $manufacturers = $Qmanufacturers->fetchAll();

      if (!empty($manufacturers)) {
        if (count($manufacturers) <= MAX_DISPLAY_MANUFACTURERS_IN_A_LIST) {
// Display a list
          $manufacturers_list = '<ul class="nav nav-list">';

          foreach ($manufacturers as $m) {
            $manufacturers_name = ((strlen($m['manufacturers_name']) > MAX_DISPLAY_MANUFACTURER_NAME_LEN) ? substr($m['manufacturers_name'], 0, MAX_DISPLAY_MANUFACTURER_NAME_LEN) . '..' : $m['manufacturers_name']);

            if (isset($_GET['manufacturers_id']) && ($_GET['manufacturers_id'] == $m['manufacturers_id'])) {
              $manufacturers_name = '<strong>' . $manufacturers_name .'</strong>';
            }

            $manufacturers_list .= '<li><a href="' . OSCOM::link('index.php', 'manufacturers_id=' . (int)$m['manufacturers_id']) . '">' . $manufacturers_name . '</a></li>';
          }

          $manufacturers_list .= '</ul>';

          $data = $manufacturers_list;
        } else {
// Display a drop-down
          $manufacturers_array = array();

          if (MAX_MANUFACTURERS_LIST < 2) {
            $manufacturers_array[] = array('id' => '', 'text' => OSCOM::getDef('pull_down_default'));
          }

          foreach ($manufacturers as $m) {
            $manufacturers_name = ((strlen($m['manufacturers_name']) > MAX_DISPLAY_MANUFACTURER_NAME_LEN) ? substr($m['manufacturers_name'], 0, MAX_DISPLAY_MANUFACTURER_NAME_LEN) . '..' : $m['manufacturers_name']);

            $manufacturers_array[] = array('id' => $m['manufacturers_id'],
                                           'text' => $manufacturers_name);
          }

          $data = HTML::form('manufacturers', OSCOM::link('index.php', '', false), 'get', null, ['session_id' => true]) .
                  HTML::selectField('manufacturers_id', $manufacturers_array, (isset($_GET['manufacturers_id']) ? $_GET['manufacturers_id'] : ''), 'onchange="this.form.submit();" size="' . MAX_MANUFACTURERS_LIST . '"') .
                  '</form>';
        }

     }

      return $data;
    }

    function execute() {
      global $oscTemplate;

      $output = $this->getData();

      ob_start();
      include('includes/modules/boxes/templates/manufacturers.php');
      $data = ob_get_clean();

      $oscTemplate->addBlock($data, $this->group);
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_BOXES_MANUFACTURERS_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Manufacturers Module',
        'configuration_key' => 'MODULE_BOXES_MANUFACTURERS_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to add the module to your shop?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Content Placement',
        'configuration_key' => 'MODULE_BOXES_MANUFACTURERS_CONTENT_PLACEMENT',
        'configuration_value' => 'Left Column',
        'configuration_description' => 'Should the module be loaded in the left or right column?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'Left Column\', \'Right Column\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_BOXES_MANUFACTURERS_SORT_ORDER',
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
      return array('MODULE_BOXES_MANUFACTURERS_STATUS', 'MODULE_BOXES_MANUFACTURERS_CONTENT_PLACEMENT', 'MODULE_BOXES_MANUFACTURERS_SORT_ORDER');
    }
  }

