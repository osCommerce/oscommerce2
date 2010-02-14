<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

include_once(DIR_WS_CLASSES . 'rss.php');

$rss = new lastRSS;
$rss->items_limit = 6;
$rss->cache_dir = DIR_FS_CACHE;
$rss->cache_time = 86400;
$feed = $rss->get('http://www.oscommerce.com/oscommerce_contributions.rdf');
?>

<table border="0" width="100%" cellspacing="0" cellpadding="4">
  <tr class="dataTableHeadingRow">
    <td class="dataTableHeadingContent"><?php echo ADMIN_INDEX_ADDONS_TITLE; ?></td>
    <td class="dataTableHeadingContent" align="right"><?php echo ADMIN_INDEX_ADDONS_DATE; ?></td>
  </tr>
<?php
if ($feed) {
  foreach ($feed['items'] as $item) {
    echo '  <tr class="dataTableRow" onmouseover="rowOverEffect(this);" onmouseout="rowOutEffect(this);">' .
         '    <td class="dataTableContent" align="left"><a href="' . $item['link'] . '" target="_blank">' . $item['title'] . '</a></td>' .
         '    <td class="dataTableContent" align="right">' . date("F j, Y", strtotime($item['pubDate'])) . '</td>' .
         '  </tr>';
  }
} else {
  echo '  <tr class="dataTableRow">' .
       '    <td class="dataTableContent" align="left" colspan="2">' . ADMIN_INDEX_ADDONS_FEED_ERROR . '</td>' .
       '  </tr>';
}
?>
</table>