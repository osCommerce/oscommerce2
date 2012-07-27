<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce

  Released under the GNU General Public License
*/

  class ht_robot_noindex {
    var $code = 'ht_robot_noindex';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function ht_robot_noindex() {
      $this->title = MODULE_HEADER_TAGS_ROBOT_NOINDEX_TITLE;
      $this->description = MODULE_HEADER_TAGS_ROBOT_NOINDEX_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_ROBOT_NOINDEX_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_ROBOT_NOINDEX_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_ROBOT_NOINDEX_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF, $oscTemplate;

      if (tep_not_null(MODULE_HEADER_TAGS_ROBOT_NOINDEX_PAGES)) {
        $pages_array = array();

        foreach (explode("\n", MODULE_HEADER_TAGS_ROBOT_NOINDEX_PAGES) as $page) {
          $page = trim($page);

          if (!empty($page)) {
            $pages_array[] = $page;
          }
        }

        if (in_array(basename($PHP_SELF), $pages_array)) {
          $oscTemplate->addBlock('<meta name="robots" content="noindex" />' . "\n", $this->group);
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_ROBOT_NOINDEX_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Robot NoIndex Module', 'MODULE_HEADER_TAGS_ROBOT_NOINDEX_STATUS', 'True', 'Do you want to enable the Robot NoIndex module?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Pages', 'MODULE_HEADER_TAGS_ROBOT_NOINDEX_PAGES', '" . implode("\n", $this->get_default_pages()) . "', 'The pages to add the meta robot noindex tag to.', '6', '0', 'ht_robot_noindex_show_pages', 'ht_robot_noindex_edit_pages(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_HEADER_TAGS_ROBOT_NOINDEX_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_HEADER_TAGS_ROBOT_NOINDEX_STATUS', 'MODULE_HEADER_TAGS_ROBOT_NOINDEX_PAGES', 'MODULE_HEADER_TAGS_ROBOT_NOINDEX_SORT_ORDER');
    }

    function get_default_pages() {
      return array('account.php',
                   'account_edit.php',
                   'account_history.php',
                   'account_history_info.php',
                   'account_newsletters.php',
                   'account_notifications.php',
                   'account_password.php',
                   'address_book.php',
                   'address_book_process.php',
                   'checkout_confirmation.php',
                   'checkout_payment.php',
                   'checkout_payment_address.php',
                   'checkout_process.php',
                   'checkout_shipping.php',
                   'checkout_shipping_address.php',
                   'checkout_success.php',
                   'cookie_usage.php',
                   'create_account.php',
                   'create_account_success.php',
                   'login.php',
                   'logoff.php',
                   'password_forgotten.php',
                   'password_reset.php',
                   'product_reviews_write.php',
                   'shopping_cart.php',
                   'ssl_check.php',
                   'tell_a_friend.php');
    }
  }

  function ht_robot_noindex_show_pages($text) {
    return nl2br($text);
  }

  function ht_robot_noindex_edit_pages($text, $key) {
    return tep_draw_textarea_field('configuration[' . $key . ']', false, 35, 5, $text);
  }
?>
