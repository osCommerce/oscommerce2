<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_products_action_reviews {
    public static function execute(app $app) {
      global $OSCOM_PDO, $Qp, $Qreview, $currencies, $products_price, $products_name, $breadcrumb;

      $app->setContentFile('reviews_all.php');

      $reviews_breadcrumb_link = osc_href_link('products', 'reviews');

      if ( !empty($_GET['reviews']) && isset($_GET['id']) && !empty($_GET['id']) ) {
        $Qcheck = $OSCOM_PDO->prepare('select r.reviews_id from :table_reviews r, :table_reviews_description rd where r.reviews_id = :reviews_id and r.products_id = :products_id and r.reviews_id = rd.reviews_id and rd.languages_id = :languages_id and r.reviews_status = 1');
        $Qcheck->bindInt(':reviews_id', $_GET['reviews']);
        $Qcheck->bindInt(':products_id', $_GET['id']);
        $Qcheck->bindInt(':languages_id', $_SESSION['languages_id']);
        $Qcheck->execute();

        if ( $Qcheck->fetch() === false ) {
          osc_redirect(osc_href_link('products', 'reviews&id=' . $_GET['id']));
        }

        if ( !isset($_GET['new']) ) {
          $Qupdate = $OSCOM_PDO->prepare('update :table_reviews set reviews_read = reviews_read+1 where reviews_id = :reviews_id');
          $Qupdate->bindInt(':reviews_id', $_GET['reviews']);
          $Qupdate->execute();
        }

        $Qreview = $OSCOM_PDO->prepare('select rd.reviews_text, r.reviews_rating, r.reviews_id, r.customers_name, r.date_added, r.reviews_read, p.products_id, p.products_price, p.products_tax_class_id, p.products_image, p.products_model, pd.products_name from :table_reviews r, :table_reviews_description rd, :table_products p, :table_products_description pd where r.reviews_id = :reviews_id and r.reviews_id = rd.reviews_id and rd.languages_id = :languages_id and r.products_id = p.products_id and p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = rd.languages_id');
        $Qreview->bindInt(':reviews_id', $_GET['reviews']);
        $Qreview->bindInt(':languages_id', $_SESSION['languages_id']);
        $Qreview->execute();

        if ($new_price = osc_get_products_special_price($Qreview->valueInt('products_id'))) {
          $products_price = '<del>' . $currencies->display_price($Qreview->value('products_price'), osc_get_tax_rate($Qreview->valueInt('products_tax_class_id'))) . '</del> <span class="productSpecialPrice">' . $currencies->display_price($new_price, osc_get_tax_rate($Qreview->valueInt('products_tax_class_id'))) . '</span>';
        } else {
          $products_price = $currencies->display_price($Qreview->value('products_price'), osc_get_tax_rate($Qreview->valueInt('products_tax_class_id')));
        }

        $products_name = $Qreview->value('products_name');

        if (osc_not_null($Qreview->value('products_model'))) {
          $products_name .= '<br /><span class="smallText">[' . $Qreview->value('products_model') . ']</span>';
        }

        $app->setContentFile('reviews_info.php');
      } elseif ( isset($_GET['id']) && !empty($_GET['id']) ) {
        $Qp = $OSCOM_PDO->prepare('select p.products_id, p.products_model, p.products_image, p.products_price, p.products_tax_class_id, pd.products_name from :table_products p, :table_products_description pd where p.products_id = :products_id and p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :language_id');
        $Qp->bindInt(':products_id', osc_get_prid($_GET['id']));
        $Qp->bindInt(':language_id', $_SESSION['languages_id']);
        $Qp->execute();

        if ( $Qp->fetch() !== false ) {
          if ($new_price = osc_get_products_special_price($Qp->valueInt('products_id'))) {
            $products_price = '<del>' . $currencies->display_price($Qp->value('products_price'), osc_get_tax_rate($Qp->valueInt('products_tax_class_id'))) . '</del> <span class="productSpecialPrice">' . $currencies->display_price($new_price, osc_get_tax_rate($Qp->valueInt('products_tax_class_id'))) . '</span>';
          } else {
            $products_price = $currencies->display_price($Qp->value('products_price'), osc_get_tax_rate($Qp->valueInt('products_tax_class_id')));
          }

          $products_name = $Qp->value('products_name');

          if (osc_not_null($Qp->value('products_model'))) {
            $products_name .= '<br /><span class="smallText">[' . $Qp->value('products_model') . ']</span>';
          }

          $app->setContentFile('reviews_product.php');

          $reviews_breadcrumb_link = osc_href_link('products', 'reviews&id=' . $_GET['id']);
        }
      }

      $breadcrumb->add(NAVBAR_TITLE_REVIEWS, $reviews_breadcrumb_link);
    }
  }
?>
