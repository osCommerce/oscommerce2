<?php
/*
  $Id:

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\Registry;

  class cm_in_category_description {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CONTENT_IN_CATEGORY_DESCRIPTION_TITLE;
      $this->description = MODULE_CONTENT_IN_CATEGORY_DESCRIPTION_DESCRIPTION;
      $this->description .= '<div class="secWarning">' . MODULE_CONTENT_BOOTSTRAP_ROW_DESCRIPTION . '</div>';

      if ( defined('MODULE_CONTENT_IN_CATEGORY_DESCRIPTION_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_IN_CATEGORY_DESCRIPTION_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_IN_CATEGORY_DESCRIPTION_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate, $category;

      $content_width = MODULE_CONTENT_IN_CATEGORY_DESCRIPTION_CONTENT_WIDTH;

      if (tep_not_null($category['categories_description'])) {
        ob_start();
        require(DIR_WS_MODULES . 'content/' . $this->group . '/templates/category_description.php');
        $template = ob_get_clean();

        $oscTemplate->addContent($template, $this->group);
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CONTENT_IN_CATEGORY_DESCRIPTION_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Category Description Module',
        'configuration_key' => 'MODULE_CONTENT_IN_CATEGORY_DESCRIPTION_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Should this module be enabled?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Content Width',
        'configuration_key' => 'MODULE_CONTENT_IN_CATEGORY_DESCRIPTION_CONTENT_WIDTH',
        'configuration_value' => '12',
        'configuration_description' => 'What width container should the content be shown in?',
        'configuration_group_id' => '6',
        'sort_order' => '3',
        'set_function' => 'tep_cfg_select_option(array(\'12\', \'11\', \'10\', \'9\', \'8\', \'7\', \'6\', \'5\', \'4\', \'3\', \'2\', \'1\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_CONTENT_IN_CATEGORY_DESCRIPTION_SORT_ORDER',
        'configuration_value' => '100',
        'configuration_description' => 'Sort order of display. Lowest is displayed first.',
        'configuration_group_id' => '6',
        'sort_order' => '2',
        'date_added' => 'now()'
      ]);
    }

    function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }

    function keys() {
      return array('MODULE_CONTENT_IN_CATEGORY_DESCRIPTION_STATUS', 'MODULE_CONTENT_IN_CATEGORY_DESCRIPTION_CONTENT_WIDTH', 'MODULE_CONTENT_IN_CATEGORY_DESCRIPTION_SORT_ORDER');
    }
  }