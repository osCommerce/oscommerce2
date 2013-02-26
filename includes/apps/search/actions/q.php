<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_search_action_q {
    public static function execute(app $app) {
      global $dfrom, $dto, $pfrom, $pto, $keywords, $messageStack, $breadcrumb;

      $app->setContentFile('results.php');

      $error = false;

      if ( (isset($_GET['q']) && empty($_GET['q'])) &&
           (isset($_GET['dfrom']) && (empty($_GET['dfrom']) || ($_GET['dfrom'] == DOB_FORMAT_STRING))) &&
           (isset($_GET['dto']) && (empty($_GET['dto']) || ($_GET['dto'] == DOB_FORMAT_STRING))) &&
           (isset($_GET['pfrom']) && !is_numeric($_GET['pfrom'])) &&
           (isset($_GET['pto']) && !is_numeric($_GET['pto'])) ) {
        $error = true;

        $messageStack->add_session('search', ERROR_AT_LEAST_ONE_INPUT);
      } else {
        $dfrom = '';
        $dto = '';
        $pfrom = '';
        $pto = '';
        $keywords = '';

        if (isset($_GET['dfrom'])) {
          $dfrom = (($_GET['dfrom'] == DOB_FORMAT_STRING) ? '' : $_GET['dfrom']);
        }

        if (isset($_GET['dto'])) {
          $dto = (($_GET['dto'] == DOB_FORMAT_STRING) ? '' : $_GET['dto']);
        }

        if (isset($_GET['pfrom'])) {
          $pfrom = $_GET['pfrom'];
        }

        if (isset($_GET['pto'])) {
          $pto = $_GET['pto'];
        }

        $keywords = trim($_GET['q']);

        $date_check_error = false;
        if (osc_not_null($dfrom)) {
          if (!osc_checkdate($dfrom, DOB_FORMAT_STRING, $dfrom_array)) {
            $error = true;
            $date_check_error = true;

            $messageStack->add_session('search', ERROR_INVALID_FROM_DATE);
          }
        }

        if (osc_not_null($dto)) {
          if (!osc_checkdate($dto, DOB_FORMAT_STRING, $dto_array)) {
            $error = true;
            $date_check_error = true;

            $messageStack->add_session('search', ERROR_INVALID_TO_DATE);
          }
        }

        if (($date_check_error == false) && osc_not_null($dfrom) && osc_not_null($dto)) {
          if (mktime(0, 0, 0, $dfrom_array[1], $dfrom_array[2], $dfrom_array[0]) > mktime(0, 0, 0, $dto_array[1], $dto_array[2], $dto_array[0])) {
            $error = true;

            $messageStack->add_session('search', ERROR_TO_DATE_LESS_THAN_FROM_DATE);
          }
        }

        $price_check_error = false;
        if (osc_not_null($pfrom)) {
          if (!settype($pfrom, 'double')) {
            $error = true;
            $price_check_error = true;

            $messageStack->add_session('search', ERROR_PRICE_FROM_MUST_BE_NUM);
          }
        }

        if (osc_not_null($pto)) {
          if (!settype($pto, 'double')) {
            $error = true;
            $price_check_error = true;

            $messageStack->add_session('search', ERROR_PRICE_TO_MUST_BE_NUM);
          }
        }

        if (($price_check_error == false) && is_float($pfrom) && is_float($pto)) {
          if ($pfrom >= $pto) {
            $error = true;

            $messageStack->add_session('search', ERROR_PRICE_TO_LESS_THAN_PRICE_FROM);
          }
        }

        if (osc_not_null($keywords)) {
          if (!osc_parse_search_string($keywords, $search_keywords)) {
            $error = true;

            $messageStack->add_session('search', ERROR_INVALID_KEYWORDS);
          }
        }
      }

      if (empty($dfrom) && empty($dto) && empty($pfrom) && empty($pto) && empty($keywords)) {
        $error = true;

        $messageStack->add_session('search', ERROR_AT_LEAST_ONE_INPUT);
      }

      if ($error == true) {
        osc_redirect(osc_href_link('search', osc_get_all_get_params(array('search', 'q')), 'NONSSL', true, false));
      }

      $breadcrumb->add(NAVBAR_TITLE_2, osc_href_link('search', osc_get_all_get_params(array('search')), 'NONSSL', true, false));
    }
  }
?>
