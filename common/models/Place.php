<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace common\models;

use MongoDB\BSON\ObjectID;
use Yii;
use yii\data\ActiveDataProvider;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * Class Place
 * @property string           title
 * @property string           status
 * @property string           slug
 * @property array|\Countable _ads
 * @property array|\Countable _ads_percent
 * @property string           mode
 * @property string           _user
 *
 * @property string           _translations
 *
 * @package common\models
 */
class Place extends MongoModel
{
    const MODE_ONE  = 'one';
    const MODE_RAND = 'rand';

    const STATUS_DISABLE = 'disable';
    const STATUS_ENABLE  = 'enable';
    const STATUS_PENDING = 'pending';

    protected $_translatedAttributes = ['title'];

    public static function collectionName()
    {
        return 'place';
    }


    public function attributes()
    {
        return [
            '_id',
            'title',
            'status',
            'slug',
            '_ads',
            '_ads_percent',
            'mode',
            '_user',
            'created_at',
            'updated_at',
            '_translations',
        ];
    }

    public function rules()
    {
        return [
            [['title', 'status', 'mode'], 'required', 'on' => ['insert', 'update']],
            [['title', 'slug'], 'unique', 'on' => ['insert', 'update']],

            [['status'], 'in', 'range' => array_keys(self::getStatusArray())],
            [['mode'], 'in', 'range' => array_keys(self::getModeArray())],
            [['title', 'slug'], 'safe', 'on' => ['insert', 'update']],
            [['slug'], 'required', 'on' => ['update']],
            [['title'], 'string', 'max' => 255],
            [['search'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'value' => $this->getTimestampValue(),
            ],
        ];
    }

    public static function getAll()
    {
        $all = self::find()->all() ?: [];

        return ArrayHelper::map(
            $all,
            function (self $model) {
                return $model->getId();
            }, 'title'
        );
    }

    public static function getModeArray()
    {
        return [
            self::MODE_ONE  => __('One'),
            self::MODE_RAND => __('Rand'),
        ];
    }

    public function getMode()
    {
        $arr = self::getModeArray();

        return isset($arr[$this->mode]) ? $arr[$this->mode] : $this->mode;
    }

    public static function getStatusArray()
    {
        return [
            self::STATUS_DISABLE => __('Disable'),
            self::STATUS_ENABLE  => __('Enable'),
            self::STATUS_PENDING => __('Pending'),
        ];
    }

    public function getAdsProvider()
    {
        $ids = \count((array)$this->_ads) ? $this->_ads : [];
        if (!empty($ids) && count($ids)) {
            $ids = array_map(function ($v) {
                return new ObjectID($v);
            }, $ids);
        }

        $query = Ad::find()
                   ->where(['_id' => ['$in' => count($ids) ? array_values($ids) : []]]);

        return new ActiveDataProvider([
                                          'query'      => $query,
                                          'sort'       => [
                                              'defaultOrder' => ['updated_at' => SORT_DESC],
                                          ],
                                          'pagination' => [
                                              'pageSize' => 10,
                                          ],
                                      ]);
    }

    public function getAdsNinProvider()
    {
        $ids = count((array)$this->_ads) ? $this->_ads : [];
        if (!empty($ids) && count($ids)) {
            $ids = array_map(function ($v) {
                return new ObjectID($v);
            }, $ids);
        }

        $query = Ad::find()
                   ->where(['_id' => ['$nin' => count($ids) ? array_values($ids) : []]]);

        return new ActiveDataProvider([
                                          'query'      => $query,
                                          'sort'       => [
                                              'defaultOrder' => ['updated_at' => SORT_DESC],
                                          ],
                                          'pagination' => [
                                              'pageSize' => 15,
                                          ],
                                      ]);
    }

    public function getStatusLabel()
    {
        $arr = self::getStatusArray();

        return isset($arr[$this->status]) ? $arr[$this->status] : $this->status;
    }

    public function addAds($data)
    {
        $this->_ads = array_unique(array_merge($this->_ads ? $this->_ads : [], $data));;

        $percents = [];
        $ads      = [];

        foreach ($this->_ads as $id) {
            $percents[(string)$id] = isset($this->_ads_percent[(string)$id]) ? $this->_ads_percent[(string)$id] : 1;
            $ads[(string)$id]      = (string)$id;
        }

        $this->updateAttributes(['_ads_percent' => $percents, '_ads' => array_values($ads)]);
    }

    public function removeAds($data)
    {
        $percents = $this->_ads_percent;
        $ads      = $this->_ads;


        foreach ($percents as $i => $id) {
            if (in_array($id, $data)) {
                unset($percents[$i]);
            }
        }

        foreach ($ads as $i => $id) {
            unset($percents[$id]);

            if (in_array((string)$id, $data)) {
                unset($ads[$i]);
            }
        }

        $this->updateAttributes(['_ads_percent' => $percents, '_ads' => $ads]);
    }

    public function getAddPercent(Ad $ad)
    {
        $percent = $this->_ads_percent;
        if (isset($percent[$ad->getId()])) {
            return $percent[$ad->getId()];
        }

        return 0;
    }

    public function changeAdPercent(Ad $ad, $percent)
    {
        $percents = $this->_ads_percent;
        $percent  = $this->getAddPercent($ad) + $percent;

        if ($percent < 1) $percent = 1;

        $percents[$ad->getId()] = $percent;
        $this->updateAttributes(['_ads_percent' => $percents]);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
                                                   'query'      => $query,
                                                   'pagination' => [
                                                       'pageSize' => 30,
                                                   ],
                                               ]);


        if ($this->search) {
            $query->orFilterWhere(['like', 'title', $this->search]);
        }

        return $dataProvider;
    }

    public function getDate($format = null)
    {
        return Yii::$app->formatter->asDatetime(
            is_object($this->created_at) ? $this->created_at->getTimestamp() : $this->created_at,
            $format
        );
    }

    public function beforeSave($insert)
    {
        if (Yii::$app instanceof \yii\web\Application && $this->isNewRecord) {
            $this->_user = Yii::$app->user->identity->getId();
        }

        return parent::beforeSave($insert);
    }


    public function checkStatus()
    {
        if ($this->status != self::STATUS_DISABLE) {
            if (count($this->_ads)) {
                $ids = array_map(function ($id) {
                    return new ObjectID($id);
                }, $this->_ads);

                $ads = Ad::find()
                         ->where(['_id' => ['$in' => $ids]])
                         ->andWhere(['status' => self::STATUS_ENABLE])
                         ->indexBy('id')
                         ->all();

                if (count($ads)) {
                    return $this->updateAttributes(['status' => self::STATUS_ENABLE]);
                }
            }

            $this->updateAttributes(['status' => self::STATUS_PENDING]);
        }
    }

    public static function reindexStatuses()
    {
        $places = self::find()
                      ->where([
                                  'status' => [
                                      '$in' => [self::STATUS_ENABLE, self::STATUS_PENDING]],
                              ])
                      ->all();
        foreach ($places as $place) {
            $place->checkStatus();
        }
    }
}