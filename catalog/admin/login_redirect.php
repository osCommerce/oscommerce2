<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  if ( !tep_session_is_registered('redirect_origin') || !isset($redirect_origin['post']) || empty($redirect_origin['post']) ) {
    tep_redirect(tep_href_link(FILENAME_DEFAULT));
  }

  tep_session_unregister('redirect_origin');

  include(DIR_WS_INCLUDES . 'template_top.php');
?>

<style>
#contentText {
  margin-left: 0;
}

#redirect_accordion {
  padding: 20px;
}
</style>

<div id="redirect_accordion">
  <h3><?php echo HEADING_TITLE; ?></h3>

  <div>

<?php
  echo tep_draw_form('redirect', $redirect_origin['page'], http_build_query($redirect_origin['get']));

  foreach ( $redirect_origin['post'] as $key => $value ) {
    echo tep_draw_hidden_field($key, $value);
  }
?>

    <p><?php echo sprintf(TEXT_REDIRECTING_IN, tep_output_string_protected($redirect_origin['page'])); ?></p>

    <p style="padding-top: 10px;"><?php echo tep_draw_button(BUTTON_SKIP_REDIRECT); ?></p>

    <p style="padding-top: 10px;"><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT) . '">' . TEXT_DASHBOARD_LINK . '</a>'; ?></p>

    </form>
  </div>
</div>

<script type="text/javascript">
$(function() {
  $('#adminAppMenu').hide();

  $('#redirect_accordion').accordion();

  var redirect_count = 10;
  var redirect_timer = setInterval(function() {
    if ( --redirect_count < 1 ) {
      clearInterval(redirect_timer);
      $('form[name="redirect"]').submit();
    }

    $('#redirect_counter').text(redirect_count);
  }, 1000);
});
</script>

<?php
  include(DIR_WS_INCLUDES . 'template_bottom.php');
  include(DIR_WS_INCLUDES . 'application_bottom.php');
?>
