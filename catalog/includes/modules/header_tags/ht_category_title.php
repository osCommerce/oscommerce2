<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class ht_category_title {
    var $code = 'ht_category_title';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->title = OSCOM::getDef('module_header_tags_category_title_title');
      $this->description = OSCOM::getDef('module_header_tags_category_title_description');

      if ( defined('MODULE_HEADER_TAGS_CATEGORY_TITLE_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_CATEGORY_TITLE_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_CATEGORY_TITLE_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF, $oscTemplate, $current_category_id;

      $OSCOM_Db = Registry::get('Db');
      $OSCOM_Language = Registry::get('Language');

      if (basename($PHP_SELF) == 'index.php') {
        if ($current_category_id > 0) {
          $Qcategory = $OSCOM_Db->get('categories_description', ['categories_name', 'categories_seo_title'], ['categories_id' => $current_category_id, 'language_id' => $OSCOM_Language->getId()]);

          if ($Qcategory->fetch() !== false) {
            if ( tep_not_null($Qcategory->value('categories_seo_title')) && (MODULE_HEADER_TAGS_CATEGORY_TITLE_SEO_TITLE_OVERRIDE == 'True') ) {
              $oscTemplate->setTitle($Qcategory->value('categories_seo_title') . OSCOM::getDef('module_header_tags_category_seo_separator') . $oscTemplate->getTitle());
            }
            else {
              $oscTemplate->setTitle($Qcategory->value('categories_name') . OSCOM::getDef('module_header_tags_category_seo_separator') . $oscTemplate->getTitle());
            }
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

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'SEO Title Override?',
        'configuration_key' => 'MODULE_HEADER_TAGS_CATEGORY_TITLE_SEO_TITLE_OVERRIDE',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to allow category titles to be over-ridden by your SEO Titles (if set)?',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'SEO Breadcrumb Override?',
        'configuration_key' => 'MODULE_HEADER_TAGS_CATEGORY_TITLE_SEO_BREADCRUMB_OVERRIDE',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to allow category names in the breadcrumb to be over-ridden by your SEO Titles (if set)?',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);
    }

    function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }

    function keys() {
      return array('MODULE_HEADER_TAGS_CATEGORY_TITLE_STATUS', 'MODULE_HEADER_TAGS_CATEGORY_TITLE_SORT_ORDER', 'MODULE_HEADER_TAGS_CATEGORY_TITLE_SEO_TITLE_OVERRIDE', 'MODULE_HEADER_TAGS_CATEGORY_TITLE_SEO_BREADCRUMB_OVERRIDE');
    }
  }
