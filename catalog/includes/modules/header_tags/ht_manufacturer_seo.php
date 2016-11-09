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

  class ht_manufacturer_seo {
    var $code = 'ht_manufacturer_seo';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->title = OSCOM::getDef('module_header_tags_manufacturers_seo_title');
      $this->description = OSCOM::getDef('module_header_tags_manufacturers_seo_description');

      if ( defined('MODULE_HEADER_TAGS_MANUFACTURERS_SEO_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_MANUFACTURERS_SEO_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_MANUFACTURERS_SEO_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF, $oscTemplate;

      $OSCOM_Db = Registry::get('Db');
      $OSCOM_Language = Registry::get('Language');

      if (basename($PHP_SELF) == 'index.php') {
        if (isset($_GET['manufacturers_id']) && is_numeric($_GET['manufacturers_id'])) {
          $Qmeta = $OSCOM_Db->get('manufacturers_info', ['manufacturers_seo_description', 'manufacturers_seo_keywords'], ['manufacturers_id' => (int)$_GET['manufacturers_id'], 'languages_id' => $OSCOM_Language->getId()]);

          if ($Qmeta->fetch() !== false) {
            if (tep_not_null($Qmeta->value('manufacturers_seo_description'))) {
              $oscTemplate->addBlock('<meta name="description" content="' . HTML::output($Qmeta->value('manufacturers_seo_description')) . '" />' . PHP_EOL, $this->group);
            }
            if ( tep_not_null($Qmeta->value('manufacturers_seo_keywords')) && (MODULE_HEADER_TAGS_MANUFACTURERS_SEO_KEYWORDS_STATUS == 'True') ) {
              $oscTemplate->addBlock('<meta name="keywords" content="' . HTML::output($Qmeta->value('manufacturers_seo_keywords')) . '" />' . PHP_EOL, $this->group);
            }
          }
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_MANUFACTURERS_SEO_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Manufacturer Meta Module',
        'configuration_key' => 'MODULE_HEADER_TAGS_MANUFACTURERS_SEO_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to allow Manufacturer meta tags to be added to the page header?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Display Manufacturer Meta Description',
        'configuration_key' => 'MODULE_HEADER_TAGS_MANUFACTURERS_SEO_DESCRIPTION_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Manufacturer Descriptions help your site and your sites visitors.',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Display Manufacturer Meta Keywords',
        'configuration_key' => 'MODULE_HEADER_TAGS_MANUFACTURERS_SEO_KEYWORDS_STATUS',
        'configuration_value' => 'False',
        'configuration_description' => 'Manufacturer Keywords are almost pointless.  If you are into the Chinese Market select True (for Baidu Search Engine) otherwise select False.',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_HEADER_TAGS_MANUFACTURERS_SEO_SORT_ORDER',
        'configuration_value' => '110',
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
      return array('MODULE_HEADER_TAGS_MANUFACTURERS_SEO_STATUS', 'MODULE_HEADER_TAGS_MANUFACTURERS_SEO_DESCRIPTION_STATUS', 'MODULE_HEADER_TAGS_MANUFACTURERS_SEO_KEYWORDS_STATUS', 'MODULE_HEADER_TAGS_MANUFACTURERS_SEO_SORT_ORDER');
    }
  }
