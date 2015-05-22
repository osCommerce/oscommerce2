<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/advanced_search.php');

  $breadcrumb->add(NAVBAR_TITLE_1, OSCOM::link('advanced_search.php'));

  require('includes/template_top.php');
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE_1; ?></h1>
</div>

<?php
  if ($messageStack->size('search') > 0) {
    echo $messageStack->output('search');
  }
?>

<?php echo tep_draw_form('advanced_search', OSCOM::link('advanced_search_result.php', '', 'NONSSL', false), 'get', 'class="form-horizontal" role="form"') . tep_hide_session_id(); ?>

<div class="contentContainer">

  <div class="contentText">

    <p class="inputRequirement text-right"><?php echo FORM_REQUIRED_INFORMATION; ?></p>

    <div class="form-group has-feedback">
      <label for="inputKeywords" class="control-label col-sm-3"><?php echo HEADING_SEARCH_CRITERIA; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('keywords', '', 'required aria-required="true" id="inputKeywords" placeholder="' . TEXT_SEARCH_PLACEHOLDER . '"');
        echo FORM_REQUIRED_INPUT;
        echo HTML::hiddenField('search_in_description', '1');
        ?>
      </div>
    </div>

    <br />

    <div class="row">
      <div class="col-xs-6 text-right pull-right"><?php echo tep_draw_button(IMAGE_BUTTON_SEARCH, 'glyphicon glyphicon-search', null, 'primary', null, 'btn-success'); ?></div>
      <div class="col-xs-6"><a data-toggle="modal" data-target="#helpSearch" class="btn btn-link"><?php echo TEXT_SEARCH_HELP_LINK; ?></a></div>
    </div>

    <div class="modal fade" id="helpSearch" tabindex="-1" role="dialog" aria-labelledby="helpSearchLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><span class="glyphicon glyphicon-remove"></span></button>
            <h4 class="modal-title"><?php echo HEADING_SEARCH_HELP; ?></h4>
          </div>
          <div class="modal-body">
            <p><?php echo TEXT_SEARCH_HELP; ?></p>
          </div>
        </div>
      </div>
    </div>



    <hr>

    <div class="form-group">
      <label for="entryCategories" class="control-label col-sm-3"><?php echo ENTRY_CATEGORIES; ?></label>
      <div class="col-sm-9">
        <?php
        echo tep_draw_pull_down_menu('categories_id', tep_get_categories(array(array('id' => '', 'text' => TEXT_ALL_CATEGORIES))), NULL, 'id="entryCategories"');
        ?>
      </div>
    </div>
    <div class="form-group">
      <label for="entryIncludeSubs" class="control-label col-sm-3"><?php echo ENTRY_INCLUDE_SUBCATEGORIES; ?></label>
      <div class="col-sm-9">
        <div class="checkbox">
          <label>
            <?php
            echo HTML::checkboxField('inc_subcat', '1', true, 'id="entryIncludeSubs"') . '&nbsp;';
            ?>
          </label>
        </div>
      </div>
    </div>
    <div class="form-group">
      <label for="entryManufacturers" class="control-label col-sm-3"><?php echo ENTRY_MANUFACTURERS; ?></label>
      <div class="col-sm-9">
        <?php
        echo tep_draw_pull_down_menu('manufacturers_id', tep_get_manufacturers(array(array('id' => '', 'text' => TEXT_ALL_MANUFACTURERS))), NULL, 'id="entryManufacturers"');
        ?>
      </div>
    </div>
    <div class="form-group">
      <label for="PriceFrom" class="control-label col-sm-3"><?php echo ENTRY_PRICE_FROM; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('pfrom', '', 'id="PriceFrom" placeholder="' . ENTRY_PRICE_FROM_TEXT . '"');
        ?>
      </div>
    </div>
    <div class="form-group">
      <label for="PriceTo" class="control-label col-sm-3"><?php echo ENTRY_PRICE_TO; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('pto', '', 'id="PriceTo" placeholder="' . ENTRY_PRICE_TO_TEXT . '"');
        ?>
      </div>
    </div>
    <div class="form-group">
      <label for="dfrom" class="control-label col-sm-3"><?php echo ENTRY_DATE_FROM; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('dfrom', '', 'id="dfrom" placeholder="' . ENTRY_DATE_FROM_TEXT . '"');
        ?>
      </div>
    </div>
    <div class="form-group">
      <label for="dto" class="control-label col-sm-3"><?php echo ENTRY_DATE_TO; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('dto', '', 'id="dto" placeholder="' . ENTRY_DATE_TO_TEXT . '"');
        ?>
      </div>
    </div>


  </div>
</div>

</form>

<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
