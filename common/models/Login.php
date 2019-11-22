<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\BaseActiveRecord;

/**
 * This is the model class for table "region_city".
 *
 * @property string $id
 * @property string $status
 * @property string $type
 * @property string $ip
 * @property string $login
 * @property string $created_at
 *
 */
class Login extends MongoModel
{
    const STATUS_SUCCESS = 'success';
    const STATUS_FAIL    = 'fail';
    const TYPE_ADMIN     = 'admin';
    const TYPE_USER      = 'user';

    public function attributes()
    {
        return ['_id', 'status', 'type', 'ip', 'login', 'created_at'];
    }

    public static function getIsAdminOptions()
    {
        return [
            ''      => __('Type'),
            'admin' => __('Admin'),
            'user'  => __('User'),
        ];
    }

    public static function getStatusOptions()
    {
        return [
            ''        => __('Status'),
            'success' => __('Success'),
            'fail'    => __('Fail'),
        ];
    }

    public function getStatusLabel()
    {
        $labels = self::getStatusOptions();
        return isset($labels[$this->status]) ? $labels[$this->status] : '';
    }

    public function behaviors()
    {
        return [
            [
                'class'      => TimestampBehavior::className(),
                'value'      => $this->getTimestampValue(),
                'attributes' => [
                    BaseActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'login';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['search', 'type', 'status'], 'safe', 'on' => 'search'],
        ];
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
                                                   'query'      => $query,
                                                   'sort'       => [
                                                       'defaultOrder' => ['created_at' => SORT_DESC],
                                                       'attributes'   => [
                                                           'login',
                                                           'ip',
                                                           'status',
                                                           'created_at',
                                                           'admin',
                                                       ],
                                                   ],
                                                   'pagination' => [
                                                       'pageSize' => 50,
                                                   ],
                                               ]);

        if ($this->search) {
            $query->orWhere(['like', 'login', $this->search]);
            $query->orFilterWhere(['like', 'ip', $this->search]);
        }

        if ($this->status != -1) {
            $query->andFilterWhere(['status' => $this->status]);
        }
        if ($this->type != -1) {
            $query->andFilterWhere(['type' => $this->type]);
        }


        return $dataProvider;
    }

}
