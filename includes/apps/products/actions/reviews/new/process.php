<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_products_action_reviews_new_process {
    public static function execute(app $app) {
      global $OSCOM_Customer, $OSCOM_MessageStack, $OSCOM_PDO, $Qcustomer;

      if ( isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken']) ) {
        $rating = isset($_POST['rating']) ? trim($_POST['rating']) : null;
        $review = isset($_POST['review']) ? trim($_POST['review']) : null;

        $error = false;

        if ( strlen($review) < REVIEW_TEXT_MIN_LENGTH ) {
          $error = true;

          $OSCOM_MessageStack->addError('review', JS_REVIEW_TEXT);
        }

        if (($rating < 1) || ($rating > 5)) {
          $error = true;

          $OSCOM_MessageStack->addError('review', JS_REVIEW_RATING);
        }

        if ($error == false) {
          $sql_data_array = array('products_id' => osc_get_prid($_GET['id']),
                                  'customers_id' => $OSCOM_Customer->getID(),
                                  'customers_name' => $Qcustomer->value('customers_firstname') . ' ' . $Qcustomer->value('customers_lastname'),
                                  'reviews_rating' => $rating,
                                  'date_added' => 'now()');

          $OSCOM_PDO->perform('reviews', $sql_data_array);

          $insert_id = $OSCOM_PDO->lastInsertId();

          $OSCOM_PDO->perform('reviews_description', array('reviews_id' => $insert_id, 'languages_id' => $_SESSION['languages_id'], 'reviews_text' => $review));

          $OSCOM_MessageStack->addSuccess('product_reviews', TEXT_REVIEW_RECEIVED);

          osc_redirect(osc_href_link('products', 'reviews&id=' . $_GET['id']));
        }
      }
    }
  }
?>
