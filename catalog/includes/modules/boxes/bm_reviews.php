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

  class bm_reviews {
    var $code = 'bm_reviews';
    var $group = 'boxes';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->title = OSCOM::getDef('module_boxes_reviews_title');
      $this->description = OSCOM::getDef('module_boxes_reviews_description');

      if ( defined('MODULE_BOXES_REVIEWS_STATUS') ) {
        $this->sort_order = MODULE_BOXES_REVIEWS_SORT_ORDER;
        $this->enabled = (MODULE_BOXES_REVIEWS_STATUS == 'True');

        $this->group = ((MODULE_BOXES_REVIEWS_CONTENT_PLACEMENT == 'Left Column') ? 'boxes_column_left' : 'boxes_column_right');
      }
    }

    function execute() {
      global $currencies, $oscTemplate;

      $OSCOM_Db = Registry::get('Db');
      $OSCOM_Language = Registry::get('Language');

      $reviews_box_contents = '';

      $sql_query = 'select r.reviews_id from :table_reviews r, :table_reviews_description rd, :table_products p, :table_products_description pd where r.reviews_status = 1 and r.products_id = p.products_id and p.products_status = 1 and r.reviews_id = rd.reviews_id and rd.languages_id = :languages_id and p.products_id = pd.products_id and pd.language_id = rd.languages_id';

      if (isset($_GET['products_id'])) {
        $sql_query .= ' and p.products_id = :products_id';
      }

      $sql_query .= ' order by r.reviews_id desc limit ' . (int)MAX_RANDOM_SELECT_REVIEWS;

      $Qcheck = $OSCOM_Db->prepare($sql_query);
      $Qcheck->bindInt(':languages_id', $OSCOM_Language->getId());

      if (isset($_GET['products_id'])) {
        $Qcheck->bindInt(':products_id', $_GET['products_id']);
      }

      $Qcheck->execute();

      $result = $Qcheck->fetchAll();

      if (count($result) > 0) {
        $result = $result[mt_rand(0, count($result)-1)];

        $Qreview = $OSCOM_Db->prepare('select r.reviews_id, r.reviews_rating, substring(rd.reviews_text, 1, 60) as reviews_text, p.products_id, p.products_image, pd.products_name from :table_reviews r, :table_reviews_description rd, :table_products p, :table_products_description pd where r.reviews_id = :reviews_id and r.reviews_id = rd.reviews_id and rd.languages_id = :languages_id and r.products_id = p.products_id and p.products_id = pd.products_id and pd.language_id = rd.languages_id');
        $Qreview->bindInt(':reviews_id', $result['reviews_id']);
        $Qreview->bindInt(':languages_id', $OSCOM_Language->getId());
        $Qreview->execute();

        if ($Qreview->fetch() !== false) {
// display random review box
          $rand_review_text = tep_break_string($Qreview->valueProtected('reviews_text'), 15, '-<br />');

          $reviews_box_contents = '<div class="text-center"><a href="' . OSCOM::link('product_reviews.php', 'products_id=' . $Qreview->valueInt('products_id')) . '">' . HTML::image(OSCOM::linkImage($Qreview->value('products_image')), $Qreview->value('products_name'), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a></div><div><a href="' . OSCOM::link('product_reviews.php', 'products_id=' . $Qreview->valueInt('products_id')) . '">' . $rand_review_text . '</a>...</div><div class="text-center" title="' .  OSCOM::getDef('module_boxes_reviews_box_text_of_5_stars', ['reviews_rating' => $Qreview->valueInt('reviews_rating')]) . '">' . HTML::stars($Qreview->valueInt('reviews_rating')) . '</div>';
        }
      } elseif (isset($_GET['products_id'])) {
// display 'write a review' box
        $reviews_box_contents = '<span class="fa fa-thumbs-up"></span> <a href="' . OSCOM::link('product_reviews_write.php', 'products_id=' . (int)$_GET['products_id']) . '">' . OSCOM::getDef('module_boxes_reviews_box_write_review') .'</a>';
      } else {
// display 'no reviews' box
        $reviews_box_contents = '<p>' . OSCOM::getDef('module_boxes_reviews_box_no_reviews') . '</p>';
      }

      ob_start();
      include('includes/modules/boxes/templates/reviews.php');
      $data = ob_get_clean();

      $oscTemplate->addBlock($data, $this->group);
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_BOXES_REVIEWS_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Reviews Module',
        'configuration_key' => 'MODULE_BOXES_REVIEWS_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to add the module to your shop?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Content Placement',
        'configuration_key' => 'MODULE_BOXES_REVIEWS_CONTENT_PLACEMENT',
        'configuration_value' => 'Right Column',
        'configuration_description' => 'Should the module be loaded in the left or right column?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'Left Column\', \'Right Column\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_BOXES_REVIEWS_SORT_ORDER',
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
      return array('MODULE_BOXES_REVIEWS_STATUS', 'MODULE_BOXES_REVIEWS_CONTENT_PLACEMENT', 'MODULE_BOXES_REVIEWS_SORT_ORDER');
    }
  }

