<?php
/*
  $Id: $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2007 osCommerce

  Released under the GNU General Public License
*/

  if ($messageStack->size > 0) {
    echo $messageStack->output();
  }
?>
<table border="0" width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT, '', 'NONSSL') . '">' . tep_image(DIR_WS_IMAGES . 'oscommerce.png', 'osCommerce Online Merchant v2.2 RC1') . '</a>'; ?></td>
    <td align="right"><?php echo '<a href="http://www.oscommerce.com" target="_blank">' . tep_image(DIR_WS_IMAGES . 'header_support.gif', HEADER_TITLE_SUPPORT_SITE, '50', '50') . '</a>&nbsp;&nbsp;<a href="' . tep_catalog_href_link() . '">' . tep_image(DIR_WS_IMAGES . 'header_checkout.gif', HEADER_TITLE_ONLINE_CATALOG, '53', '50') . '</a>&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_DEFAULT, '', 'NONSSL') . '">' . tep_image(DIR_WS_IMAGES . 'header_administration.gif', HEADER_TITLE_ADMINISTRATION, '50', '50') . '</a>'; ?>&nbsp;&nbsp;</td>
  </tr>
  <tr class="headerBar">
    <td class="headerBarContent">&nbsp;&nbsp;<?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT, '', 'NONSSL') . '" class="headerLink">' . HEADER_TITLE_TOP . '</a>'; ?></td>
    <td class="headerBarContent" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_LOGIN, 'action=logoff') . '" class="headerLink">Logoff</a> &nbsp;|&nbsp; <a href="http://www.oscommerce.com" class="headerLink">' . HEADER_TITLE_SUPPORT_SITE . '</a> &nbsp;|&nbsp; <a href="' . tep_catalog_href_link() . '" class="headerLink">' . HEADER_TITLE_ONLINE_CATALOG . '</a> &nbsp;|&nbsp; <a href="' . tep_href_link(FILENAME_DEFAULT, '', 'NONSSL') . '" class="headerLink">' . HEADER_TITLE_ADMINISTRATION . '</a>'; ?>&nbsp;&nbsp;</td>
  </tr>
</table>