<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  function tep_dt_get_tables() {
    $result = array();

    $tables_query = tep_db_query('show table status');
    while ( $tables = tep_db_fetch_array($tables_query) ) {
      $result[] = $tables['Name'];
    }

    return $result;
  }

  $action = null;
  $actions = array(array('id' => 'check',
                         'text' => ACTION_CHECK_TABLES),
                   array('id' => 'analyze',
                         'text' => ACTION_ANALYZE_TABLES),
                   array('id' => 'optimize',
                         'text' => ACTION_OPTIMIZE_TABLES),
                   array('id' => 'repair',
                         'text' => ACTION_REPAIR_TABLES),
                   array('id' => 'utf8',
                         'text' => ACTION_UTF8_CONVERSION));

  if ( isset($HTTP_POST_VARS['action']) ) {
    if ( in_array($HTTP_POST_VARS['action'], array('check', 'analyze', 'optimize', 'repair', 'utf8')) ) {
      if ( isset($HTTP_POST_VARS['id']) && is_array($HTTP_POST_VARS['id']) && !empty($HTTP_POST_VARS['id']) ) {
        $tables = tep_dt_get_tables();

        foreach ( $HTTP_POST_VARS['id'] as $key => $value ) {
          if ( !in_array($value, $tables) ) {
            unset($HTTP_POST_VARS['id'][$key]);
          }
        }

        if ( !empty($HTTP_POST_VARS['id']) ) {
          $action = $HTTP_POST_VARS['action'];
        }
      }
    }
  }

  switch ( $action ) {
    case 'check':
    case 'analyze':
    case 'optimize':
    case 'repair':
      tep_set_time_limit(0);

      $table_headers = array(TABLE_HEADING_TABLE,
                             TABLE_HEADING_MSG_TYPE,
                             TABLE_HEADING_MSG,
                             tep_draw_checkbox_field('masterblaster'));

      $table_data = array();

      foreach ( $HTTP_POST_VARS['id'] as $table ) {
        $current_table = null;

        $sql_query = tep_db_query($action . " table " . $table);
        while ( $sql = tep_db_fetch_array($sql_query) ) {
          $table_data[] = array(($table != $current_table) ? tep_output_string_protected($table) : '',
                                tep_output_string_protected($sql['Msg_type']),
                                tep_output_string_protected($sql['Msg_text']),
                                ($table != $current_table) ? tep_draw_checkbox_field('id[]', $table, isset($HTTP_POST_VARS['id']) && in_array($table, $HTTP_POST_VARS['id'])) : '');

          $current_table = $table;
        }
      }

      break;

    case 'utf8':
      tep_set_time_limit(0);

      $table_headers = array(TABLE_HEADING_TABLE,
                             TABLE_HEADING_MSG,
                             tep_draw_checkbox_field('masterblaster'));

      $table_data = array();

      foreach ( $HTTP_POST_VARS['id'] as $table ) {
        $result = 'OK';

// mysqli_query() is directly called as tep_db_query() dies when an error occurs
        if ( !mysqli_query($db_link, "alter table " . $table . " convert to character set utf8 collate utf8_unicode_ci") ) {
          $result = mysqli_error($db_link);
        }

        $table_data[] = array(tep_output_string_protected($table),
                              tep_output_string_protected($result),
                              tep_draw_checkbox_field('id[]', $table));
      }

      break;

    default:
      $table_headers = array(TABLE_HEADING_TABLE,
                             TABLE_HEADING_ROWS,
                             TABLE_HEADING_SIZE,
                             TABLE_HEADING_ENGINE,
                             TABLE_HEADING_COLLATION,
                             tep_draw_checkbox_field('masterblaster'));

      $table_data = array();

      $sql_query = tep_db_query('show table status');
      while ( $sql = tep_db_fetch_array($sql_query) ) {
        $table_data[] = array(tep_output_string_protected($sql['Name']),
                              tep_output_string_protected($sql['Rows']),
                              round(($sql['Data_length'] + $sql['Index_length']) / 1024 / 1024, 2) . 'M',
                              tep_output_string_protected($sql['Engine']),
                              tep_output_string_protected($sql['Collation']),
                              tep_draw_checkbox_field('id[]', $sql['Name']));
      }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<?php
  if ( isset($action) ) {
    echo '<div style="float: right;">' . tep_draw_button(IMAGE_BACK, 'triangle-1-w', tep_href_link('database_tables.php')) . '</div>';
  }
?>

<h1 class="pageHeading"><?php echo HEADING_TITLE; ?></h1>

<?php
  echo tep_draw_form('sql', 'database_tables.php');
?>

<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr class="dataTableHeadingRow">

<?php
  foreach ( $table_headers as $th ) {
    echo '    <td class="dataTableHeadingContent">' . $th . '</td>' . "\n";
  }
?>
  </tr>

<?php
  foreach ( $table_data as $td ) {
    echo '  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";

    foreach ( $td as $data ) {
      echo '    <td class="dataTableContent">' . $data . '</td>' . "\n";
    }

    echo '  </tr>' . "\n";
  }
?>

</table>

<div style="text-align: right;">
  <?php echo tep_draw_pull_down_menu('action', $actions) . tep_draw_button(BUTTON_ACTION_GO); ?>
</div>

</form>

<script type="text/javascript">
$(function() {
  if ( $('form[name="sql"] input[type="checkbox"][name="masterblaster"]').length > 0 ) {
    $('form[name="sql"] input[type="checkbox"][name="masterblaster"]').click(function() {
      $('form[name="sql"] input[type="checkbox"][name="id[]"]').prop('checked', $('form[name="sql"] input[type="checkbox"][name="masterblaster"]').prop('checked'));
    });
  }
});
</script>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
