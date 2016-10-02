<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;

  class tp_account {
    var $group = 'account';

    function prepare() {
      global $oscTemplate;

      $oscTemplate->_data[$this->group] = array('account' => array('title' => MY_ACCOUNT_TITLE,
                                                                   'sort_order' => 10,
                                                                   'links' => array('edit' => array('title' => MY_ACCOUNT_INFORMATION,
                                                                                                    'link' => OSCOM::link('account_edit.php', '', 'SSL'),
                                                                                                    'icon' => 'fa fa-fw fa-user'),
                                                                                    'address_book' => array('title' => MY_ACCOUNT_ADDRESS_BOOK,
                                                                                                            'link' => OSCOM::link('address_book.php', '', 'SSL'),
                                                                                                            'icon' => 'fa fa-fw fa-home'),
                                                                                    'password' => array('title' => MY_ACCOUNT_PASSWORD,
                                                                                                        'link' => OSCOM::link('account_password.php', '', 'SSL'),
                                                                                                        'icon' => 'fa fa-fw fa-cog'))),
                                                'orders' => array('title' => MY_ORDERS_TITLE,
                                                                  'sort_order' => 20,
                                                                  'links' => array('history' => array('title' => MY_ORDERS_VIEW,
                                                                                                      'link' => OSCOM::link('account_history.php', '', 'SSL'),
                                                                                                      'icon' => 'fa fa-fw fa-shopping-cart'))),
                                                'notifications' => array('title' => EMAIL_NOTIFICATIONS_TITLE,
                                                                         'sort_order' => 30,
                                                                         'links' => array('newsletters' => array('title' => EMAIL_NOTIFICATIONS_NEWSLETTERS,
                                                                                                                 'link' => OSCOM::link('account_newsletters.php', '', 'SSL'),
                                                                                                                 'icon' => 'fa fa-fw fa-envelope'),
                                                                                          'products' => array('title' => EMAIL_NOTIFICATIONS_PRODUCTS,
                                                                                                              'link' => OSCOM::link('account_notifications.php', '', 'SSL'),
                                                                                                              'icon' => 'fa fa-fw fa-send'))));
    }

    function build() {
      global $oscTemplate;

      foreach ( $oscTemplate->_data[$this->group] as $key => $row ) {
        $arr[$key] = $row['sort_order'];
      }
      array_multisort($arr, SORT_ASC, $oscTemplate->_data[$this->group]);

      $output = '<div class="col-sm-12">';

      foreach ( $oscTemplate->_data[$this->group] as $group ) {
        $output .= '<h2>' . $group['title'] . '</h2>' .
                   '<div class="contentText">' .
                   '  <ul class="list-unstyled">';

        foreach ( $group['links'] as $entry ) {
          $output .= '    <li>';

          if ( isset($entry['icon']) ) {
            $output .= '<i class="' . $entry['icon'] . '"></i> ';
          }

          $output .= (tep_not_null($entry['link'])) ? '<a href="' . $entry['link'] . '">' . $entry['title'] . '</a>' : $entry['title'];

          $output .= '    </li>';
        }

        $output .= '  </ul>' .
                   '</div>';
      }

      $output .= '</div>';

      $oscTemplate->addContent($output, $this->group);
    }
  }
?>
