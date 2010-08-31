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

  echo '<?xml version="1.0"?>' . "\n";
?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/" xmlns:moz="http://www.mozilla.org/2006/browser/search/">
  <ShortName><?php echo tep_output_string(STORE_NAME); ?></ShortName>
  <InputEncoding>UTF-8</InputEncoding>
  <Url type="text/html" method="get" template="<?php echo tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, 'keywords={searchTerms}', 'NONSSL', false); ?>" />
</OpenSearchDescription>
<?php
  require('includes/application_bottom.php');
?>
