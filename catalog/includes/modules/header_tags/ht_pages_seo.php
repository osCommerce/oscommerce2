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

  class ht_pages_seo {
    var $code = 'ht_pages_seo';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->title = OSCOM::getDef('module_header_tags_pages_seo_title');
      $this->description = OSCOM::getDef('module_header_tags_pages_seo_description');
      $this->description .= '<div class="secWarning">' . OSCOM::getDef('module_header_tags_pages_seo_helper') . '</div>';

      if ( defined('MODULE_HEADER_TAGS_PAGES_SEO_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_PAGES_SEO_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_PAGES_SEO_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate;

      if ( (defined('META_SEO_TITLE')) && (strlen(META_SEO_TITLE) > 0) ) {
        $oscTemplate->setTitle(HTML::output(META_SEO_TITLE)  . OSCOM::getDef('module_header_tags_pages_seo_separator') . $oscTemplate->getTitle());
      }
      if ( (defined('META_SEO_DESCRIPTION')) && (strlen(META_SEO_DESCRIPTION) > 0) ) {
        $oscTemplate->addBlock('<meta name="description" content="' . HTML::output(META_SEO_DESCRIPTION) . '" />' . "\n", $this->group);
      }
      if ( (defined('META_SEO_KEYWORDS')) && (strlen(META_SEO_KEYWORDS) > 0) ) {
        $oscTemplate->addBlock('<meta name="keywords" content="' . HTML::output(META_SEO_KEYWORDS) . '" />' . "\n", $this->group);
      }

    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_PAGES_SEO_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Pages SEO Module',
        'configuration_key' => 'MODULE_HEADER_TAGS_PAGES_SEO_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to allow this module to write SEO to your Pages?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_HEADER_TAGS_PAGES_SEO_SORT_ORDER',
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
      return array('MODULE_HEADER_TAGS_PAGES_SEO_STATUS', 'MODULE_HEADER_TAGS_PAGES_SEO_SORT_ORDER');
    }
  }

