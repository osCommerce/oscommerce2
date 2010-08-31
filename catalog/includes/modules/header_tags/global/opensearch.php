<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class ht_global_opensearch {
    function parse() {
      global $oscTemplate;

      $oscTemplate->addHeaderTag('<link rel="search" type="application/opensearchdescription+xml" href="' . tep_href_link('opensearch.php', '', 'NONSSL', false) . '" title="' . tep_output_string(STORE_NAME) . '" />');
    }
  }
?>
