<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  class cm_pi_reviews {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function cm_pi_reviews() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CONTENT_PRODUCT_INFO_REVIEWS_TITLE;
      $this->description = MODULE_CONTENT_PRODUCT_INFO_REVIEWS_DESCRIPTION;

      if ( defined('MODULE_CONTENT_PRODUCT_INFO_REVIEWS_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_PRODUCT_INFO_REVIEWS_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_PRODUCT_INFO_REVIEWS_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate, $_GET;
      
      $content_width = (int)MODULE_CONTENT_PRODUCT_INFO_REVIEWS_CONTENT_WIDTH;

      $review_query = tep_db_query("select SUBSTRING_INDEX(rd.reviews_text, ' ', 20) as reviews_text, r.reviews_rating, r.reviews_id, r.customers_name, r.date_added, r.reviews_read, p.products_id, p.products_price, p.products_tax_class_id, p.products_image, p.products_model, pd.products_name from reviews r, reviews_description rd, products p, products_description pd where r.products_id = '" . (int)$_GET['products_id'] . "' and r.reviews_id = rd.reviews_id and rd.languages_id = '" . (int)$_SESSION['languages_id'] . "' and r.products_id = p.products_id and p.products_status = '1' and r.reviews_status = '1' and p.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' order by r.reviews_rating DESC limit " . (int)MODULE_CONTENT_PRODUCT_INFO_REVIEWS_CONTENT_LIMIT);
      $review_data = NULL;

      if (tep_db_num_rows($review_query) > 0) {
        while ($review = tep_db_fetch_array($review_query)) {
          $review_data .=  '<blockquote class="col-sm-6">';
          $review_data .=   '  <p>' . tep_output_string_protected($review['reviews_text']) . ' ... </p>';
          $review_name = tep_output_string_protected($review['customers_name']);
          $review_data .=   '  <footer>' . sprintf(MODULE_CONTENT_PRODUCT_INFO_REVIEWS_TEXT_RATED, tep_draw_stars($review['reviews_rating']), $review_name, $review_name) . '</footer>';
          $review_data .=   '</blockquote>';
        }
        
        ob_start();
        include(DIR_WS_MODULES . 'content/' . $this->group . '/templates/reviews.php');
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
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Reviews Module', 'MODULE_CONTENT_PRODUCT_INFO_REVIEWS_STATUS', 'True', 'Should the reviews block be shown on the product info page?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Width', 'MODULE_CONTENT_PRODUCT_INFO_REVIEWS_CONTENT_WIDTH', '6', 'What width container should the content be shown in?', '6', '1', 'tep_cfg_select_option(array(\'12\', \'11\', \'10\', \'9\', \'8\', \'7\', \'6\', \'5\', \'4\', \'3\', \'2\', \'1\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Number of Reviews', 'MODULE_CONTENT_PRODUCT_INFO_REVIEWS_CONTENT_LIMIT', '4', 'How many reviews should be shown?', '6', '1', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CONTENT_PRODUCT_INFO_REVIEWS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from configuration where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_CONTENT_PRODUCT_INFO_REVIEWS_STATUS', 'MODULE_CONTENT_PRODUCT_INFO_REVIEWS_CONTENT_WIDTH', 'MODULE_CONTENT_PRODUCT_INFO_REVIEWS_CONTENT_LIMIT', 'MODULE_CONTENT_PRODUCT_INFO_REVIEWS_SORT_ORDER');
    }
  }

