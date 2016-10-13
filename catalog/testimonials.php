<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  require('includes/languages/' . $language . '/testimonials.php');

  $breadcrumb->add(NAVBAR_TITLE, OSCOM::link('testimonials.php'));

  require('includes/template_top.php');
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<div class="contentContainer">

<?php
  $testimonials_query_raw = "select t.testimonials_id, td.testimonials_text, t.date_added, t.customers_name from testimonials t, testimonials_description td where t.testimonials_id = td.testimonials_id and td.languages_id = '" . (int)$languages_id . "' and testimonials_status = 1 order by t.testimonials_id DESC";
  $testimonials_split = new splitPageResults($testimonials_query_raw, MAX_DISPLAY_NEW_REVIEWS);

  if ($testimonials_split->number_of_rows > 0) {
    if ((PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3')) {
?>
<div class="row">
  <div class="col-sm-6 pagenumber hidden-xs">
    <?php echo $testimonials_split->display_count(TEXT_DISPLAY_NUMBER_OF_TESTIMONIALS); ?>
  </div>
  <div class="col-sm-6">
    <span class="pull-right pagenav"><ul class="pagination"><?php echo $testimonials_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info'))); ?></ul></span>
    <span class="pull-right"><?php echo TEXT_RESULT_PAGE; ?></span>
  </div>
</div>
<?php
    }
    ?>
    <div class="contentText">
      <div class="testimonials">
<?php
    $testimonials_query = tep_db_query($testimonials_split->sql_query);
    while ($testimonials = tep_db_fetch_array($testimonials_query)) {
      echo '<blockquote class="col-sm-6">';
      echo '  <p>' . HTML::outputProtected($testimonials['testimonials_text']) . '</p><div class="clearfix"></div>';
      echo '  <p><small>' . HTML::outputProtected($testimonials['customers_name']) . '</small></p>';
      echo '</blockquote>';
    }
    ?>
      </div>
      <div class="clearfix"></div>
    </div>
<?php
  } else {
?>

  <div class="contentText">
    <div class="alert alert-info">
      <?php echo TEXT_NO_TESTIMONIALS; ?>
    </div>
  </div>

<?php
  }

  if (($testimonials_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3'))) {
?>
<div class="row">
  <div class="col-sm-6 pagenumber hidden-xs">
    <?php echo $testimonials_split->display_count(TEXT_DISPLAY_NUMBER_OF_TESTIMONIALS); ?>
  </div>
  <div class="col-sm-6">
    <span class="pull-right pagenav"><ul class="pagination"><?php echo $testimonials_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info'))); ?></ul></span>
    <span class="pull-right"><?php echo TEXT_RESULT_PAGE; ?></span>
  </div>
</div>
<?php
  }
?>

</div>

<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
