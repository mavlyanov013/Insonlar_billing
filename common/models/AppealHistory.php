<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;

/**
 * Class Cart
 * @property string $id
 * @property string $_appeal
 * @property string $comment
 * @property string $_admin
 * @property string $status_before
 * @property string $status_after
 * @property string $created_at
 * @property string $updated_at
 * @property Admin  $admin
 * @package common\models
 */
class AppealHistory extends MongoModel
{
    public static function collectionName()
    {
        return 'appeal_history';
    }


    public function attributes()
    {
        return [
            '_id',
            '_appeal',
            '_admin',
            'comment',
            'status_before',
            'status_after',
            'created_at',
            'updated_at',
        ];
    }

    public function rules()
    {
        return [
            [['comment', 'status_after'], 'safe'],
            [['comment'], 'string', 'max' => 10000],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'value' => $this->getTimestampValue(),
            ],
        ];
    }


    /**
     * @return \yii\db\ActiveQueryInterface
     */
    public function getAdmin()
    {
        return $this->hasOne(Admin::class, ['_id' => '_admin']);
    }


    public function search($params)
    {
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'pageSize' => 30,
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        if ($this->search) {
            $query->orFilterWhere(['like', 'comment', $this->search]);
        }

        return $dataProvider;
    }

    /**
     * @param Appeal $model
     * @return AppealHistory[]
     */
    public static function findByAppeal(Appeal $model)
    {
        return self::find()
                   ->where(['_appeal' => $model->_id])
                   ->addOrderBy(['created_at' => SORT_ASC])
                   ->all();
    }

    public function updateAppeal(Appeal $model)
    {
        $nextStatus = $model->getNextStatusArray();

        if (isset($nextStatus[$this->status_after]) && (!empty($this->comment) || $model->status != $this->status_after)) {

            if (Yii::$app->user && Yii::$app->user->identity)
                $this->_admin = Yii::$app->user->identity->_id;
            $this->_appeal       = $model->_id;
            $this->status_before = $model->status;

            if ($this->save()) {
                if ($model->updateAttributes(['status' => $this->status_after, 'updated_at' => call_user_func($model->getTimestampValue())])) {
                    return true;
                } else {
                    $this->delete();
                }
            }
        }

        return false;
    }
}