<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class ht_mailchimp_360 {
    var $code = 'ht_mailchimp_360';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->title = OSCOM::getDef('module_header_tags_mailchimp_360_title');
      $this->description = OSCOM::getDef('module_header_tags_mailchimp_360_description');

      if ( defined('MODULE_HEADER_TAGS_MAILCHIMP_360_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_MAILCHIMP_360_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_MAILCHIMP_360_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF;

      include('includes/modules/header_tags/ht_mailchimp_360/MCAPI.class.php');
      include('includes/modules/header_tags/ht_mailchimp_360/mc360.php');

      $mc360 = new mc360();
      $mc360->set_cookies();

      if (basename($PHP_SELF) == 'checkout_success.php') {
        $mc360->process();
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_MAILCHIMP_360_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable MailChimp 360 Module',
        'configuration_key' => 'MODULE_HEADER_TAGS_MAILCHIMP_360_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to activate this module in your shop?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'API Key',
        'configuration_key' => 'MODULE_HEADER_TAGS_MAILCHIMP_360_API_KEY',
        'configuration_value' => '',
        'configuration_description' => 'An API Key assigned to your MailChimp account',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Debug E-Mail',
        'configuration_key' => 'MODULE_HEADER_TAGS_MAILCHIMP_360_DEBUG_EMAIL',
        'configuration_value' => '',
        'configuration_description' => 'If an e-mail address is entered, debug data will be sent to it',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_HEADER_TAGS_MAILCHIMP_360_SORT_ORDER',
        'configuration_value' => '0',
        'configuration_description' => 'Sort order of display. Lowest is displayed first.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);

// Internal parameters

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'MailChimp Store ID',
        'configuration_key' => 'MODULE_HEADER_TAGS_MAILCHIMP_360_STORE_ID',
        'configuration_value' => '',
        'configuration_description' => 'Do not edit. Store ID value.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'MailChimp Key Valid',
        'configuration_key' => 'MODULE_HEADER_TAGS_MAILCHIMP_360_KEY_VALID',
        'configuration_value' => '',
        'configuration_description' => 'Do not edit. Key Value value.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);
    }

    function remove() {
      Registry::get('Db')->query('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');

// Internal parameters
      Registry::get('Db')->query('delete from :table_configuration where configuration_key in ("MODULE_HEADER_TAGS_MAILCHIMP_360_STORE_ID", "MODULE_HEADER_TAGS_MAILCHIMP_360_KEY_VALID")');
    }

    function keys() {
      return array('MODULE_HEADER_TAGS_MAILCHIMP_360_STATUS', 'MODULE_HEADER_TAGS_MAILCHIMP_360_API_KEY', 'MODULE_HEADER_TAGS_MAILCHIMP_360_DEBUG_EMAIL', 'MODULE_HEADER_TAGS_MAILCHIMP_360_SORT_ORDER');
    }
  }
?>
