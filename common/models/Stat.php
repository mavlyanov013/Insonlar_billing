<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace common\models;


use Yii;

/**
 * Class Comment
 * @property string     question
 * @property string     status
 * @property string     expire_time
 * @property string[]   answers
 * @property PollItem[] items
 * @property integer    votes
 * @package common\models
 */
class Stat extends MongoModel
{
    protected $_integerAttributes = ['count', 'day', 'month', 'year'];
    const TYPE_POST_VIEW = 'pv';
    const TYPE_AD_VIEW   = 'av';
    const TYPE_AD_CLICK  = 'ac';

    public static function collectionName()
    {
        return 'stat';
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'type',
            'model',
            'hour',
            'day',
            'month',
            'year',
            'count',
        ]);
    }

    public static function registerPostView(Post $post, $count = 1)
    {
        $date = new \DateTime();

        $key = [
            'type'  => self::TYPE_POST_VIEW,
            'model' => $post->_id,
            'hour'  => (int)$date->format('H'),
            'day'   => (int)$date->format('d'),
            'month' => (int)$date->format('m'),
            'year'  => (int)$date->format('Y'),
        ];

        if ($stat = self::find()->where($key)->one()) {
            return $stat->updateCounters(['count' => $count]);
        } else {
            $key['count'] = $count;
            $key['time']  = (int)$date->format('U');

            return boolval(self::getConnection()
                               ->getCollection(self::collectionName())
                               ->insert($key));
        }
    }

    public static function registerAdView(Ad $ad)
    {
        $date = new \DateTime();

        $key = [
            'type'  => self::TYPE_AD_VIEW,
            'model' => $ad->_id,
            'hour'  => (int)$date->format('H'),
            'day'   => (int)$date->format('d'),
            'month' => (int)$date->format('m'),
            'year'  => (int)$date->format('Y'),
        ];

        if ($stat = self::find()->where($key)->one()) {
            return $stat->updateCounters(['count' => 1]);
        } else {
            $key['count'] = 1;
            $key['time']  = (int)$date->format('U');

            return boolval(self::getConnection()
                               ->getCollection(self::collectionName())
                               ->insert($key));
        }
    }

    public static function registerAdClick(Ad $ad)
    {
        $date = new \DateTime();

        $key = [
            'type'  => self::TYPE_AD_CLICK,
            'model' => $ad->_id,
            'hour'  => (int)$date->format('H'),
            'day'   => (int)$date->format('d'),
            'month' => (int)$date->format('m'),
            'year'  => (int)$date->format('Y'),
        ];

        if ($stat = self::find()->where($key)->one()) {
            return $stat->updateCounters(['count' => 1]);
        } else {
            $key['count'] = 1;
            $key['time']  = (int)$date->format('U');

            return boolval(self::getConnection()
                               ->getCollection(self::collectionName())
                               ->insert($key));
        }
    }

    public static function indexPostViewsReset()
    {
        Post::updateAll([
                            'views_l3d'   => 0,
                            'views_l7d'   => 0,
                            'views_l30d'  => 0,
                            'views_today' => 0,
                        ]);
    }

    public static function indexAdViewsAll()
    {
        echo "indexAdViewsAll===================\n";
        $result = self::getConnection()
                      ->getCollection(self::collectionName())
                      ->aggregate(
                          ['$match' => [
                              'time' => ['$gt' => 0],
                              'type' => ['$eq' => self::TYPE_AD_VIEW],
                          ]],
                          array('$group' => array(
                              '_id'   => '$model',
                              'count' => ['$sum' => '$count'],
                          ))
                      );

        foreach ($result as $item) {
            Ad::updateAll(['views' => $item['count']], ['_id' => $item['_id']]);
        }
    }

    public static function indexAdClicksAll()
    {
        echo "indexAdClicksAll===================\n";
        $result = self::getConnection()
                      ->getCollection(self::collectionName())
                      ->aggregate(
                          ['$match' => [
                              'time' => ['$gt' => 0],
                              'type' => ['$eq' => self::TYPE_AD_CLICK],
                          ]],
                          array('$group' => array(
                              '_id'   => '$model',
                              'count' => ['$sum' => '$count'],
                          ))
                      );

        foreach ($result as $item) {
            Ad::updateAll(['clicks' => $item['count']], ['_id' => $item['_id']]);
        }
    }

    public static function indexPostViewsAll()
    {
        echo "indexPostViewsAll===================\n";
        $result = self::getConnection()
                      ->getCollection(self::collectionName())
                      ->aggregate(
                          ['$match' => ['time' => ['$gt' => 0]]],
                          array('$group' => array(
                              '_id'   => '$model',
                              'count' => ['$sum' => '$count'],
                          ))
                      );

        foreach ($result as $item) {
            Post::updateAll(['views' => $item['count']], ['_id' => $item['_id']]);
            if ($item['count'] >= Notification::POST_VIEW_STEP) {
                if ($post = Post::findOne($item['_id'])) {
                    Notification::createPostViewNotification($post, $item['count']);
                }
            }
        }
    }

    public static function indexPostViewsL3D()
    {
        echo "indexPostViewL3D===================\n";
        $date = new \DateTime();
        $time = (int)$date->format('U')
            - 3 * 24 * 3600
            - ((int)$date->format('h')) * 3600;

        $result = self::getConnection()
                      ->getCollection(self::collectionName())
                      ->aggregate(
                          ['$match' => ['time' => ['$gt' => $time]]],
                          ['$group' => [
                              '_id'   => '$model',
                              'count' => ['$sum' => '$count'],
                          ]]
                      );

        foreach ($result as $item) {
            Post::updateAll(['views_l3d' => $item['count']], ['_id' => $item['_id']]);
        }
    }

    public static function indexPostViewsL7D()
    {
        echo "indexPostViewL7D===================\n";
        $date = new \DateTime();
        $time = (int)$date->format('U')
            - 7 * 24 * 3600
            - ((int)$date->format('h')) * 3600;

        $result = self::getConnection()
                      ->getCollection(self::collectionName())
                      ->aggregate(
                          ['$match' => ['time' => ['$gt' => $time]]],
                          ['$group' => [
                              '_id'   => '$model',
                              'count' => ['$sum' => '$count'],
                          ]]
                      );

        foreach ($result as $item) {
            Post::updateAll(['views_l7d' => $item['count']], ['_id' => $item['_id']]);
        }
    }

    public static function indexPostViewsL30D()
    {
        echo "indexPostViewL30D===================\n";

        $date = new \DateTime();
        $time = (int)$date->format('U')
            - 30 * 24 * 3600
            - ((int)$date->format('h')) * 3600;

        $result = self::getConnection()
                      ->getCollection(self::collectionName())
                      ->aggregate(
                          ['$match' => ['time' => ['$gt' => $time]]],
                          ['$group' => [
                              '_id'   => '$model',
                              'count' => ['$sum' => '$count'],
                          ]]
                      );

        foreach ($result as $item) {
            Post::updateAll(['views_l30d' => $item['count']], ['_id' => $item['_id']]);
        }
    }

    public static function indexPostViewsToday()
    {
        echo "indexPostViewToday===================\n";
        $date = new \DateTime();
        $time = (int)$date->format('U')
            - ((int)$date->format('h')) * 3600;

        $result = self::getConnection()
                      ->getCollection(self::collectionName())
                      ->aggregate(
                          ['$match' => ['time' => ['$gt' => $time]]],
                          ['$group' => [
                              '_id'   => '$model',
                              'count' => ['$sum' => '$count'],
                          ]]
                      );

        foreach ($result as $item) {
            Post::updateAll(['views_today' => $item['count']], ['_id' => $item['_id']]);
        }
    }

    /**
     * @return \yii\mongodb\Connection
     */
    private static function getConnection()
    {
        return Yii::$app->mongodb;
    }
}


