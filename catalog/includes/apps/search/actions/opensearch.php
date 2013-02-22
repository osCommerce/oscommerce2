<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_search_action_opensearch {
    public static function execute(app $app) {
      if ( !defined('MODULE_HEADER_TAGS_OPENSEARCH_STATUS') || (MODULE_HEADER_TAGS_OPENSEARCH_STATUS != 'True') ) {
        exit;
      }

      header('Content-Type: text/xml');

      $result = '<?xml version="1.0"?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/" xmlns:moz="http://www.mozilla.org/2006/browser/search/">
  <ShortName>' . tep_output_string(MODULE_HEADER_TAGS_OPENSEARCH_SITE_SHORT_NAME) . '</ShortName>
  <Description>' . tep_output_string(MODULE_HEADER_TAGS_OPENSEARCH_SITE_DESCRIPTION) . '</Description>';

      if (tep_not_null(MODULE_HEADER_TAGS_OPENSEARCH_SITE_CONTACT)) {
        $result .= '  <Contact>' . tep_output_string(MODULE_HEADER_TAGS_OPENSEARCH_SITE_CONTACT) . '</Contact>' . "\n";
      }

      if (tep_not_null(MODULE_HEADER_TAGS_OPENSEARCH_SITE_TAGS)) {
        $result .= '  <Tags>' . tep_output_string(MODULE_HEADER_TAGS_OPENSEARCH_SITE_TAGS) . '</Tags>' . "\n";
      }

      if (tep_not_null(MODULE_HEADER_TAGS_OPENSEARCH_SITE_ATTRIBUTION)) {
        $result .= '  <Attribution>' . tep_output_string(MODULE_HEADER_TAGS_OPENSEARCH_SITE_ATTRIBUTION) . '</Attribution>' . "\n";
      }

      if (MODULE_HEADER_TAGS_OPENSEARCH_SITE_ADULT_CONTENT == 'True') {
        $result .= '  <AdultContent>True</AdultContent>' . "\n";
      }

      if (tep_not_null(MODULE_HEADER_TAGS_OPENSEARCH_SITE_ICON)) {
        $result .= '  <Image height="16" width="16" type="image/x-icon">' . tep_output_string(MODULE_HEADER_TAGS_OPENSEARCH_SITE_ICON) . '</Image>' . "\n";
      }

      if (tep_not_null(MODULE_HEADER_TAGS_OPENSEARCH_SITE_IMAGE)) {
        $result .= '  <Image height="64" width="64" type="image/png">' . tep_output_string(MODULE_HEADER_TAGS_OPENSEARCH_SITE_IMAGE) . '</Image>' . "\n";
      }

      $result .= '  <InputEncoding>UTF-8</InputEncoding>
  <Url type="text/html" method="get" template="' . tep_href_link('search', 'q={searchTerms}', 'NONSSL', false) . '" />
</OpenSearchDescription>';

      echo $result;

      exit;
    }
  }
?>
