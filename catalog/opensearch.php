<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  header('Content-Type: text/xml');

  require('includes/application_top.php');

  if ( !defined('MODULE_HEADER_TAGS_OPENSEARCH_STATUS') || (MODULE_HEADER_TAGS_OPENSEARCH_STATUS != 'True') ) {
    exit;
  }

  echo '<?xml version="1.0"?>' . "\n";
?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/" xmlns:moz="http://www.mozilla.org/2006/browser/search/">
  <ShortName><?php echo osc_output_string(MODULE_HEADER_TAGS_OPENSEARCH_SITE_SHORT_NAME); ?></ShortName>
  <Description><?php echo osc_output_string(MODULE_HEADER_TAGS_OPENSEARCH_SITE_DESCRIPTION); ?></Description>
<?php
  if (osc_not_null(MODULE_HEADER_TAGS_OPENSEARCH_SITE_CONTACT)) {
    echo '  <Contact>' . osc_output_string(MODULE_HEADER_TAGS_OPENSEARCH_SITE_CONTACT) . '</Contact>' . "\n";
  }

  if (osc_not_null(MODULE_HEADER_TAGS_OPENSEARCH_SITE_TAGS)) {
    echo '  <Tags>' . osc_output_string(MODULE_HEADER_TAGS_OPENSEARCH_SITE_TAGS) . '</Tags>' . "\n";
  }

  if (osc_not_null(MODULE_HEADER_TAGS_OPENSEARCH_SITE_ATTRIBUTION)) {
    echo '  <Attribution>' . osc_output_string(MODULE_HEADER_TAGS_OPENSEARCH_SITE_ATTRIBUTION) . '</Attribution>' . "\n";
  }

  if (MODULE_HEADER_TAGS_OPENSEARCH_SITE_ADULT_CONTENT == 'True') {
    echo '  <AdultContent>True</AdultContent>' . "\n";
  }

  if (osc_not_null(MODULE_HEADER_TAGS_OPENSEARCH_SITE_ICON)) {
    echo '  <Image height="16" width="16" type="image/x-icon">' . osc_output_string(MODULE_HEADER_TAGS_OPENSEARCH_SITE_ICON) . '</Image>' . "\n";
  }

  if (osc_not_null(MODULE_HEADER_TAGS_OPENSEARCH_SITE_IMAGE)) {
    echo '  <Image height="64" width="64" type="image/png">' . osc_output_string(MODULE_HEADER_TAGS_OPENSEARCH_SITE_IMAGE) . '</Image>' . "\n";
  }
?>
  <InputEncoding>UTF-8</InputEncoding>
  <Url type="text/html" method="get" template="<?php echo tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, 'keywords={searchTerms}', 'NONSSL', false); ?>" />
</OpenSearchDescription>
<?php
  require('includes/application_bottom.php');
?>
