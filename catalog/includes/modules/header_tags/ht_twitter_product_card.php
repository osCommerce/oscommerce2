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

  class ht_twitter_product_card {
    var $code = 'ht_twitter_product_card';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function ht_twitter_product_card() {
      $this->title = MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_TITLE;
      $this->description = MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF, $oscTemplate, $currencies;

      $OSCOM_Db = Registry::get('Db');

      if ( ($PHP_SELF == 'product_info.php') && isset($_GET['products_id']) ) {
        $Qproduct = $OSCOM_Db->prepare('select p.products_id, pd.products_name, pd.products_description, p.products_image, p.products_price, p.products_quantity, p.products_tax_class_id, p.products_date_available from :table_products p, :table_products_description pd where p.products_id = :products_id and p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :language_id');
        $Qproduct->bindInt(':products_id', $_GET['products_id']);
        $Qproduct->bindInt(':language_id', $_SESSION['languages_id']);
        $Qproduct->execute();

        if ($Qproduct->fetch() !== false) {
          $data = array('card' => 'product',
                        'title' => $Qproduct->value('products_name'));

          if ( tep_not_null(MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_SITE_ID) ) {
            $data['site'] = MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_SITE_ID;
          }

          if ( tep_not_null(MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_USER_ID) ) {
            $data['creator'] = MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_USER_ID;
          }

          $product_description = substr(trim(preg_replace('/\s\s+/', ' ', strip_tags($Qproduct->value('products_description')))), 0, 197);

          if ( strlen($product_description) == 197 ) {
            $product_description .= ' ..';
          }

          $data['description'] = $product_description;

          $products_image = $Qproduct->value('products_image');

          $Qimage = $OSCOM_Db->get('products_images', 'image', ['products_id' => $Qproduct->valueInt('products_id')], 'sort_order', 1);

          if ($Qimage->fetch() !== false) {
            $products_image = $Qimage->value('image');
          }

          $data['image:src'] = OSCOM::link(DIR_WS_IMAGES . $products_image, '', 'NONSSL', false, false);

          if ($new_price = tep_get_products_special_price($Qproduct->valueInt('products_id'))) {
            $products_price = $currencies->display_price($new_price, tep_get_tax_rate($Qproduct->valueInt('products_tax_class_id')));
          } else {
            $products_price = $currencies->display_price($Qproduct->value('products_price'), tep_get_tax_rate($Qproduct->valueInt('products_tax_class_id')));
          }

          $data['data1'] = $products_price;
          $data['label1'] = $_SESSION['currency'];

          if ( $Qproduct->value('products_date_available') > date('Y-m-d H:i:s') ) {
            $data['data2'] = MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_TEXT_PRE_ORDER;
            $data['label2'] = tep_date_short($Qproduct->value('products_date_available'));
          } elseif ( $Qproduct->valueInt('products_quantity') > 0 ) {
            $data['data2'] = MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_TEXT_IN_STOCK;
            $data['label2'] = MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_TEXT_BUY_NOW;
          } else {
            $data['data2'] = MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_TEXT_OUT_OF_STOCK;
            $data['label2'] = MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_TEXT_CONTACT_US;
          }

          $result = '';

          foreach ( $data as $key => $value ) {
            $result .= '<meta name="twitter:' . tep_output_string_protected($key) . '" content="' . tep_output_string_protected($value) . '" />' . "\n";
          }

          $oscTemplate->addBlock($result, $this->group);
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Twitter Product Card Module',
        'configuration_key' => 'MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to allow Twitter Product Card tags to be added to your product information pages? Note that your product images MUST be at least 160px by 160px.',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Twitter Author @username',
        'configuration_key' => 'MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_USER_ID',
        'configuration_value' => '',
        'configuration_description' => 'Your @username at Twitter',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Twitter Shop @username',
        'configuration_key' => 'MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_SITE_ID',
        'configuration_value' => '',
        'configuration_description' => 'Your shops @username at Twitter (or leave blank if it is the same as your @username above).',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_SORT_ORDER',
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
      return array('MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_STATUS', 'MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_USER_ID', 'MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_SITE_ID', 'MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_SORT_ORDER');
    }
  }
?>
