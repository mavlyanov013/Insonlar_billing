<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

/**
 * Created by PhpStorm.
 * Date: 11/9/17
 * Time: 9:54 PM
 */

namespace backend\widgets\tags;


use yii\web\AssetBundle;

class TagsInputAsset extends AssetBundle
{
    public $sourcePath = '@bower/jquery.tagsinput';

    public $js = [
        'src/jquery.tagsinput.js',
    ];

    public $css = [
        'src/jquery.tagsinput.css',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\jui\JuiAsset',
    ];
}