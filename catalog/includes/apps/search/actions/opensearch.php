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
  <ShortName>' . osc_output_string(MODULE_HEADER_TAGS_OPENSEARCH_SITE_SHORT_NAME) . '</ShortName>
  <Description>' . osc_output_string(MODULE_HEADER_TAGS_OPENSEARCH_SITE_DESCRIPTION) . '</Description>';

      if (osc_not_null(MODULE_HEADER_TAGS_OPENSEARCH_SITE_CONTACT)) {
        $result .= '  <Contact>' . osc_output_string(MODULE_HEADER_TAGS_OPENSEARCH_SITE_CONTACT) . '</Contact>' . "\n";
      }

      if (osc_not_null(MODULE_HEADER_TAGS_OPENSEARCH_SITE_TAGS)) {
        $result .= '  <Tags>' . osc_output_string(MODULE_HEADER_TAGS_OPENSEARCH_SITE_TAGS) . '</Tags>' . "\n";
      }

      if (osc_not_null(MODULE_HEADER_TAGS_OPENSEARCH_SITE_ATTRIBUTION)) {
        $result .= '  <Attribution>' . osc_output_string(MODULE_HEADER_TAGS_OPENSEARCH_SITE_ATTRIBUTION) . '</Attribution>' . "\n";
      }

      if (MODULE_HEADER_TAGS_OPENSEARCH_SITE_ADULT_CONTENT == 'True') {
        $result .= '  <AdultContent>True</AdultContent>' . "\n";
      }

      if (osc_not_null(MODULE_HEADER_TAGS_OPENSEARCH_SITE_ICON)) {
        $result .= '  <Image height="16" width="16" type="image/x-icon">' . osc_output_string(MODULE_HEADER_TAGS_OPENSEARCH_SITE_ICON) . '</Image>' . "\n";
      }

      if (osc_not_null(MODULE_HEADER_TAGS_OPENSEARCH_SITE_IMAGE)) {
        $result .= '  <Image height="64" width="64" type="image/png">' . osc_output_string(MODULE_HEADER_TAGS_OPENSEARCH_SITE_IMAGE) . '</Image>' . "\n";
      }

      $result .= '  <InputEncoding>UTF-8</InputEncoding>
  <Url type="text/html" method="get" template="' . osc_href_link('search', 'q={searchTerms}', 'NONSSL', false) . '" />
</OpenSearchDescription>';

      echo $result;

      exit;
    }
  }
?>
