<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace common\models;


use DateTime;
use MongoDB\BSON\Timestamp;
use MongoTimestamp;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * Class Comment
 * @property string    $_user
 * @property string    $name
 * @property string    $ga
 * @property string    $ip
 * @property string    $url
 * @property string    $message
 * @property string    $data
 * @property string    $browser
 * @property Timestamp $created_at
 * @package common\models
 */
class Log extends MongoModel
{
    public function rules()
    {
        return [
            [['search'], 'safe'],
        ];
    }

    public static function collectionName()
    {
        return 'log';
    }

    public function attributes()
    {
        return [
            '_id',
            '_user',
            'name',
            'message',
            'ip',
            'ga',
            'url',
            'get',
            'post',
            'browser',
            'created_at',
        ];
    }

    public static function registerAction($message)
    {
        $request = Yii::$app->request;
        $user    = Yii::$app->user->isGuest ? new Admin() : Yii::$app->user->identity;

        $post = $_POST;
        foreach ($post as $k => $value) {
            if ($k == '_csrf') unset($post[$k]);
            if (is_array($value)) {
                foreach ($value as $i => $v) {
                    if ($i == 'password' || $i == 'confirmation') $post[$k][$i] = '****';
                }
            }
        }

        $get = $_GET;
        foreach ($get as $k => $value) {
            if ($k == '_csrf' || $k == '_pjax') unset($get[$k]);
        }

        $data = [
            '_user'      => $user->getId(),
            'name'       => $user->getFullname(),
            'message'    => $message,
            'ip'         => $request->getUserIP(),
            'ga'         => isset($_COOKIE['_ga']) ? $_COOKIE['_ga'] : (isset($_COOKIE['_gab']) ? $_COOKIE['_gab'] : ''),
            'url'        => $request->hostName . '/' . $request->pathInfo,
            'get'        => http_build_query($get),
            'post'       => json_encode($post),
            'browser'    => $request->getUserAgent(),
            'created_at' => new Timestamp(1, (new DateTime())->getTimestamp()),
        ];

        return self::getConnection()
                   ->getCollection(self::collectionName())
                   ->insert($data);
    }


    /**
     * @return \yii\mongodb\Connection
     */
    private static function getConnection()
    {
        return Yii::$app->mongodb;
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
                                                   'query'      => $query,
                                                   'sort'       => [
                                                       'defaultOrder' => [
                                                           'created_at' => SORT_DESC,
                                                       ],
                                                       'attributes'   => [
                                                           '_user',
                                                           'name',
                                                           'ip',
                                                           'url',
                                                           'post',
                                                           'get',
                                                           'created_at',
                                                       ],
                                                   ],
                                                   'pagination' => [
                                                       'pageSize' => 50,
                                                   ],
                                               ]);

        if ($this->search) {
            $query->orWhere(['like', 'name', $this->search]);
            $query->orWhere(['like', '_user', $this->search]);
            $query->orWhere(['like', 'ip', $this->search]);
            $query->orWhere(['like', 'ga', $this->search]);
            $query->orWhere(['like', 'url', $this->search]);
            $query->orWhere(['like', 'get', $this->search]);
            $query->orWhere(['like', 'post', $this->search]);
            $query->orWhere(['like', 'browser', $this->search]);
        }

        return $dataProvider;
    }

    public static function cleanLogs()
    {
        return self::deleteAll([
                                   'created_at' => ['$lt' => new Timestamp(1, (new DateTime())->getTimestamp() - 30 * 24 * 3600)],
                                   'post'       => ['$eq' => "[]"],
                               ]);
    }
}


