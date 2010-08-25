<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  if (!class_exists('lastRSS')) {
    include(DIR_WS_CLASSES . 'rss.php');
  }

  $rss = new lastRSS;
  $rss->items_limit = 5;
  $rss->cache_dir = DIR_FS_CACHE;
  $rss->cache_time = 86400;
  $feed = $rss->get('http://feeds.feedburner.com/osCommerceNewsAndBlogs');
?>

<table border="0" width="100%" cellspacing="0" cellpadding="4">
  <tr class="dataTableHeadingRow">
    <td class="dataTableHeadingContent"><?php echo ADMIN_INDEX_NEWS_TITLE; ?></td>
    <td class="dataTableHeadingContent" align="right"><?php echo ADMIN_INDEX_NEWS_DATE; ?></td>
  </tr>
<?php
  if (is_array($feed) && !empty($feed)) {
    foreach ($feed['items'] as $item) {
      echo '  <tr class="dataTableRow" onmouseover="rowOverEffect(this);" onmouseout="rowOutEffect(this);">' .
           '    <td class="dataTableContent"><a href="' . $item['link'] . '" target="_blank">' . $item['title'] . '</a></td>' .
           '    <td class="dataTableContent" align="right" style="white-space: nowrap;">' . date("F j, Y", strtotime($item['pubDate'])) . '</td>' .
           '  </tr>';
    }
  } else {
    echo '  <tr class="dataTableRow">' .
         '    <td class="dataTableContent" colspan="2">' . ADMIN_INDEX_NEWS_FEED_ERROR . '</td>' .
         '  </tr>';
  }

  echo '  <tr class="dataTableRow">' .
       '    <td class="dataTableContent" align="right" colspan="2"><a href="http://www.oscommerce.com/newsletter/subscribe" target="_blank">' . tep_image(DIR_WS_IMAGES . 'icon_newsletter.png', ADMIN_INDEX_NEWS_NEWSLETTER) . '</a>&nbsp;<a href="http://www.facebook.com/pages/osCommerce/33387373079" target="_blank">' . tep_image(DIR_WS_IMAGES . 'icon_facebook.png', ADMIN_INDEX_NEWS_FACEBOOK) . '</a>&nbsp;<a href="http://twitter.com/osCommerce" target="_blank">' . tep_image(DIR_WS_IMAGES . 'icon_twitter.png', ADMIN_INDEX_NEWS_TWITTER) . '</a>&nbsp;<a href="http://feeds.feedburner.com/osCommerceNewsAndBlogs" target="_blank">' . tep_image(DIR_WS_IMAGES . 'icon_rss.png', ADMIN_INDEX_NEWS_RSS) . '</a></td>' .
       '  </tr>';

?>
</table>