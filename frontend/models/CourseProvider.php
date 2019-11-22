<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace frontend\models;


use common\models\Course;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;

class CourseProvider extends Course
{
    public static function dataProvider($limit = 10)
    {
        $query = self::find()
                     ->andWhere(['status' => self::STATUS_ENABLE])
                     ->orderBy(['update_at' => SORT_DESC]);


        return new ActiveDataProvider([ 
                                          'query'      => $query,
                                          'pagination' => [
                                              'pageSize' => intval(\Yii::$app->request->get('load', $limit)),
                                          ],
                                      ]);
    }

    public function getViewUrl($schema = true)
    {
        return Url::to(['course/view', 'id' => $this->id], $schema);
    }
}