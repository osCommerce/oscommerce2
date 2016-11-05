<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class cm_create_account_link {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = OSCOM::getDef('module_content_create_account_link_title');
      $this->description = OSCOM::getDef('module_content_create_account_link_description');

      if ( defined('MODULE_CONTENT_CREATE_ACCOUNT_LINK_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_CREATE_ACCOUNT_LINK_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_CREATE_ACCOUNT_LINK_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate;

      ob_start();
      include('includes/modules/content/' . $this->group . '/templates/create_account_link.php');
      $template = ob_get_clean();

      $oscTemplate->addContent($template, $this->group);
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CONTENT_CREATE_ACCOUNT_LINK_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable New User Module',
        'configuration_key' => 'MODULE_CONTENT_CREATE_ACCOUNT_LINK_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to enable the new user module?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Content Width',
        'configuration_key' => 'MODULE_CONTENT_CREATE_ACCOUNT_LINK_CONTENT_WIDTH',
        'configuration_value' => 'Half',
        'configuration_description' => 'Should the content be shown in a full or half width container?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'Full\', \'Half\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_CONTENT_CREATE_ACCOUNT_LINK_SORT_ORDER',
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
      return array('MODULE_CONTENT_CREATE_ACCOUNT_LINK_STATUS', 'MODULE_CONTENT_CREATE_ACCOUNT_LINK_CONTENT_WIDTH', 'MODULE_CONTENT_CREATE_ACCOUNT_LINK_SORT_ORDER');
    }
  }
?>
