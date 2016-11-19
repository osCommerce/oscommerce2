<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  $OSCOM_Language->loadDefinitions('advanced_search');

  $breadcrumb->add(OSCOM::getDef('navbar_title_1'), OSCOM::link('advanced_search.php'));

  require($oscTemplate->getFile('template_top.php'));
?>

<script src="<?= OSCOM::linkPublic('js/general.js'); ?>"></script>
<script><!--
function check_form() {
  var error_message = <?= json_encode(OSCOM::getDef('js_error') . "\n\n"); ?>;
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
    error_message = error_message + "* <?php echo OSCOM::getDef('error_at_least_one_input'); ?>\n";
    error_field = document.advanced_search.keywords;
    error_found = true;
  }

  if (dfrom.length > 0) {
    if (!IsValidDate(dfrom, '<?php echo OSCOM::getDef('dob_format_string'); ?>')) {
      error_message = error_message + "* <?php echo OSCOM::getDef('error_invalid_from_date'); ?>\n";
      error_field = document.advanced_search.dfrom;
      error_found = true;
    }
  }

  if (dto.length > 0) {
    if (!IsValidDate(dto, '<?php echo OSCOM::getDef('dob_format_string'); ?>')) {
      error_message = error_message + "* <?php echo OSCOM::getDef('error_invalid_to_date'); ?>\n";
      error_field = document.advanced_search.dto;
      error_found = true;
    }
  }

  if ((dfrom.length > 0) && (IsValidDate(dfrom, '<?php echo OSCOM::getDef('dob_format_string'); ?>')) && (dto.length > 0) && (IsValidDate(dto, '<?php echo OSCOM::getDef('dob_format_string'); ?>'))) {
    if (!CheckDateRange(document.advanced_search.dfrom, document.advanced_search.dto)) {
      error_message = error_message + "* <?php echo OSCOM::getDef('error_to_date_less_than_from_date'); ?>\n";
      error_field = document.advanced_search.dto;
      error_found = true;
    }
  }

  if (pfrom.length > 0) {
    pfrom_float = parseFloat(pfrom);
    if (isNaN(pfrom_float)) {
      error_message = error_message + "* <?php echo OSCOM::getDef('error_price_from_must_be_num'); ?>\n";
      error_field = document.advanced_search.pfrom;
      error_found = true;
    }
  } else {
    pfrom_float = 0;
  }

  if (pto.length > 0) {
    pto_float = parseFloat(pto);
    if (isNaN(pto_float)) {
      error_message = error_message + "* <?php echo OSCOM::getDef('error_price_to_must_be_num'); ?>\n";
      error_field = document.advanced_search.pto;
      error_found = true;
    }
  } else {
    pto_float = 0;
  }

  if ( (pfrom.length > 0) && (pto.length > 0) ) {
    if ( (!isNaN(pfrom_float)) && (!isNaN(pto_float)) && (pto_float < pfrom_float) ) {
      error_message = error_message + "* <?php echo OSCOM::getDef('error_price_to_less_than_price_from'); ?>\n";
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
  <h1><?php echo OSCOM::getDef('heading_title_1'); ?></h1>
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
      <label for="inputKeywords" class="control-label col-sm-3"><?php echo OSCOM::getDef('heading_search_criteria'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('keywords', '', 'required aria-required="true" id="inputKeywords" placeholder="' . OSCOM::getDef('text_search_placeholder') . '"', 'search');
        echo OSCOM::getDef('form_required_input');
        echo HTML::hiddenField('search_in_description', '1');
        ?>
      </div>
    </div>

    <div class="buttonSet row">
      <div class="col-xs-6"><a data-toggle="modal" href="#helpSearch" class="btn btn-primary"><?php echo OSCOM::getDef('text_search_help_link'); ?></a></div>
      <div class="col-xs-6 text-right"><?php echo HTML::button(OSCOM::getDef('image_button_search'), 'fa fa-search', null, null, 'btn-success'); ?></div>
    </div>

    <div class="modal fade" id="helpSearch" tabindex="-1" role="dialog" aria-labelledby="helpSearchLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><span class="fa fa-remove"></span></button>
            <h4 class="modal-title"><?php echo OSCOM::getDef('heading_search_help'); ?></h4>
          </div>
          <div class="modal-body">
            <p><?php echo OSCOM::getDef('text_search_help'); ?></p>
          </div>
        </div>
      </div>
    </div>

    <hr>

    <div class="form-group">
      <label for="entryCategories" class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_categories'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::selectField('categories_id', tep_get_categories(array(array('id' => '', 'text' => OSCOM::getDef('text_all_categories')))), null, 'id="entryCategories"');
        ?>
      </div>
    </div>
    <div class="form-group">
      <label for="entryIncludeSubs" class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_include_subcategories'); ?></label>
      <div class="col-sm-9">
        <div class="checkbox">
          <label>
            <?php echo HTML::checkboxField('inc_subcat', '1', true, 'id="entryIncludeSubs"'); ?>
	  </label>
        </div>
      </div>
    </div>
    <div class="form-group">
      <label for="entryManufacturers" class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_manufacturers'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::selectField('manufacturers_id', tep_get_manufacturers(array(array('id' => '', 'text' => OSCOM::getDef('text_all_manufacturers')))), null, 'id="entryManufacturers"');
        ?>
      </div>
    </div>
    <div class="form-group">
      <label for="PriceFrom" class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_price_from'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('pfrom', '', 'id="PriceFrom" placeholder="' . OSCOM::getDef('entry_price_from_text') . '"');
        ?>
      </div>
    </div>
    <div class="form-group">
      <label for="PriceTo" class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_price_to'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('pto', '', 'id="PriceTo" placeholder="' . OSCOM::getDef('entry_price_to_text') . '"');
        ?>
      </div>
    </div>
    <div class="form-group">
      <label for="dfrom" class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_date_from'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('dfrom', '', 'id="dfrom" placeholder="' . OSCOM::getDef('entry_date_from_text') . '"');
        ?>
      </div>
    </div>
    <div class="form-group">
      <label for="dto" class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_date_to'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('dto', '', 'id="dto" placeholder="' . OSCOM::getDef('entry_date_to_text') . '"');
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
