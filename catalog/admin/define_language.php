<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

    use OSC\OM\Cache;
    use OSC\OM\DateTime;
    use OSC\OM\HTML;
    use OSC\OM\OSCOM;

    require('includes/application_top.php');

    $action = (isset($_GET['action']) ? $_GET['action'] : '');

    if (tep_not_null($action)) {
        switch ($action) {
            case 'save':
        
            if (isset($_GET['content_group'])) $content_group = $_GET['content_group'];
            if (isset($_POST['definition_key'])) $definition_key = $_POST['definition_key'];
            if (isset($_POST['definition_value'])) $definition_value = $_POST['definition_value'];

            foreach ($definition_value as $id => $definition) {
                $sql_data_array = array(
                    'definition_value' => $definition
                );
                $OSCOM_Db->save('languages_definitions', $sql_data_array, ['id' => (int)$id]);
            }
            
            $group = str_replace("-", "/", $content_group) . '.txt';
            $file = OSCOM::getConfig('dir_root', 'Shop') . 'includes/languages/' . $OSCOM_Language->get('directory', $OSCOM_Language->get('code')) . '/' . $group;
            unlink($file);
            
            $languages_definitions_array = array_combine ($definition_key, $definition_value);
            foreach ($languages_definitions_array as $defKey => $defVal) {
              $data = $defKey . ' = ' . $defVal;
              file_put_contents($file, $data . PHP_EOL, FILE_APPEND | LOCK_EX);  
            }
            
            Cache::clear('languages-defs-' . $content_group . '-lang' . $OSCOM_Language->getId());
            
            OSCOM::redirect('define_language.php', 'content_group=' . $content_group . '&action=edit');
        }
    }
      $languages = tep_get_languages();

    require($oscTemplate->getFile('template_top.php'));
    
    $heading_title = isset($_GET['content_group']) ? OSCOM::getDef('heading_title_2', ['content_group' => $_GET['content_group']]) : OSCOM::getDef('heading_title');
    ?>

    <h2><i class="fa fa-language"></i> <a href="<?= OSCOM::link('define_language.php'); ?>"><?= $heading_title; ?></a></h2>

    <?php

    if (isset($_GET['content_group'])) {
        echo HTML::form('define_language', OSCOM::link('define_language.php', 'content_group=' . $_GET['content_group'] . '&action=save', 'post', 'enctype="multipart/form-data"'));
     
     ?>   
        <ul class="nav nav-tabs">
            <?php
                for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
                    echo '<li ' . ($i === 0 ? 'class="active"' : '') . '><a data-target="#section_general_content_' . $languages[$i]['directory'] . '" data-toggle="tab">' . $OSCOM_Language->getImage($languages[$i]['code']) . '&nbsp;' . $languages[$i]['name'] . '</a></li>';
                }
            ?>
        </ul>
        <div class="tab-content">
<?php

        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
                                       
            
?>
            <div id="section_general_content_<?php echo $languages[$i]['directory']; ?>" class="tab-pane <?= ($i === 0 ? 'active' : ''); ?>">
                <div class="panel panel-info oscom-panel">
                    <div class="panel-body">
                        <div class="container-fluid">
                            <div class="row">

                                <table class="oscom-table table table-bordered table-hover">
                                    
                                    <thead>
                                        <tr class="info">
                                            <th class="col-md-4"><?= OSCOM::getDef('table_heading_definition_key'); ?></th>
                                            <th class="col-md-8"><?= OSCOM::getDef('table_heading_definition_value'); ?></th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
                                        <?php
                                            $Qdefinitions = $OSCOM_Db->prepare('select id, definition_key, definition_value from :table_languages_definitions where content_group = :content_group and languages_id = :languages_id ');
                                            $Qdefinitions->bindValue(':content_group', $_GET['content_group']);
                                            $Qdefinitions->bindInt(':languages_id', $languages[$i]['id']);
                                            $Qdefinitions->execute();
                                            
                                            while ($Qdefinitions->fetch()) {
                                             ?>
                                                <tr>
                                                  <td><input type="hidden" name="definition_key[<?= $Qdefinitions->value('id'); ?>]" value="<?= htmlentities($Qdefinitions->value('definition_key')); ?>"><?= $Qdefinitions->value('definition_key'); ?></td>
                                                  <td><input type="text" class="form-control" name="definition_value[<?= $Qdefinitions->value('id'); ?>]" value="<?= htmlentities($Qdefinitions->value('definition_value')); ?>"></td>
                                                </tr>
                                            
                                             <?php
                                            }
                                        ?>  
                                    </tbody>
                                </table>   
                            
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    <?php
        }
    
?>
                <div class="btn-group pull-right">
                  <?= HTML::button(OSCOM::getDef('image_back'), 'fa fa-chevron-left', OSCOM::link('define_language.php')); ?>
                  <?= HTML::button(OSCOM::getDef('image_save'), 'fa fa-save'); ?>
                </div>

        </div>
    </form>        
<?php
    }
    if ( empty($action) && !isset($_GET['content_group']) ) {
    ?>
        <table class="oscom-table table table-bordered table-hover">
            
            <thead>
                <tr class="info">
                    <th class="col-md-8"><?= OSCOM::getDef('table_heading_content_group_title'); ?></th>
                    <th class="col-md-4 action"><?= OSCOM::getDef('table_heading_content_group_action'); ?></th>
                </tr>
            </thead>
            
            <tbody>
                <?php
                    $Qcontent_group = $OSCOM_Db->prepare('select distinct content_group from :table_languages_definitions');
                    $Qcontent_group->execute();
                    
                    while ($Qcontent_group->fetch()) {
                     ?>
                        <tr>
                            <td><?= $Qcontent_group->value('content_group'); ?></td>
                            <td class="action"><a href="<?= OSCOM::link('define_language.php?content_group=' . $Qcontent_group->value('content_group') . '&action=edit'); ?>"><i class="fa fa-pencil" title="<?= OSCOM::getDef('image_edit'); ?>"></i></a></td>
                        </tr>
                     <?php
                    }
                ?>  
            </tbody>
        </table>  

    <?php
    }

    require($oscTemplate->getFile('template_bottom.php'));
    require('includes/application_bottom.php');
?>
