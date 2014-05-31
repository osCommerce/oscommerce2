<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class cm_account_sage_pay_cards {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function cm_account_sage_pay_cards() {
      global $language;

      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_TITLE;
      $this->description = MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_DESCRIPTION;

      if ( defined('MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_STATUS == 'True');
      }

      $this->public_title = MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_LINK_TITLE;

      $sage_pay_enabled = false;

      if ( defined('MODULE_PAYMENT_INSTALLED') && tep_not_null(MODULE_PAYMENT_INSTALLED) && in_array('sage_pay_direct.php', explode(';', MODULE_PAYMENT_INSTALLED)) ) {
        if ( !class_exists('sage_pay_direct') ) {
          include(DIR_FS_CATALOG . 'includes/languages/' . $language . '/modules/payment/sage_pay_direct.php');
          include(DIR_FS_CATALOG . 'includes/modules/payment/sage_pay_direct.php');
        }

        $sage_pay_direct = new sage_pay_direct();

        if ( $sage_pay_direct->enabled ) {
          $sage_pay_enabled = true;

          if ( MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_SERVER == 'Test' ) {
            $this->title .= ' [Test]';
            $this->public_title .= ' (' . $sage_pay_direct->code . '; Test)';
          }
        }
      }

      if ( $sage_pay_enabled !== true ) {
        $this->enabled = false;

        $this->description = '<div class="secWarning">' . MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_ERROR_MAIN_MODULE . '</div>' . $this->description;
      }
    }

    function execute() {
      global $oscTemplate;

      $oscTemplate->_data['account']['account']['links']['sage_pay_cards'] = array('title' => $this->public_title,
                                                                                   'link' => tep_href_link('ext/modules/content/account/sage_pay/cards.php', '', 'SSL'),
                                                                                   'icon' => 'newwin');
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Sage Pay Card Management', 'MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_STATUS', 'True', 'Do you want to enable the Sage Pay Card Management module?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_STATUS', 'MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_SORT_ORDER');
    }
  }
?>
