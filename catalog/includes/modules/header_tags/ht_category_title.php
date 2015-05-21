<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\Registry;

  class ht_category_title {
    var $code = 'ht_category_title';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function ht_category_title() {
      $this->title = MODULE_HEADER_TAGS_CATEGORY_TITLE_TITLE;
      $this->description = MODULE_HEADER_TAGS_CATEGORY_TITLE_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_CATEGORY_TITLE_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_CATEGORY_TITLE_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_CATEGORY_TITLE_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF, $oscTemplate, $current_category_id;

      $OSCOM_Db = Registry::get('Db');

      if (basename($PHP_SELF) == 'index.php') {
        if ($current_category_id > 0) {
          $Qcategory = $OSCOM_Db->get('categories_description', 'categories_name', ['categories_id' => $current_category_id, 'language_id' => $_SESSION['languages_id']]);

          if ($Qcategory->fetch() !== false) {
            $oscTemplate->setTitle($Qcategory->value('categories_name') . ', ' . $oscTemplate->getTitle());
          }
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_CATEGORY_TITLE_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Category Title Module',
        'configuration_key' => 'MODULE_HEADER_TAGS_CATEGORY_TITLE_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to allow category titles to be added to the page title?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_HEADER_TAGS_CATEGORY_TITLE_SORT_ORDER',
        'configuration_value' => '0',
        'configuration_description' => 'Sort order of display. Lowest is displayed first.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);
    }

    function remove() {
      return Registry::get('Db')->query('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")')->rowCount();
    }

    function keys() {
      return array('MODULE_HEADER_TAGS_CATEGORY_TITLE_STATUS', 'MODULE_HEADER_TAGS_CATEGORY_TITLE_SORT_ORDER');
    }
  }
?>
