<?php

namespace app\assets;

use yii\web\AssetBundle;

class UrbanAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $sourcePath = '@bower';

    public $js = [
    ];

    public $css = [
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];
}
