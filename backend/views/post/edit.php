<?php

use backend\assets\TinyMceAsset;
use backend\components\View;
use backend\widgets\checkbo\CheckBo;
use backend\widgets\filekit\Upload;
use common\components\Config;
use common\models\Admin;
use common\models\Category;
use common\models\Post;
use dosamigos\selectize\SelectizeTextInput;
use trntv\yii\datetime\DateTimeWidget;
use wbraganca\fancytree\FancytreeWidget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii2mod\chosen\ChosenSelect;

/* @var $this View */
/* @var $model common\models\Post */
/* @var $type string */
/* @var $user Admin */

$this->title                   = $model->getTitleView();
$this->params['breadcrumbs'][] = ['url' => ['post/index'], 'label' => __('Manage Posts')];
$this->params['breadcrumbs'][] = $this->title;
$user                          = $this->context->_user();
?>
<div class="post-create">
    <div class="post-form">
        <?php $form = ActiveForm::begin(['enableAjaxValidation' => true, 'enableClientValidation' => true, 'validateOnSubmit' => false, 'options' => ['id' => 'post_form']]); ?>
        <div class="row">
            <div class="col col-md-9">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h4><?= __('Post Information') ?></h4>
                    </div>
                    <div class="panel-body">
                        <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
                        <?= $form->field($model, 'url')->textInput(['maxlength' => true, 'placeholder' => __('Post Link')])->label(false) ?>
                        <?= $form->field($model, 'info')->textarea(['maxlength' => true, 'rows' => 4, 'placeholder' => __('Short Information')])->label(false) ?>

                        <?= $this->renderFile("@backend/views/post/_$type.php", ['form' => $form, 'model' => $model]) ?>
                        <?= $form->field($model, '_tags')->widget(SelectizeTextInput::className(), [
                            'options' => [],
                            'loadUrl' => Url::to(['post/tag']),

                            'clientOptions' => [
                                'maxItems'     => 100,
                                'maxOptions'   => 10,
                                'hideSelected' => true,
                                'create'       => true,
                                'valueField'   => 'v',
                                'labelField'   => 't',
                                'searchField'  => 't',
                                'options'      => $model->getTagsData(),
                                'plugins'      => ['remove_button', 'drag_drop'],
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>
            <div class="col col-md-3 " id="post_settings">
                <?php if ($model->status == Post::STATUS_PUBLISHED && !$model->hasErrors() && $this->_user()->canAccessToResource('post/share')) : ?>
                    <div class="panel panel-default">

                        <div class="panel-heading">
                            <h4><?= __('Social Share') ?></h4>
                        </div>
                        <div class="panel-body">

                            <?= $form->field($model, 'is_main')->widget(CheckBo::className(), ['type' => 'switch'])->label(__('Is Main')) ?>

                        </div>
                    </div>
                <?php endif; ?>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4><?= __('Settings') ?></h4>
                    </div>
                    <div class="panel-body">
                        <table style="width: 100%">
                            <?php if ($model->created_at): ?>
                                <tr>
                                    <td><label class="control-label"><?= __('Created At') ?></label></td>
                                    <td class="text-right"><?= Yii::$app->formatter->asDatetime($model->created_at->getTimestamp()) ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($model->published_on): ?>
                                <tr>
                                    <td><label class="control-label"><?= __('Published On') ?></label></td>
                                    <td class="text-right"><?= $model->published_on instanceof \MongoDB\BSON\Timestamp ? Yii::$app->formatter->asDatetime($model->published_on->getTimestamp()) : $model->published_on ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($model->updated_on): ?>
                                <tr>
                                    <td><label class="control-label"><?= __('Updated On') ?></label></td>
                                    <td class="text-right"><?= Yii::$app->formatter->asDatetime($model->updated_on->getTimestamp()) ?></td>
                                </tr>
                            <?php endif; ?>
                        </table>
                        <hr>
                        <div class="form-group">
                            <?php if ($model->type == Post::TYPE_VIDEO): ?>
                                <?php if ($file = $model->getYoutubeEmbedUrl()): ?>
                                    <iframe class="embed-responsive-item" src="<?= $file ?>" frameborder="0"
                                            allowfullscreen></iframe>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <?php if (Yii::$app->language == Config::LANGUAGE_RUSSIAN): ?>
                            <?= $form->field($model, 'has_russian')->widget(CheckBo::className(), ['type' => 'switch'])->label(__('Translated in Russian')) ?>
                        <?php endif; ?>


                        <?= $this->renderFile('@backend/views/layouts/_convert.php', ['link' => Url::to(['post/edit', 'id' => $model->getId(), 'convert' => 1])]) ?>

                        <?php if ($model->getId() && $model->status != Post::STATUS_IN_TRASH): ?>
                            <?= $form->field($model, 'status')->widget(ChosenSelect::className(), [
                                'items'         => Post::getStatusArray(),
                                'options'       => ['onchange' => 'statusChanged(this.value)'],
                                'pluginOptions' => ['width' => '100%', 'allow_single_deselect' => true, 'disable_search' => true],
                            ]) ?>

                            <div id="widget_published_on_wrapper" >

                                <?= $form->field($model, 'published_on', [
                                    'options' => [
                                        'value'    => $model->getPublishedOnSeconds(),
                                        'id'       => 'hidden_published_on',
                                        'disabled' => !($model->status == Post::STATUS_PUBLISHED || $model->status == Post::STATUS_DRAFT),
                                    ],
                                ])->hiddenInput(['id' => 'published_on_time', 'value' => $model->getPublishedOnSeconds(),]) ?>
                                <?php $time = $model->getPublishedOnSeconds() ?>
                                    <?= DateTimeWidget::widget([
                                                                   'id'               => 'widget_published_on',
                                                                   'locale'           => Yii::$app->language == Config::LANGUAGE_UZBEK ? 'uz-latn' : (Yii::$app->language == Config::LANGUAGE_CYRILLIC ? 'uz' : 'ru'),
                                                                   'model'            => $model,
                                                                   'name'             => 'date_published_on_time',
                                                                   'value'            => $time ? Yii::$app->formatter->asDatetime($time, 'dd.MM.yyyy, HH:mm') : null,
                                                                   'containerOptions' => [],
                                                                   'clientEvents'     => [
                                                                       'dp.change' => new JsExpression('function(d){
                                                                           time = d.date._d.getTime() / 1000;
                                                                           $("#published_on_time").val(Math.round(time))
                                                                        }'),
                                                                   ],
                                                               ]) ?>
                            </div>

                        <?php endif; ?>


                        <div class="form-group" style="margin-top: 10px">
                            <?= $form->field($model, '_categories', ['options' => ['class' => '']])->hiddenInput(['id' => '_categories']); ?>
                            <?php echo FancytreeWidget::widget([
                                                                   'options' => [
                                                                       'checkbox'   => true,
                                                                       'selectMode' => 2,
                                                                       'source'     => Category::getCategoryTreeAsArray(explode(",", $model->_categories), Config::get(Config::CONFIG_CATALOG_POST_ROOT)),
                                                                       'extensions' => ['dnd'],
                                                                       'select'     => new JsExpression('function(event, data) {
                                                                                    var selNodes = data.tree.getSelectedNodes();
                                                                                    var selKeys = $.map(selNodes, function(node){
                                                                                            return node.key;
                                                                                        });
                                                                                    $("#_categories").val(selKeys.join(","));
                                                                                    console.log(data);
                                                                                }'),
                                                                   ],
                                                               ]); ?>
                        </div>

                        <?= $form->field($model, 'image')->widget(
                            Upload::className(),
                            [
                                'url'              => ['file-storage/upload', 'type' => 'post-image'],
                                'acceptFileTypes'  => new JsExpression('/(\.|\/)(jpe?g|png)$/i'),
                                'sortable'         => true,
                                'maxFileSize'      => 10 * 1024 * 1024, // 10 MiB
                                'maxNumberOfFiles' => 1,
                                'multiple'         => false,
                                'languages'        => array_keys(Config::getShortLanguageOptions()),
                                'clientOptions'    => [],
                            ]
                        )->label(); ?>
                        <?= $form->field($model, 'image_source')->textInput([]); ?>

                        <?php if ($model->type == Post::TYPE_VIDEO && false) {
                            echo $form->field($model, 'video')->widget(Upload::className(), [
                                'url'              => ['file-storage/upload', 'type' => 'video'],
                                'acceptFileTypes'  => new JsExpression('/(\.|\/)(mp4|avi|mpe?g)$/i'),
                                'sortable'         => true,
                                'maxFileSize'      => 10 * 1024 * 1024, // 10 MiB
                                'maxNumberOfFiles' => 1,
                                'multiple'         => false,
                                'clientOptions'    => [],
                            ])->label();
                        } ?>
                        <?php if ($user->isSuperAdmin): ?>
                            <?= $form->field($model, 'short_id')->textInput()->label(__('Short URL')) ?>
                        <?php endif; ?>
                    </div>
                    <div class="panel-footer">
                        <div class="pull-left">
                            <?php if ($model->getId() && $model->status != Post::STATUS_IN_TRASH): ?>
                                <?= Html::a("<i class='fa fa-trash'></i>", ['post/trash', 'id' => $model->getId()], ['class' => 'btn btn-danger btn-trash', 'data-confirm' => __('Are you sure move to trash?')]) ?>
                            <?php endif; ?>

                        </div>
                        <div class="pull-right">
                            <?php if ($model->status != Post::STATUS_IN_TRASH): ?>
                                <?= Html::submitButton('&nbsp;&nbsp;&nbsp;<i class="fa fa-check"></i> ' . __('Save') . '&nbsp;&nbsp;&nbsp;', ['class' => 'btn btn-success']) ?>
                            <?php endif; ?>
                            <?php if ($model->status == Post::STATUS_IN_TRASH && $this->_user()->canAccessToResource('post/restore')): ?>
                                <?= Html::a('&nbsp;&nbsp;&nbsp;<i class="fa fa-check"></i> ' . __('Restore'), ['post/restore', 'id' => $model->getId()], ['class' => 'btn btn-success btn-trash', 'data-confirm' => __('Are you sure to restore?')]) ?>
                            <?php endif; ?>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<?php
TinyMceAsset::register($this);

$this->registerJs('initPostEditor();');
?>
<script>
    var focused = true;

    function statusChanged(status) {
        if (status == 'auto_publish') {
            $('#widget_auto_publish_wrapper').removeClass('hidden');
            $('#hidden_auto_publish').attr('disabled', false);
        } else {
            $('#widget_auto_publish_wrapper').addClass('hidden');
            $('#hidden_auto_publish').attr('disabled', 'disabled');
        }

        if (status == 'published' || status == 'draft') {
            $('#widget_published_on_wrapper').removeClass('hidden');
            $('#hidden_published_on').attr('disabled', false);
        } else {
            $('#widget_published_on_wrapper').addClass('hidden');
            $('#hidden_published_on').attr('disabled', true);
        }

    }

    function initPostEditor() {
        $(window).on("blur focus", function (e) {
            var prevType = $(this).data("prevType");

            if (prevType != e.type) {
                switch (e.type) {
                    case "blur":
                        focused = false;
                        break;
                    case "focus":
                        focused = true;
                        initAutoSave();
                        break;
                }
            }

            $(this).data("prevType", e.type);
        });

        initAutoSave();

        $('#post-title').blur(function () {
            if ($('#post-url').val().length < 2) $('#post-url').val(convertToSlug($(this).val()));
        });

        $('#post_settings').theiaStickySidebar({
            additionalMarginTop: 70,
            additionalMarginBottom: 20
        });

        var $loading = $('#loader').hide();

        $(document).ajaxStart(function () {
            $loading.hide();
        });
    }

    var timeout;

    function initAutoSave() {
        if (focused) {
            clearTimeout(timeout);

            timeout = setTimeout(function () {
                tinymce.triggerSave();
                var form = $('#post_form');

                $.post(
                    form.attr('action') + "?save=1",
                    form.serialize(),
                    function (data, status) {
                        <?=YII_DEBUG ? 'console.log(data);' : ''?>
                        initAutoSave();
                    }
                );
            }, 5000);
        }

    }

    function switchState(input) {
        if (input.is(':checked')) {
        }
    }

    function shareTo(button, social) {
        var btn = $(button);
        var data = {};
        console.log(btn.html());
        btn.button('loading');
        data.sharer = social;
        $.post(
            '<?= Url::to(['post/share', 'id' => $model->getId()]) ?>',
            data,
            function (res) {
                btn.button("reset");
            })
    }
</script>
