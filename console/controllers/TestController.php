<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace console\controllers;

use common\models\_MongoModel;
use common\models\Admin;
use common\models\license\Coupon;
use common\models\license\Product;
use GuzzleHttp\Client;
use Yii;
use yii\console\Controller;
use yii\mongodb\ActiveRecord;

class TestController extends Controller
{
    public function actionIndex()
    {
        $cont = '<div class="xs-services xs-bg" style="background-image: url(\'{{backgrounds/bg-1.png}}\');">';
        $content = preg_replace_callback('/({{)(.*)(}})/', function ($matches) {
            return $this->vard($matches);
        }, $cont);
        echo $content;
        echo "\n";
    }

    protected function vard($mat)
    {
        var_dump($mat);
        return 'asfafa.sds';
    }

}