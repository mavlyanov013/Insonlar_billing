<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class VendorAsset extends AssetBundle
{
    public $sourcePath = '@app/assets/vendor';

    public $css = [
        '//fonts.googleapis.com/css?family=Quicksand:300,400,500,700|Roboto:400,500,700&subset=cyrillic',
        'css/bootstrap.min.css',
        /*'css/iconfont.css',
        'css/font-awesome.min.css',
        'css/glyphicon.css',*/
        'css/fontello.css',
        'css/fontello-ie7.css',
        'css/isotope.css',
        'css/magnific-popup.css',
        'css/owl.carousel.min.css',
        'css/owl.theme.default.min.css',
        'css/animate.css',
        'css/plugins.css',
    ];

    public $js = [
        'js/plugins.js',
        'js/Popper.js',
        'js/bootstrap.min.js',
        'js/jquery.magnific-popup.min.js',
        'js/owl.carousel.min.js',
        'js/jquery.waypoints.min.js',
        'js/jquery.easypiechart.min.js',
        'js/skrollr.min.js',
        'js/isotope.pkgd.min.js',
        'js/jquery.formatter.min.js',
        'js/jquery.inputmask.min.js',
    ];

    public $depends = [

    ];
}
