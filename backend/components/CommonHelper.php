<?php
/**
 * Created by PhpStorm.
 * User: shavkat
 * Date: 7/19/17
 * Time: 10:19 AM
 */

namespace backend\components;


class CommonHelper
{
    public static function pageTotal($models, $fieldName)
    {
        $total = 0;
        foreach ($models as $item) {
            $total += $item->$fieldName;
        }
        return \Yii::$app->formatter->asDecimal($total);
    }
}