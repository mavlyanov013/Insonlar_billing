<?php
/* @var $this yii\web\View */
$this->registerCss("
body {
   // background-color: #F8F8F8;
}
"); ?>
<?php $this->beginContent('@backend/views/layouts/main.php'); ?>
    <div class="app layout-fixed-header bg-white usersession">
        <div class="full-height">
            <div class="center-wrapper">
                <div class="center-content">
                    <div class="row no-margin">
                        <div class="col-xs-10 col-xs-offset-1 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4">
                            <?= \common\widgets\Alert::widget() ?>
                            <?php echo $content; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $this->endContent(); ?>