<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  if ( !class_exists('OSCOM_Braintree') ) {
    include(DIR_FS_CATALOG . 'includes/apps/braintree/OSCOM_Braintree.php');
  }

  class cm_account_braintree_cards {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;
    var $_app;

    function cm_account_braintree_cards() {
      global $language;

      $this->_app = new OSCOM_Braintree();
      $this->_app->loadLanguageFile('shop/account_cards_page.php');

      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = $this->_app->getDef('account_braintree_cards_title');
      $this->description = $this->_app->getDef('account_braintree_cards_description') . '<div align="center">' . $this->_app->drawButton($this->_app->getDef('accouint_braintree_cards_legacy_admin_app_button'), tep_href_link('braintree.php', 'action=configure&module=CC'), 'primary', null, true) . '</div>';

      if ( defined('MODULE_CONTENT_ACCOUNT_BRAINTREE_CARDS_SORT_ORDER') ) {
        $this->sort_order = MODULE_CONTENT_ACCOUNT_BRAINTREE_CARDS_SORT_ORDER;
        $this->enabled = defined('OSCOM_APP_PAYPAL_BRAINTREE_CC_STATUS') && in_array(OSCOM_APP_PAYPAL_BRAINTREE_CC_STATUS, array('1', '0')) ? true : false;
      }

      $this->public_title = $this->_app->getDef('account_braintree_cards_link_title');

      $braintree_enabled = false;

      if ( defined('MODULE_PAYMENT_INSTALLED') && tep_not_null(MODULE_PAYMENT_INSTALLED) && in_array('braintree_cc.php', explode(';', MODULE_PAYMENT_INSTALLED)) ) {
        if ( !class_exists('braintree_cc') ) {
          include(DIR_FS_CATALOG . 'includes/languages/' . $language . '/modules/payment/braintree_cc.php');
          include(DIR_FS_CATALOG . 'includes/modules/payment/braintree_cc.php');
        }

        $braintree_cc = new braintree_cc();

        if ( $braintree_cc->enabled ) {
          $braintree_enabled = true;

          if ( defined('OSCOM_APP_PAYPAL_BRAINTREE_CC_STATUS') ) {
            if ( OSCOM_APP_PAYPAL_BRAINTREE_CC_STATUS == '0' ) {
              $this->title .= ' [Sandbox]';
              $this->public_title .= ' (' . $braintree_cc->code . '; Sandbox)';
            }
          }
        }
      }

      if ( $braintree_enabled !== true ) {
        $this->enabled = false;

        $this->description = '<div class="secWarning">' . $this->_app->getDef('account_braintree_cards_error_main_module') . '</div>' . $this->description;
      }
    }

    function execute() {
      global $oscTemplate;

      $oscTemplate->_data['account']['account']['links']['braintree_cards'] = array('title' => $this->public_title,
                                                                                    'link' => tep_href_link('ext/modules/content/account/braintree/cards.php', '', 'SSL'),
                                                                                    'icon' => 'newwin');
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'OSCOM_APP_PAYPAL_BRAINTREE_CC_STATUS'");
      if ( tep_db_num_rows($check_query) ) {
        $check = tep_db_fetch_array($check_query);

        return tep_not_null($check['configuration_value']);
      }

      return false;
    }

    function install() {
      tep_redirect(tep_href_link('braintree.php', 'action=configure'));
    }

    function remove() {
      tep_redirect(tep_href_link('braintree.php', 'action=configure'));
    }

    function keys() {
      return array('MODULE_CONTENT_ACCOUNT_BRAINTREE_CARDS_SORT_ORDER');
    }
  }
?>
