<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/
?>

<div class="ui-widget infoBoxContainer">
  <div class="ui-widget-header infoBoxHeading"><?php echo BOX_HEADING_SEARCH; ?></div>

  <div class="ui-widget-content infoBoxContents" style="text-align: center;">

<?php
    echo tep_draw_form('quick_find', tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', 'NONSSL', false), 'get') .
         tep_draw_input_field('keywords', '', 'size="10" maxlength="30" style="width: ' . (BOX_WIDTH-30) . 'px"') . '&nbsp;' . tep_draw_hidden_field('search_in_description', '1') . tep_hide_session_id() . tep_image_submit('button_quick_find.gif', BOX_HEADING_SEARCH) . '<br />' . BOX_SEARCH_TEXT . '<br /><a href="' . tep_href_link(FILENAME_ADVANCED_SEARCH) . '"><b>' . BOX_SEARCH_ADVANCED_SEARCH . '</b></a>' .
         '</form>';
?>

  </div>
</div>
