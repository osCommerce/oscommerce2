<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class ht_product_colorbox {
    var $code = 'ht_product_colorbox';
    var $group = 'footer_scripts';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function ht_product_colorbox() {
      $this->title = MODULE_HEADER_TAGS_PRODUCT_COLORBOX_TITLE;
      $this->description = MODULE_HEADER_TAGS_PRODUCT_COLORBOX_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_PRODUCT_COLORBOX_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_PRODUCT_COLORBOX_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_PRODUCT_COLORBOX_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF, $oscTemplate;

      if (tep_not_null(MODULE_HEADER_TAGS_PRODUCT_COLORBOX_PAGES)) {
        $pages_array = array();

        foreach (explode(';', MODULE_HEADER_TAGS_PRODUCT_COLORBOX_PAGES) as $page) {
          $page = trim($page);

          if (!empty($page)) {
            $pages_array[] = $page;
          }
        }

        if (in_array(basename($PHP_SELF), $pages_array)) {
          $oscTemplate->addBlock('<script src="ext/photoset-grid/jquery.photoset-grid.min.js"></script>' . "\n", $this->group);
          $oscTemplate->addBlock('<link rel="stylesheet" href="ext/colorbox/colorbox.css" />' . "\n", 'header_tags');
          $oscTemplate->addBlock('<script src="ext/colorbox/jquery.colorbox-min.js"></script>' . "\n", $this->group);
          $oscTemplate->addBlock('<script>var ImgCount = $(".piGal").data("imgcount"); $(function() {$(\'.piGal\').css({\'visibility\': \'hidden\'});$(\'.piGal\').photosetGrid({layout: ""+ ImgCount +"",width: \'100%\',highresLinks: true,rel: \'pigallery\',onComplete: function() {$(\'.piGal\').css({\'visibility\': \'visible\'});$(\'.piGal a\').colorbox({maxHeight: \'90%\',maxWidth: \'90%\', rel: \'pigallery\'});$(\'.piGal img\').each(function() {var imgid = $(this).attr(\'id\').substring(9);if ( $(\'#piGalDiv_\' + imgid).length ) {$(this).parent().colorbox({ inline: true, href: "#piGalDiv_" + imgid });}});}});});</script>', $this->group);
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_PRODUCT_COLORBOX_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Colorbox Script', 'MODULE_HEADER_TAGS_PRODUCT_COLORBOX_STATUS', 'True', 'Do you want to enable the Colorbox Scripts?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Pages', 'MODULE_HEADER_TAGS_PRODUCT_COLORBOX_PAGES', '" . implode(';', $this->get_default_pages()) . "', 'The pages to add the Colorbox Scripts to.', '6', '0', 'ht_product_colorbox_show_pages', 'ht_product_colorbox_edit_pages(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_HEADER_TAGS_PRODUCT_COLORBOX_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_HEADER_TAGS_PRODUCT_COLORBOX_STATUS', 'MODULE_HEADER_TAGS_PRODUCT_COLORBOX_PAGES', 'MODULE_HEADER_TAGS_PRODUCT_COLORBOX_SORT_ORDER');
    }

    function get_default_pages() {
      return array('product_info.php');
    }
  }

  function ht_product_colorbox_show_pages($text) {
    return nl2br(implode("\n", explode(';', $text)));
  }

  function ht_product_colorbox_edit_pages($values, $key) {
    global $PHP_SELF;

    $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
    $files_array = array();
	  if ($dir = @dir(DIR_FS_CATALOG)) {
	    while ($file = $dir->read()) {
	      if (!is_dir(DIR_FS_CATALOG . $file)) {
	        if (substr($file, strrpos($file, '.')) == $file_extension) {
            $files_array[] = $file;
          }
        }
      }
      sort($files_array);
      $dir->close();
    }

    $values_array = explode(';', $values);

    $output = '';
    foreach ($files_array as $file) {
      $output .= tep_draw_checkbox_field('ht_product_colorbox_file[]', $file, in_array($file, $values_array)) . '&nbsp;' . tep_output_string($file) . '<br />';
    }

    if (!empty($output)) {
      $output = '<br />' . substr($output, 0, -6);
    }

    $output .= tep_draw_hidden_field('configuration[' . $key . ']', '', 'id="htrn_files"');

    $output .= '<script>
                function htrn_update_cfg_value() {
                  var htrn_selected_files = \'\';

                  if ($(\'input[name="ht_product_colorbox_file[]"]\').length > 0) {
                    $(\'input[name="ht_product_colorbox_file[]"]:checked\').each(function() {
                      htrn_selected_files += $(this).attr(\'value\') + \';\';
                    });

                    if (htrn_selected_files.length > 0) {
                      htrn_selected_files = htrn_selected_files.substring(0, htrn_selected_files.length - 1);
                    }
                  }

                  $(\'#htrn_files\').val(htrn_selected_files);
                }

                $(function() {
                  htrn_update_cfg_value();

                  if ($(\'input[name="ht_product_colorbox_file[]"]\').length > 0) {
                    $(\'input[name="ht_product_colorbox_file[]"]\').change(function() {
                      htrn_update_cfg_value();
                    });
                  }
                });
                </script>';

    return $output;
  }
?>
