<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;

  class tp_account {
    var $group = 'account';

    function prepare() {
      global $oscTemplate;

      $oscTemplate->_data[$this->group] = array('account' => array('title' => OSCOM::getDef('my_account_title'),
                                                                   'sort_order' => 10,
                                                                   'links' => array('edit' => array('title' => OSCOM::getDef('my_account_information'),
                                                                                                    'link' => OSCOM::link('account_edit.php'),
                                                                                                    'icon' => 'fa fa-fw fa-user'),
                                                                                    'address_book' => array('title' => OSCOM::getDef('my_account_address_book'),
                                                                                                            'link' => OSCOM::link('address_book.php'),
                                                                                                            'icon' => 'fa fa-fw fa-home'),
                                                                                    'password' => array('title' => OSCOM::getDef('my_account_password'),
                                                                                                        'link' => OSCOM::link('account_password.php'),
                                                                                                        'icon' => 'fa fa-fw fa-cog'))),
                                                'orders' => array('title' => OSCOM::getDef('my_orders_title'),
                                                                  'sort_order' => 20,
                                                                  'links' => array('history' => array('title' => OSCOM::getDef('my_orders_view'),
                                                                                                      'link' => OSCOM::link('account_history.php'),
                                                                                                      'icon' => 'fa fa-fw fa-shopping-cart'))),
                                                'notifications' => array('title' => OSCOM::getDef('email_notifications_title'),
                                                                         'sort_order' => 30,
                                                                         'links' => array('newsletters' => array('title' => OSCOM::getDef('email_notifications_newsletters'),
                                                                                                                 'link' => OSCOM::link('account_newsletters.php'),
                                                                                                                 'icon' => 'fa fa-fw fa-envelope'),
                                                                                          'products' => array('title' => OSCOM::getDef('email_notifications_products'),
                                                                                                              'link' => OSCOM::link('account_notifications.php'),
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
