<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  class tp_account {
    var $group = 'account';

    function prepare() {
      global $oscTemplate;

      $oscTemplate->_data[$this->group] = array('account' => array('title' => MY_ACCOUNT_TITLE,
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

    function build() {
      global $oscTemplate;

      $output = '';

      foreach ( $oscTemplate->_data[$this->group] as $group ) {
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

      $oscTemplate->addContent($output, $this->group);
    }
  }
?>
