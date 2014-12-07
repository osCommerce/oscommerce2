<div class="panel panel-default">
  <div class="panel-heading"><?php echo MODULE_BOXES_SEARCH_BOX_TITLE; ?></div>
  <div class="panel-body text-center">
    <?php
    // initialize form
    echo $_form;
    ?>
    <div class="input-group">
      <?php
      // input box
      echo $_input;
      ?>
      <span class="input-group-btn"><button type="submit" class="btn btn-search"><i class="glyphicon glyphicon-search"></i></button></span>
    </div>
    </form>
  </div>
  <div class="panel-footer"><?php echo MODULE_BOXES_SEARCH_BOX_TEXT . '<br /><a href="' . tep_href_link('advanced_search.php') . '"><strong>' . MODULE_BOXES_SEARCH_BOX_ADVANCED_SEARCH . '</strong></a>'; ?></div>
</div>
