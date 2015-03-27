<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  class tp_account {
    var $group = 'account';

    function prepare() {
      global $oscTemplate;

      $oscTemplate->_data[$this->group] = array('account' => array('title' => MY_ACCOUNT_TITLE,
                                                                   'links' => array('edit' => array('title' => MY_ACCOUNT_INFORMATION,
                                                                                                    'link' => tep_href_link('account_edit.php', '', 'SSL'),
                                                                                                    'style' => 'btn btn-info',
                                                                                                    'icon' => 'fa fa-user fa-4x'),
                                                                                    'address_book' => array('title' => MY_ACCOUNT_ADDRESS_BOOK,
                                                                                                            'link' => tep_href_link('address_book.php', '', 'SSL'),
                                                                                                            'style' => 'btn btn-info',
                                                                                                            'icon' => 'fa fa-home fa-4x'),
                                                                                    'password' => array('title' => MY_ACCOUNT_PASSWORD,
                                                                                                        'link' => tep_href_link('account_password.php', '', 'SSL'),
                                                                                                        'style' => 'btn btn-info',
                                                                                                        'icon' => 'fa fa-cog fa-4x'))),
                                                'orders' => array('title' => MY_ORDERS_TITLE,
                                                                  'links' => array('history' => array('title' => MY_ORDERS_VIEW,
                                                                                                      'link' => tep_href_link('account_history.php', '', 'SSL'),
                                                                                                      'style' => 'btn btn-info',
                                                                                                      'icon' => 'fa fa-shopping-cart fa-4x'))),
                                                'notifications' => array('title' => EMAIL_NOTIFICATIONS_TITLE,
                                                                         'links' => array('newsletters' => array('title' => EMAIL_NOTIFICATIONS_NEWSLETTERS,
                                                                                                                 'link' => tep_href_link('account_newsletters.php', '', 'SSL'),
                                                                                                                 'style' => 'btn btn-info',
                                                                                                                 'icon' => 'fa fa-envelope fa-4x'),
                                                                                          'products' => array('title' => EMAIL_NOTIFICATIONS_PRODUCTS,
                                                                                                              'link' => tep_href_link('account_notifications.php', '', 'SSL'),
                                                                                                              'style' => 'btn btn-info',
                                                                                                              'icon' => 'fa fa-send fa-4x'))),
                                                'logoff' => array('title' => MY_ACCOUNT_LOGOFF,
                                                                  'links' => array('history' => array('title' => MY_ACCOUNT_LOGOFF,
                                                                                                      'link' => tep_href_link('logoff.php', '', 'SSL'),
                                                                                                      'style' => 'btn btn-danger',
                                                                                                      'icon' => 'fa fa-sign-out fa-4x'))));
    }

    function build() {
      global $oscTemplate;
      
      $output = '<div class="row">';

      foreach ( $oscTemplate->_data[$this->group] as $group ) {

        foreach ( $group['links'] as $entry ) {
          $output .= '<div class="col-sm-6"><a class="' . $entry['style'] . ' btn-block" role="button" href="' . $entry['link'] . '"><i class="';

          if ( isset($entry['icon']) ) {
            $output .= $entry['icon'];
          }

          $output .= '"></i><br>' . $entry['title'] . '</a><br></div>';
        }
        
      }
      
      $output .= '</div>';

      $oscTemplate->addContent($output, $this->group);
    }
  }
?>
