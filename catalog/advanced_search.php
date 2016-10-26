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

  $OSCOM_Language->loadDefinitions('advanced_search');

  $breadcrumb->add(NAVBAR_TITLE_1, OSCOM::link('advanced_search.php'));

  require($oscTemplate->getFile('template_top.php'));
?>

<script src="<?= OSCOM::linkPublic('js/general.js'); ?>"></script>
<script><!--
function check_form() {
  var error_message = "<?php echo JS_ERROR; ?>";
  var error_found = false;
  var error_field;
  var keywords = document.advanced_search.keywords.value;
  var dfrom = document.advanced_search.dfrom.value;
  var dto = document.advanced_search.dto.value;
  var pfrom = document.advanced_search.pfrom.value;
  var pto = document.advanced_search.pto.value;
  var pfrom_float;
  var pto_float;

  if ( ((keywords == '') || (keywords.length < 1)) && ((dfrom == '') || (dfrom.length < 1)) && ((dto == '') || (dto.length < 1)) && ((pfrom == '') || (pfrom.length < 1)) && ((pto == '') || (pto.length < 1)) ) {
    error_message = error_message + "* <?php echo ERROR_AT_LEAST_ONE_INPUT; ?>\n";
    error_field = document.advanced_search.keywords;
    error_found = true;
  }

  if (dfrom.length > 0) {
    if (!IsValidDate(dfrom, '<?php echo DOB_FORMAT_STRING; ?>')) {
      error_message = error_message + "* <?php echo ERROR_INVALID_FROM_DATE; ?>\n";
      error_field = document.advanced_search.dfrom;
      error_found = true;
    }
  }

  if (dto.length > 0) {
    if (!IsValidDate(dto, '<?php echo DOB_FORMAT_STRING; ?>')) {
      error_message = error_message + "* <?php echo ERROR_INVALID_TO_DATE; ?>\n";
      error_field = document.advanced_search.dto;
      error_found = true;
    }
  }

  if ((dfrom.length > 0) && (IsValidDate(dfrom, '<?php echo DOB_FORMAT_STRING; ?>')) && (dto.length > 0) && (IsValidDate(dto, '<?php echo DOB_FORMAT_STRING; ?>'))) {
    if (!CheckDateRange(document.advanced_search.dfrom, document.advanced_search.dto)) {
      error_message = error_message + "* <?php echo ERROR_TO_DATE_LESS_THAN_FROM_DATE; ?>\n";
      error_field = document.advanced_search.dto;
      error_found = true;
    }
  }

  if (pfrom.length > 0) {
    pfrom_float = parseFloat(pfrom);
    if (isNaN(pfrom_float)) {
      error_message = error_message + "* <?php echo ERROR_PRICE_FROM_MUST_BE_NUM; ?>\n";
      error_field = document.advanced_search.pfrom;
      error_found = true;
    }
  } else {
    pfrom_float = 0;
  }

  if (pto.length > 0) {
    pto_float = parseFloat(pto);
    if (isNaN(pto_float)) {
      error_message = error_message + "* <?php echo ERROR_PRICE_TO_MUST_BE_NUM; ?>\n";
      error_field = document.advanced_search.pto;
      error_found = true;
    }
  } else {
    pto_float = 0;
  }

  if ( (pfrom.length > 0) && (pto.length > 0) ) {
    if ( (!isNaN(pfrom_float)) && (!isNaN(pto_float)) && (pto_float < pfrom_float) ) {
      error_message = error_message + "* <?php echo ERROR_PRICE_TO_LESS_THAN_PRICE_FROM; ?>\n";
      error_field = document.advanced_search.pto;
      error_found = true;
    }
  }

  if (error_found == true) {
    alert(error_message);
    error_field.focus();
    return false;
  } else {
    return true;
  }
}
//--></script>

<div class="page-header">
  <h1><?php echo HEADING_TITLE_1; ?></h1>
</div>

<?php
  if ($messageStack->size('search') > 0) {
    echo $messageStack->output('search');
  }
?>

<?php echo HTML::form('advanced_search', OSCOM::link('advanced_search_result.php', '', false), 'get', 'class="form-horizontal" onsubmit="return check_form(this);"', ['session_id' => true]); ?>

<div class="contentContainer">

  <div class="contentText">
    <div class="form-group has-feedback">
      <label for="inputKeywords" class="control-label col-sm-3"><?php echo HEADING_SEARCH_CRITERIA; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('keywords', '', 'required aria-required="true" id="inputKeywords" placeholder="' . TEXT_SEARCH_PLACEHOLDER . '"', 'search');
        echo FORM_REQUIRED_INPUT;
        echo HTML::hiddenField('search_in_description', '1');
        ?>
      </div>
    </div>

    <div class="buttonSet row">
      <div class="col-xs-6"><a data-toggle="modal" href="#helpSearch" class="btn btn-primary"><?php echo TEXT_SEARCH_HELP_LINK; ?></a></div>
      <div class="col-xs-6 text-right"><?php echo HTML::button(IMAGE_BUTTON_SEARCH, 'fa fa-search', null, null, 'btn-success'); ?></div>
    </div>

    <div class="modal fade" id="helpSearch" tabindex="-1" role="dialog" aria-labelledby="helpSearchLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><span class="fa fa-remove"></span></button>
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
        echo HTML::selectField('categories_id', tep_get_categories(array(array('id' => '', 'text' => TEXT_ALL_CATEGORIES))), null, 'id="entryCategories"');
        ?>
      </div>
    </div>
    <div class="form-group">
      <label for="entryIncludeSubs" class="control-label col-sm-3"><?php echo ENTRY_INCLUDE_SUBCATEGORIES; ?></label>
      <div class="col-sm-9">
        <div class="checkbox">
          <label>
            <?php echo HTML::checkboxField('inc_subcat', '1', true, 'id="entryIncludeSubs"'); ?>
	  </label>
        </div>
      </div>
    </div>
    <div class="form-group">
      <label for="entryManufacturers" class="control-label col-sm-3"><?php echo ENTRY_MANUFACTURERS; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::selectField('manufacturers_id', tep_get_manufacturers(array(array('id' => '', 'text' => TEXT_ALL_MANUFACTURERS))), null, 'id="entryManufacturers"');
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
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
