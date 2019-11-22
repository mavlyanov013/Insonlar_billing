<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

/**
 * Created by PhpStorm.
 * User: rustam
 * Date: 6/28/18
 * Time: 6:12 PM
 */

namespace frontend\models;


use common\models\Post;
use yii\data\ActiveDataProvider;

class PostProvider extends Post
{

    public static function findBySlug($slug)
    {
        return self::find()
                   ->where(['url' => $slug])
                   ->andWhere(['status' => self::STATUS_PUBLISHED])
                   ->one();
    }

    public static function dataProvider($limit = 10, $category = false)
    {
        $query = self::find()
                     ->andWhere(['status' => Post::STATUS_PUBLISHED])
                     ->orderBy(['published_on' => SORT_DESC]);

        if ($category) {
            $query->andFilterWhere(['_categories' => ['$in' => [$category->id]]]);
        }

        return new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'pageSize' => intval(\Yii::$app->request->get('load', $limit)),
            ],
        ]);
    }

    /**
     * @param $limit
     * @return Post[]
     */
    public static function getLastPosts($limit)
    {
        return self::find()
                   ->andWhere(['status' => Post::STATUS_PUBLISHED])
                   ->orderBy(['published_on' => SORT_DESC])
                   ->limit($limit)
                   ->all();
    }
}