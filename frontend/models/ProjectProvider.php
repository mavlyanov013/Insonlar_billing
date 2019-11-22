<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace frontend\models;


use common\models\Project;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;

class ProjectProvider extends Project
{
    public static function dataProvider($limit = 10)
    {
        $query = self::find()
                     ->andWhere(['status' => self::STATUS_ENABLE])
                     ->orderBy(['created_at' => SORT_DESC]);

        /*if (isRussian()) {
            $query->andWhere(['has_russian' => true]);
        } else {
            $query->andWhere(['has_uzbek' => true]);
        }*/

        return new ActiveDataProvider([
                                          'query'      => $query,
                                          'pagination' => [
                                              'pageSize' => intval(\Yii::$app->request->get('load', $limit)),
                                          ],
                                      ]);
    }

    public function getViewUrl($schema = true)
    {
        return Url::to(['project/view', 'id' => $this->id], $schema);
    }
}