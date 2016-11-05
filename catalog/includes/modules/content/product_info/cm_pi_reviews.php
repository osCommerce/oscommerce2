<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class cm_pi_reviews {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = OSCOM::getDef('module_content_product_info_reviews_title');
      $this->description = OSCOM::getDef('module_content_product_info_reviews_description');
      $this->description .= '<div class="secWarning">' . OSCOM::getDef('module_content_bootstrap_row_description') . '</div>';

      if ( defined('MODULE_CONTENT_PRODUCT_INFO_REVIEWS_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_PRODUCT_INFO_REVIEWS_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_PRODUCT_INFO_REVIEWS_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate, $_GET;

      $OSCOM_Db = Registry::get('Db');
      $OSCOM_Language = Registry::get('Language');

      $Qreviews = $OSCOM_Db->prepare('select substring_index(rd.reviews_text, " ", 20) as reviews_text, r.reviews_rating, r.reviews_id, r.customers_name, r.date_added, r.reviews_read, p.products_id, p.products_price, p.products_tax_class_id, p.products_image, p.products_model, pd.products_name from :table_reviews r, :table_reviews_description rd, :table_products p, :table_products_description pd where r.products_id = :products_id and r.reviews_status = 1 and r.reviews_id = rd.reviews_id and rd.languages_id = :languages_id and r.products_id = p.products_id and p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = rd.languages_id order by r.reviews_rating desc limit :limit');
      $Qreviews->bindInt(':products_id', $_GET['products_id']);
      $Qreviews->bindInt(':languages_id', $OSCOM_Language->getId());
      $Qreviews->bindInt(':limit', MODULE_CONTENT_PRODUCT_INFO_REVIEWS_CONTENT_LIMIT);
      $Qreviews->execute();

      if ($Qreviews->fetch() !== false) {
        $content_width = (int)MODULE_CONTENT_PRODUCT_INFO_REVIEWS_CONTENT_WIDTH;
        $review_data = '';

        do {
          $review_data .= '<blockquote class="col-sm-6">' .
                          '  <p>' . $Qreviews->valueProtected('reviews_text') . ' ... </p>' .
                          '  <footer>' . OSCOM::getDef('module_content_product_info_reviews_text_rated', ['reviews_rating' => HTML::stars($Qreviews->valueInt('reviews_rating')), 'customers_name' => $Qreviews->valueProtected('customers_name')]) . '</footer>' .
                          '</blockquote>';
        } while ($Qreviews->fetch());

        ob_start();
        include('includes/modules/content/' . $this->group . '/templates/reviews.php');
        $template = ob_get_clean();

        $oscTemplate->addContent($template, $this->group);
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CONTENT_PRODUCT_INFO_REVIEWS_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Reviews Module',
        'configuration_key' => 'MODULE_CONTENT_PRODUCT_INFO_REVIEWS_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Should the reviews block be shown on the product info page?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Content Width',
        'configuration_key' => 'MODULE_CONTENT_PRODUCT_INFO_REVIEWS_CONTENT_WIDTH',
        'configuration_value' => '6',
        'configuration_description' => 'What width container should the content be shown in?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'12\', \'11\', \'10\', \'9\', \'8\', \'7\', \'6\', \'5\', \'4\', \'3\', \'2\', \'1\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Number of Reviews',
        'configuration_key' => 'MODULE_CONTENT_PRODUCT_INFO_REVIEWS_CONTENT_LIMIT',
        'configuration_value' => '4',
        'configuration_description' => 'How many reviews should be shown?',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_CONTENT_PRODUCT_INFO_REVIEWS_SORT_ORDER',
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
      return array('MODULE_CONTENT_PRODUCT_INFO_REVIEWS_STATUS', 'MODULE_CONTENT_PRODUCT_INFO_REVIEWS_CONTENT_WIDTH', 'MODULE_CONTENT_PRODUCT_INFO_REVIEWS_CONTENT_LIMIT', 'MODULE_CONTENT_PRODUCT_INFO_REVIEWS_SORT_ORDER');
    }
  }

