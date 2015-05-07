<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\Registry;

  class bm_reviews {
    var $code = 'bm_reviews';
    var $group = 'boxes';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function bm_reviews() {
      $this->title = MODULE_BOXES_REVIEWS_TITLE;
      $this->description = MODULE_BOXES_REVIEWS_DESCRIPTION;

      if ( defined('MODULE_BOXES_REVIEWS_STATUS') ) {
        $this->sort_order = MODULE_BOXES_REVIEWS_SORT_ORDER;
        $this->enabled = (MODULE_BOXES_REVIEWS_STATUS == 'True');

        $this->group = ((MODULE_BOXES_REVIEWS_CONTENT_PLACEMENT == 'Left Column') ? 'boxes_column_left' : 'boxes_column_right');
      }
    }

    function execute() {
      global $currencies, $oscTemplate;

      $OSCOM_Db = Registry::get('Db');

      $reviews_box_contents = '';

      $sql_query = 'select r.reviews_id from :table_reviews r, :table_reviews_description rd, :table_products p, :table_products_description pd where r.reviews_status = 1 and r.products_id = p.products_id and p.products_status = 1 and r.reviews_id = rd.reviews_id and rd.languages_id = :languages_id and p.products_id = pd.products_id and pd.language_id = rd.languages_id';

      if (isset($_GET['products_id'])) {
        $sql_query .= ' and p.products_id = :products_id';
      }

      $sql_query .= ' order by r.reviews_id desc limit ' . (int)MAX_RANDOM_SELECT_REVIEWS;

      $Qcheck = $OSCOM_Db->prepare($sql_query);
      $Qcheck->bindInt(':languages_id', $_SESSION['languages_id']);

      if (isset($_GET['products_id'])) {
        $Qcheck->bindInt(':products_id', $_GET['products_id']);
      }

      $Qcheck->execute();

      $result = $Qcheck->fetchAll();

      if (count($result) > 0) {
        $result = $result[mt_rand(0, count($result)-1)];

        $Qreview = $OSCOM_Db->prepare('select r.reviews_id, r.reviews_rating, substring(rd.reviews_text, 1, 60) as reviews_text, p.products_id, p.products_image, pd.products_name from :table_reviews r, :table_reviews_description rd, :table_products p, :table_products_description pd where r.reviews_id = :reviews_id and r.reviews_id = rd.reviews_id and rd.languages_id = :languages_id and r.products_id = p.products_id and p.products_id = pd.products_id and pd.language_id = rd.languages_id');
        $Qreview->bindInt(':reviews_id', $result['reviews_id']);
        $Qreview->bindInt(':languages_id', $_SESSION['languages_id']);
        $Qreview->execute();

        if ($Qreview->fetch() !== false) {
// display random review box
          $rand_review_text = tep_break_string($Qreview->valueProtected('reviews_text'), 15, '-<br />');

          $reviews_box_contents = '<div class="text-center"><a href="' . tep_href_link('product_reviews.php', 'products_id=' . $Qreview->valueInt('products_id')) . '">' . tep_image(DIR_WS_IMAGES . $Qreview->value('products_image'), $Qreview->value('products_name'), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a></div><div><a href="' . tep_href_link('product_reviews.php', 'products_id=' . $Qreview->valueInt('products_id')) . '">' . $rand_review_text . '</a>...</div><div class="text-center" title="' .  sprintf(MODULE_BOXES_REVIEWS_BOX_TEXT_OF_5_STARS, $Qreview->valueInt('reviews_rating')) . '">' . tep_draw_stars($Qreview->valueInt('reviews_rating')) . '</div>';
        }
      } elseif (isset($_GET['products_id'])) {
// display 'write a review' box
        $reviews_box_contents = '<span class="glyphicon glyphicon-thumbs-up"></span> <a href="' . tep_href_link('product_reviews_write.php', 'products_id=' . (int)$_GET['products_id']) . '">' . MODULE_BOXES_REVIEWS_BOX_WRITE_REVIEW .'</a>';
      } else {
// display 'no reviews' box
        $reviews_box_contents = '<p>' . MODULE_BOXES_REVIEWS_BOX_NO_REVIEWS . '</p>';
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
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Reviews Module', 'MODULE_BOXES_REVIEWS_STATUS', 'True', 'Do you want to add the module to your shop?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Placement', 'MODULE_BOXES_REVIEWS_CONTENT_PLACEMENT', 'Right Column', 'Should the module be loaded in the left or right column?', '6', '1', 'tep_cfg_select_option(array(\'Left Column\', \'Right Column\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_BOXES_REVIEWS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from configuration where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_BOXES_REVIEWS_STATUS', 'MODULE_BOXES_REVIEWS_CONTENT_PLACEMENT', 'MODULE_BOXES_REVIEWS_SORT_ORDER');
    }
  }

