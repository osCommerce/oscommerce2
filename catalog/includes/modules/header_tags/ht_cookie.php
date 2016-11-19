<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class ht_cookie {
    var $code = 'ht_cookie';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->title = OSCOM::getDef('module_header_tags_cookie_title');
      $this->description = OSCOM::getDef('module_header_tags_cookie_description');

      if ( defined('MODULE_HEADER_TAGS_COOKIE_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_COOKIE_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_COOKIE_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate;

      $message = OSCOM::getDef('module_header_tags_cookie_message_text');
      $dismiss = OSCOM::getDef('module_header_tags_cookie_dismiss_text');
      $more    = OSCOM::getDef('module_header_tags_cookie_more_text');
      $link    = OSCOM::link(MODULE_HEADER_TAGS_COOKIE_PAGE);
      $theme   = OSCOM::link('ext/cookieconsent2/' . MODULE_HEADER_TAGS_COOKIE_THEME . '.css', null, false);

      $script_src = OSCOM::link('ext/cookieconsent2/cookieconsent.min.js', null, false);

      $output  = <<<EOD
<script>window.cookieconsent_options = {"message":"{$message}", "dismiss":"{$dismiss}", "learnMore":"{$more}", "link":"{$link}", "theme":"{$theme}"};</script>
<script src="{$script_src}"></script>
EOD;

      $oscTemplate->addBlock($output . "\n", $this->group);
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_COOKIE_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Cookie Compliance Module',
        'configuration_key' => 'MODULE_HEADER_TAGS_COOKIE_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to enable this module?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Theme',
        'configuration_key' => 'MODULE_HEADER_TAGS_COOKIE_THEME',
        'configuration_value' => 'dark-top',
        'configuration_description' => 'Select Theme.',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'dark-top\', \'dark-floating\', \'dark-bottom\', \'light-floating\', \'light-top\', \'light-bottom\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Cookie Policy Page',
        'configuration_key' => 'MODULE_HEADER_TAGS_COOKIE_PAGE',
        'configuration_value' => 'privacy.php',
        'configuration_description' => 'The Page on your site that has details of your Cookie Policy.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_HEADER_TAGS_COOKIE_SORT_ORDER',
        'configuration_value' => '900',
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
      return array('MODULE_HEADER_TAGS_COOKIE_STATUS', 'MODULE_HEADER_TAGS_COOKIE_THEME', 'MODULE_HEADER_TAGS_COOKIE_PAGE', 'MODULE_HEADER_TAGS_COOKIE_SORT_ORDER');
    }
  }
