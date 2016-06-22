<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class cm_account_braintree_cards {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CONTENT_ACCOUNT_BRAINTREE_CARDS_TITLE;
      $this->description = MODULE_CONTENT_ACCOUNT_BRAINTREE_CARDS_DESCRIPTION;

      if ( defined('MODULE_CONTENT_ACCOUNT_BRAINTREE_CARDS_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_ACCOUNT_BRAINTREE_CARDS_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_ACCOUNT_BRAINTREE_CARDS_STATUS == 'True');
      }

      $this->public_title = MODULE_CONTENT_ACCOUNT_BRAINTREE_CARDS_LINK_TITLE;

      $braintree_enabled = false;

      if ( defined('MODULE_PAYMENT_INSTALLED') && tep_not_null(MODULE_PAYMENT_INSTALLED) && in_array('braintree_cc.php', explode(';', MODULE_PAYMENT_INSTALLED)) ) {
        if ( !class_exists('braintree_cc') ) {
          include(DIR_FS_CATALOG . 'includes/languages/' . $_SESSION['language'] . '/modules/payment/braintree_cc.php');
          include(DIR_FS_CATALOG . 'includes/modules/payment/braintree_cc.php');
        }

        $braintree_cc = new braintree_cc();

        if ( $braintree_cc->enabled ) {
          $braintree_enabled = true;

          if ( MODULE_PAYMENT_BRAINTREE_CC_TRANSACTION_SERVER == 'Sandbox' ) {
            $this->title .= ' [Sandbox]';
            $this->public_title .= ' (' . $braintree_cc->code . '; Sandbox)';
          }
        }
      }

      if ( $braintree_enabled !== true ) {
        $this->enabled = false;

        $this->description = '<div class="secWarning">' . MODULE_CONTENT_ACCOUNT_BRAINTREE_CARDS_ERROR_MAIN_MODULE . '</div>' . $this->description;
      }
    }

    function execute() {
      global $oscTemplate;

      $oscTemplate->_data['account']['account']['links']['braintree_cards'] = array('title' => $this->public_title,
                                                                                    'link' => OSCOM::link('ext/modules/content/account/braintree/cards.php', '', 'SSL'),
                                                                                    'icon' => 'newwin');
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CONTENT_ACCOUNT_BRAINTREE_CARDS_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Braintree Card Management',
        'configuration_key' => 'MODULE_CONTENT_ACCOUNT_BRAINTREE_CARDS_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to enable the Braintree Card Management module?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_CONTENT_ACCOUNT_BRAINTREE_CARDS_SORT_ORDER',
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
      return array('MODULE_CONTENT_ACCOUNT_BRAINTREE_CARDS_STATUS', 'MODULE_CONTENT_ACCOUNT_BRAINTREE_CARDS_SORT_ORDER');
    }
  }
?>
