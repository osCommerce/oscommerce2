<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class bm_card_acceptance {
    var $code = 'bm_card_acceptance';
    var $group = 'boxes';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->title = OSCOM::getDef('module_boxes_card_acceptance_title');
      $this->description = OSCOM::getDef('module_boxes_card_acceptance_description');

      if ( defined('MODULE_BOXES_CARD_ACCEPTANCE_STATUS') ) {
        $this->sort_order = MODULE_BOXES_CARD_ACCEPTANCE_SORT_ORDER;
        $this->enabled = (MODULE_BOXES_CARD_ACCEPTANCE_STATUS == 'True');

        $this->group = ((MODULE_BOXES_CARD_ACCEPTANCE_CONTENT_PLACEMENT == 'Left Column') ? 'boxes_column_left' : 'boxes_column_right');
      }
    }

    function execute() {
      global $PHP_SELF, $oscTemplate;

      if ( (substr(basename($PHP_SELF), 0, 8) != 'checkout') && tep_not_null(MODULE_BOXES_CARD_ACCEPTANCE_LOGOS) ) {
        $output = NULL;

        foreach ( explode(';', MODULE_BOXES_CARD_ACCEPTANCE_LOGOS) as $logo ) {
          $output .= HTML::image(OSCOM::linkImage('Shop/card_acceptance/' . basename($logo)), null, null, null, null, false);
        }

        ob_start();
        include('includes/modules/boxes/templates/card_acceptance.php');
        $data = ob_get_clean();

        $oscTemplate->addBlock($data, $this->group);
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_BOXES_CARD_ACCEPTANCE_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Card Acceptance Module',
        'configuration_key' => 'MODULE_BOXES_CARD_ACCEPTANCE_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to add the module to your shop?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' =>  'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Logos',
        'configuration_key' => 'MODULE_BOXES_CARD_ACCEPTANCE_LOGOS',
        'configuration_value' => 'paypal_horizontal_large.png;visa.png;mastercard_transparent.png;american_express.png;maestro_transparent.png',
        'configuration_description' => 'The card acceptance logos to show.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'use_function' => 'bm_card_acceptance_show_logos',
        'set_function' => 'bm_card_acceptance_edit_logos(',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Content Placement',
        'configuration_key' => 'MODULE_BOXES_CARD_ACCEPTANCE_CONTENT_PLACEMENT',
        'configuration_value' => 'Left Column',
        'configuration_description' => 'Should the module be loaded in the left or right column?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'Left Column\', \'Right Column\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_BOXES_CARD_ACCEPTANCE_SORT_ORDER',
        'configuration_value' => '0',
        'configuration_description' => 'Sort order of display. Lowest is displayed first.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);
    }

    function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
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
        $output .= '<li style="padding: 2px;">' . HTML::image(OSCOM::linkImage('Shop/card_acceptance/' . basename($card)), basename($card)) . '</li>';
      }

      $output .= '</ul>';
    }

    return $output;
  }

  function bm_card_acceptance_edit_logos($values, $key) {
    $files_array = array();

    if ( $dir = @dir(OSCOM::getConfig('dir_root', 'Shop') . 'images/card_acceptance') ) {
      while ( $file = $dir->read() ) {
        if ( !is_dir(OSCOM::getConfig('dir_root', 'Shop') . 'images/card_acceptance/' . $file) ) {
          if ( in_array(substr($file, strrpos($file, '.')+1), array('gif', 'jpg', 'png')) ) {
            $files_array[] = $file;
          }
        }
      }

      sort($files_array);

      $dir->close();
    }

    $values_array = !empty($values) ? explode(';', $values) : array();

    $output = '<h3>' . OSCOM::getDef('module_boxes_card_acceptance_shown_cards') . '</h3>' .
              '<ul id="ca_logos" style="list-style-type: none; margin: 0; padding: 5px; margin-bottom: 10px;">';

    foreach ($values_array as $file) {
      $output .= '<li style="padding: 2px;">' . HTML::image(OSCOM::linkImage('Shop/card_acceptance/' . $file), $file) . HTML::hiddenField('bm_card_acceptance_logos[]', $file) . '</li>';
    }

    $output .= '</ul>';

    $output .= '<h3>' . OSCOM::getDef('module_boxes_card_acceptance_new_cards') . '</h3><ul id="new_ca_logos" style="list-style-type: none; margin: 0; padding: 5px; margin-bottom: 10px;">';

    foreach ($files_array as $file) {
      if ( !in_array($file, $values_array) ) {
        $output .= '<li style="padding: 2px;">' . HTML::image(OSCOM::linkImage('Shop/card_acceptance/' . $file), $file) . HTML::hiddenField('bm_card_acceptance_logos[]', $file) . '</li>';
      }
    }

    $output .= '</ul>';

    $output .= HTML::hiddenField('configuration[' . $key . ']', '', 'id="ca_logo_cards"');

    $drag_here_li = '<li id="caLogoEmpty" style="background-color: #fcf8e3; border: 1px #faedd0 solid; color: #a67d57; padding: 5px;">' . addslashes(OSCOM::getDef('module_boxes_card_acceptance_drag_here')) . '</li>';

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
