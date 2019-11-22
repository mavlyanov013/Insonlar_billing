<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace backend\widgets\mediaelement;

use yii\web\AssetBundle;

/**
 * Asset bundle
 */
class MediaElementAsset extends AssetBundle
{
    public $sourcePath = '@bower/mediaelement';

    /**
     * @inheritdoc
     */
    public $css = [
        'build/mediaelementplayer.css',
    ];

    /**
     * @inheritdoc
     */
    public $js = [
        'build/mediaelement-and-player.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\JqueryAsset',
    ];

}
