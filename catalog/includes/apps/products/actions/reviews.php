<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_products_action_reviews {
    public static function execute(app $app) {
      global $breadcrumb;

      $app->setContentFile('reviews_all.php');

      $breadcrumb->add(NAVBAR_TITLE_REVIEWS, tep_href_link('products', 'reviews'));
    }
  }
?>
