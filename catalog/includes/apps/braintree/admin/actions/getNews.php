<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  $btGetNewsResult = array('rpcStatus' => -1);

  if ( function_exists('json_encode') ) {
    $btGetNewsResponse = @json_decode($OSCOM_Braintree->makeApiCall('https://www.oscommerce.com/index.php?RPC&Website&Index&GetPartnerBanner&forumid=110&onlyjson=true'), true);

    if ( is_array($btGetNewsResponse) && isset($btGetNewsResponse['title']) ) {
      $btGetNewsResult = $btGetNewsResponse;

      $btGetNewsResult['rpcStatus'] = 1;
    }

    echo json_encode($btGetNewsResult);
  }

  exit;
?>
