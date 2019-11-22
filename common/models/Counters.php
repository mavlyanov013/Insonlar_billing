<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace common\models;


use Yii;
use yii\mongodb\ActiveRecord;

/**
 * Class Comment
 * @property integer votes
 * @package common\models
 */
class Counters extends ActiveRecord
{

    public static function collectionName()
    {
        return 'counters';
    }

    public function attributes()
    {
        return [
            '_id',
            'sequence',
        ];
    }

    public static function getNextSequence($name)
    {
        $result = self::getConnection()
                      ->getCollection(self::collectionName())
                      ->findAndModify(
                          ['_id' => $name],
                          ['$inc' => ['sequence' => 1]],
                          [
                              'upsert' => true,
                              'new'    => true,
                          ]
                      );

        return $result['sequence'];

    }

    /**
     * @return \yii\mongodb\Connection
     */
    private static function getConnection()
    {
        return Yii::$app->mongodb;
    }
}


