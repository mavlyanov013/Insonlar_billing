<?php
/**
 * Created by PhpStorm.
 * User: abdujabbor
 * Date: 7/5/16
 * Time: 2:37 PM
 */

namespace app\assets;


use yii\web\AssetBundle;

class UrbanAsset extends AssetBundle
{
    public $basePath = '@webroot';

    public $sourcePath = '@bower';

    public $js  = [
        'jquery.easing/js/jquery.easing.min.js',
        'jquery-countTo/jquery.countTo.js',
        'perfect-scrollbar/js/perfect-scrollbar.jquery.js',
        'jquery.tagsinput/src/jquery.tagsinput.js',
        'chosen_v1.4.0/chosen.proto.min.js',
        'chosen_v1.4.0/chosen.jquery.min.js',
        'checkbo/src/0.1.4/js/checkBo.min.js',
        'fontawesome-iconpicker/dist/js/fontawesome-iconpicker.min.js',
        'theia-sticky-sidebar/dist/theia-sticky-sidebar.min.js'
    ];
    public $css = [
        'perfect-scrollbar/css/perfect-scrollbar.min.css',
        'jquery.tagsinput/src/jquery.tagsinput.css',
        'jquery.tagsinput/src/jquery.tagsinput.css',
        'chosen_v1.4.0/chosen.min.css',
        'checkbo/src/0.1.4/css/checkBo.min.css',
        'components-font-awesome/css/font-awesome.min.css',
        'fontawesome-iconpicker/dist/css/fontawesome-iconpicker.min.css',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];

}