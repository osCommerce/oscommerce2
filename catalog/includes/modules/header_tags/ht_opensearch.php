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

  class ht_opensearch {
    var $code = 'ht_opensearch';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->title = OSCOM::getDef('module_header_tags_opensearch_title');
      $this->description = OSCOM::getDef('module_header_tags_opensearch_description');

      if ( defined('MODULE_HEADER_TAGS_OPENSEARCH_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_OPENSEARCH_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_OPENSEARCH_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate;

      $oscTemplate->addBlock('<link rel="search" type="application/opensearchdescription+xml" href="' . OSCOM::link('opensearch.php', '', false) . '" title="' . HTML::output(STORE_NAME) . '" />', $this->group);
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_OPENSEARCH_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable OpenSearch Module',
        'configuration_key' => 'MODULE_HEADER_TAGS_OPENSEARCH_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Add shop search functionality to the browser?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Short Name',
        'configuration_key' => 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_SHORT_NAME',
        'configuration_value' => STORE_NAME,
        'configuration_description' => 'Short name to describe the search engine.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Description',
        'configuration_key' => 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_DESCRIPTION',
        'configuration_value' => 'Search ' . STORE_NAME,
        'configuration_description' => 'Description of the search engine.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Contact',
        'configuration_key' => 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_CONTACT',
        'configuration_value' => STORE_OWNER_EMAIL_ADDRESS,
        'configuration_description' => 'E-Mail address of the search engine maintainer. (optional)',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Tags',
        'configuration_key' => 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_TAGS',
        'configuration_value' => '',
        'configuration_description' => 'Keywords to identify and categorize the search content, separated by an empty space. (optional)',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Attribution',
        'configuration_key' => 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_ATTRIBUTION',
        'configuration_value' => 'Copyright (c) ' . STORE_NAME,
        'configuration_description' => 'Attribution for the search content. (optional)',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Adult Content',
        'configuration_key' => 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_ADULT_CONTENT',
        'configuration_value' => 'False',
        'configuration_description' => 'Search content contains material suitable only for adults.',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => '16x16 Icon',
        'configuration_key' => 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_ICON',
        'configuration_value' => OSCOM::linkImage('Shop/favicon.ico'),
        'configuration_description' => 'A 16x16 sized icon (must be in .ico format, eg http://server/favicon.ico). (optional)',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => '64x64 Image',
        'configuration_key' => 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_IMAGE',
        'configuration_value' => '',
        'configuration_description' => 'A 64x64 sized image (must be in .png format, eg http://server/images/logo.png). (optional)',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_HEADER_TAGS_OPENSEARCH_SORT_ORDER',
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
      return array('MODULE_HEADER_TAGS_OPENSEARCH_STATUS', 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_SHORT_NAME', 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_DESCRIPTION', 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_CONTACT', 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_TAGS', 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_ATTRIBUTION', 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_ADULT_CONTENT', 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_ICON', 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_IMAGE', 'MODULE_HEADER_TAGS_OPENSEARCH_SORT_ORDER');
    }
  }
?>
