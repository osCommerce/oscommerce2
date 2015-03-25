<?php
$OSCOM_CategoryTree->setCategoryPath($cPath, '<strong>', '</strong>');
$OSCOM_CategoryTree->setSpacerString('&nbsp;&nbsp;', 1);

$OSCOM_CategoryTree->setParentGroupString('<ul class="nav nav-pills nav-stacked">', '</ul>', true);

$category_tree = $OSCOM_CategoryTree->getTree();
?>
<div class="panel panel-default">
  <div class="panel-heading"><?php echo MODULE_BOXES_CATEGORIES_BOX_TITLE; ?></div>
  <?php echo $category_tree; ?>
</div>
