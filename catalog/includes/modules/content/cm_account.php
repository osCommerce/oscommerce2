<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  class cm_account {
    var $code = 'cm_account';
    var $group = 'account';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function cm_account() {
      $this->title = MODULE_CONTENT_ACCOUNT_TITLE;
      $this->description = MODULE_CONTENT_ACCOUNT_DESCRIPTION;

      if ( defined('MODULE_CONTENT_ACCOUNT_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_ACCOUNT_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_ACCOUNT_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF, $oscTemplate;

      if ( basename($PHP_SELF) == FILENAME_ACCOUNT ) {
        $oscTemplate->_data['account'] = array('account' => array('title' => MY_ACCOUNT_TITLE,
                                                                  'links' => array('edit' => array('title' => MY_ACCOUNT_INFORMATION,
                                                                                                   'link' => tep_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL'),
                                                                                                   'icon' => 'person'),
                                                                                   'address_book' => array('title' => MY_ACCOUNT_ADDRESS_BOOK,
                                                                                                           'link' => tep_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'),
                                                                                                           'icon' => 'home'),
                                                                                   'password' => array('title' => MY_ACCOUNT_PASSWORD,
                                                                                                       'link' => tep_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL'),
                                                                                                       'icon' => 'key'))),
                                               'orders' => array('title' => MY_ORDERS_TITLE,
                                                                 'links' => array('history' => array('title' => MY_ORDERS_VIEW,
                                                                                                     'link' => tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'),
                                                                                                     'icon' => 'cart'))),
                                               'notifications' => array('title' => EMAIL_NOTIFICATIONS_TITLE,
                                                                        'links' => array('newsletters' => array('title' => EMAIL_NOTIFICATIONS_NEWSLETTERS,
                                                                                                                'link' => tep_href_link(FILENAME_ACCOUNT_NEWSLETTERS, '', 'SSL'),
                                                                                                                'icon' => 'mail-closed'),
                                                                                         'products' => array('title' => EMAIL_NOTIFICATIONS_PRODUCTS,
                                                                                                             'link' => tep_href_link(FILENAME_ACCOUNT_NOTIFICATIONS, '', 'SSL'),
                                                                                                             'icon' => 'heart'))));
      }
    }

    function build() {
      global $oscTemplate;

      $output = '';

      foreach ( $oscTemplate->_data['account'] as $group ) {
        $output .= '<h2>' . $group['title'] . '</h2>' .
                   '<div class="contentText">' .
                   '  <ul class="accountLinkList">';

        foreach ( $group['links'] as $entry ) {
          $output .= '    <li><span class="';

          if ( isset($entry['icon']) ) {
            $output .= ' ui-icon ui-icon-' . $entry['icon'] . ' ';
          }

          $output .= 'accountLinkListEntry"></span><a href="' . $entry['link'] . '">' . $entry['title'] . '</a></li>';
        }

        $output .= '  </ul>' .
                   '</div>';
      }

      $oscTemplate->addBlock($output, $this->group);
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CONTENT_ACCOUNT_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Customer Account Module', 'MODULE_CONTENT_ACCOUNT_STATUS', 'True', 'Do you want to enable the Customer Account module?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CONTENT_ACCOUNT_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_CONTENT_ACCOUNT_STATUS', 'MODULE_CONTENT_ACCOUNT_SORT_ORDER');
    }
  }
?>
