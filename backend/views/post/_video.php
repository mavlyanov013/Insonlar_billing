<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

use backend\widgets\media\EmbedMedia;
use common\models\Post;
use dosamigos\tinymce\TinyMce;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @var $model Post
 * @var $form  ActiveForm
 */
?>

<?= $form->field($model, 'youtube_url')->textInput([]); ?>
<?= $form->field($model, 'mover_url')->textInput([]); ?>
<?php if ($file = $model->getFileUrl('video')) {
    echo EmbedMedia::widget([
                                'type'    => 'video',
                                'file'    => $file,
                                'options' => ['class' => 'mb15'],
                            ]);
} ?>


<?= $form->field($model, 'content')->widget(TinyMce::className(), [
    'clientOptions' => [
        'plugins'           => [
            "advlist autolink lists link imagetools image charmap print hr anchor pagebreak",
            "searchreplace wordcount visualblocks visualchars code fullscreen",
            "insertdatetime media nonbreaking save table contextmenu directionality",
            "emoticons template paste textcolor colorpicker textpattern frontview",
        ],
        'image_title'       => true,
        'image_class_list'  => 'img-responsive',
        'image_dimensions'  => false,
        'automatic_uploads' => true,
        'image_caption'     => true,
        'content_style'     => 'body {max-width: 768px; margin: 5px auto;}.mce-content-body img{width:98%; height:98%}figure.image{margin:0px;width:100%}',
        'images_upload_url' => Url::to(['file-storage/upload', 'type' => 'content-image', 'fileparam' => 'file']),
        'preview_url'       => Url::to('@frontendUrl/preview/' . $model->getId()),
        'toolbar1'          => "undo redo | styleselect blockquote |  bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | link code fullscreen",
    ],
    'options'       => ['rows' => 10],
]) ?>