<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class bm_card_acceptance {
    var $code = 'bm_card_acceptance';
    var $group = 'boxes';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function bm_card_acceptance() {
      $this->title = MODULE_BOXES_CARD_ACCEPTANCE_TITLE;
      $this->description = MODULE_BOXES_CARD_ACCEPTANCE_DESCRIPTION;

      if ( defined('MODULE_BOXES_CARD_ACCEPTANCE_STATUS') ) {
        $this->sort_order = MODULE_BOXES_CARD_ACCEPTANCE_SORT_ORDER;
        $this->enabled = (MODULE_BOXES_CARD_ACCEPTANCE_STATUS == 'True');

        $this->group = ((MODULE_BOXES_CARD_ACCEPTANCE_CONTENT_PLACEMENT == 'Left Column') ? 'boxes_column_left' : 'boxes_column_right');
      }
    }

    function execute() {
      global $PHP_SELF, $oscTemplate;

      if ( (substr(basename($PHP_SELF), 0, 8) != 'checkout') && tep_not_null(MODULE_BOXES_CARD_ACCEPTANCE_LOGOS) ) {
        $output = '<div class="ui-widget infoBoxContainer">' .
                  '  <div class="ui-widget-header infoBoxHeading">' . MODULE_BOXES_CARD_ACCEPTANCE_BOX_TITLE . '</div>' .
                  '  <div class="ui-widget-content infoBoxContents" style="text-align: center;">';

        foreach ( explode(';', MODULE_BOXES_CARD_ACCEPTANCE_LOGOS) as $logo ) {
          $output .= tep_image(DIR_WS_IMAGES . 'card_acceptance/' . basename($logo));
        }

        $output .= '  </div>' .
                   '</div>';

        $oscTemplate->addBlock($output, $this->group);
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_BOXES_CARD_ACCEPTANCE_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Card Acceptance Module', 'MODULE_BOXES_CARD_ACCEPTANCE_STATUS', 'True', 'Do you want to add the module to your shop?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Logos', 'MODULE_BOXES_CARD_ACCEPTANCE_LOGOS', 'paypal_horizontal_large.png;visa.png;mastercard_transparent.png;american_express.png;maestro_transparent.png', 'The card acceptance logos to show.', '6', '0', 'bm_card_acceptance_show_logos', 'bm_card_acceptance_edit_logos(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Placement', 'MODULE_BOXES_CARD_ACCEPTANCE_CONTENT_PLACEMENT', 'Left Column', 'Should the module be loaded in the left or right column?', '6', '1', 'tep_cfg_select_option(array(\'Left Column\', \'Right Column\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_BOXES_CARD_ACCEPTANCE_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_BOXES_CARD_ACCEPTANCE_STATUS', 'MODULE_BOXES_CARD_ACCEPTANCE_LOGOS', 'MODULE_BOXES_CARD_ACCEPTANCE_CONTENT_PLACEMENT', 'MODULE_BOXES_CARD_ACCEPTANCE_SORT_ORDER');
    }
  }

  function bm_card_acceptance_show_logos($text) {
    $output = '';

    if ( !empty($text) ) {
      $output = '<ul style="list-style-type: none; margin: 0; padding: 5px; margin-bottom: 10px;">';

      foreach (explode(';', $text) as $card) {
        $output .= '<li style="padding: 2px;">' . tep_image(DIR_WS_CATALOG_IMAGES . 'card_acceptance/' . basename($card), basename($card)) . '</li>';
      }

      $output .= '</ul>';
    }

    return $output;
  }

  function bm_card_acceptance_edit_logos($values, $key) {
    $files_array = array();

    if ( $dir = @dir(DIR_FS_CATALOG . DIR_WS_IMAGES . 'card_acceptance') ) {
      while ( $file = $dir->read() ) {
        if ( !is_dir(DIR_FS_CATALOG . DIR_WS_IMAGES . 'card_acceptance/' . $file) ) {
          if ( in_array(substr($file, strrpos($file, '.')+1), array('gif', 'jpg', 'png')) ) {
            $files_array[] = $file;
          }
        }
      }

      sort($files_array);

      $dir->close();
    }

    $values_array = !empty($values) ? explode(';', $values) : array();

    $output = '<h3>' . MODULE_BOXES_CARD_ACCEPTANCE_SHOWN_CARDS . '</h3>' .
              '<ul id="ca_logos" style="list-style-type: none; margin: 0; padding: 5px; margin-bottom: 10px;">';

    foreach ($values_array as $file) {
      $output .= '<li style="padding: 2px;">' . tep_image(DIR_WS_CATALOG_IMAGES . 'card_acceptance/' . $file, $file) . tep_draw_hidden_field('bm_card_acceptance_logos[]', $file) . '</li>';
    }

    $output .= '</ul>';

    $output .= '<h3>' . MODULE_BOXES_CARD_ACCEPTANCE_NEW_CARDS . '</h3><ul id="new_ca_logos" style="list-style-type: none; margin: 0; padding: 5px; margin-bottom: 10px;">';

    foreach ($files_array as $file) {
      if ( !in_array($file, $values_array) ) {
        $output .= '<li style="padding: 2px;">' . tep_image(DIR_WS_CATALOG_IMAGES . 'card_acceptance/' . $file, $file) . tep_draw_hidden_field('bm_card_acceptance_logos[]', $file) . '</li>';
      }
    }

    $output .= '</ul>';

    $output .= tep_draw_hidden_field('configuration[' . $key . ']', '', 'id="ca_logo_cards"');

    $drag_here_li = '<li id="caLogoEmpty" style="background-color: #fcf8e3; border: 1px #faedd0 solid; color: #a67d57; padding: 5px;">' . addslashes(MODULE_BOXES_CARD_ACCEPTANCE_DRAG_HERE) . '</li>';

    $output .= <<<EOD
<script>
$(function() {
  var drag_here_li = '{$drag_here_li}';

  if ( $('#ca_logos li').size() < 1 ) {
    $('#ca_logos').append(drag_here_li);
  }

  $('#ca_logos').sortable({
    connectWith: '#new_ca_logos',
    items: 'li:not("#caLogoEmpty")',
    stop: function (event, ui) {
      if ( $('#ca_logos li').size() < 1 ) {
        $('#ca_logos').append(drag_here_li);
      } else if ( $('#caLogoEmpty').length > 0 ) {
        $('#caLogoEmpty').remove();
      }
    }
  });

  $('#new_ca_logos').sortable({
    connectWith: '#ca_logos',
    stop: function (event, ui) {
      if ( $('#ca_logos li').size() < 1 ) {
        $('#ca_logos').append(drag_here_li);
      } else if ( $('#caLogoEmpty').length > 0 ) {
        $('#caLogoEmpty').remove();
      }
    }
  });

  $('#ca_logos, #new_ca_logos').disableSelection();

  $('form[name="modules"]').submit(function(event) {
    var ca_selected_cards = '';

    if ( $('#ca_logos li').size() > 0 ) {
      $('#ca_logos li input[name="bm_card_acceptance_logos[]"]').each(function() {
        ca_selected_cards += $(this).attr('value') + ';';
      });
    }

    if (ca_selected_cards.length > 0) {
      ca_selected_cards = ca_selected_cards.substring(0, ca_selected_cards.length - 1);
    }

    $('#ca_logo_cards').val(ca_selected_cards);
  });
});
</script>
EOD;

    return $output;
  }
?>
