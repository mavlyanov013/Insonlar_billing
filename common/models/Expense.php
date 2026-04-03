<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace common\models;

use MongoDB\BSON\Timestamp;
use yii\data\ActiveDataProvider;

/**
 * Class Expense
 * @package common\models
 * @property string comment
 * @property string status
 * @property string _category
 * @property mixed  expense_on
 * @property mixed  name
 * @property mixed  amount
 */
class Expense extends MongoModel
{
    public $_idAttributes         = ['_category'];
    public $_translatedAttributes = [];
    public $_timestampAttributes  = [];
    public $_integerAttributes    = ['amount'];
    public $date_range;
    public $from_date;
    public $to_date;

    public static function collectionName()
    {
        return 'expense';
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'name',
            'comment',
            'amount',
            'files',
            '_category',
            'expense_on',
        ]);
    }

    public function rules()
    {
        return [
            [[
                 'name',
                 'comment',
                 'amount',
                 '_category',
             ], 'required'],
            [['amount'], 'number'],
            [['files', 'expense_on'], 'safe'],
            [['date_range', 'from_date', 'to_date', 'search'], 'safe', 'on' => 'search'],
        ];
    }

    public function beforeSave($insert)
    {
        if (is_numeric($this->expense_on)) {
            $this->expense_on = new Timestamp(1, $this->expense_on);
        }
        return parent::beforeSave($insert);
    }


    /**
     * @return \yii\db\ActiveQueryInterface
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['_id' => '_category']);
    }

    public function search($params)
    {
        $query = self::find()->orderBy(['expense_on' => SORT_DESC]);

        $this->load($params);


        if ($this->search) {
            $query->orFilterWhere(['name' => ['$regex' => $this->search, '$options' => 'si']]);
            $query->orFilterWhere(['comment' => ['$regex' => $this->search, '$options' => 'si']]);


            if (intval($this->search)) {
                $query->orFilterWhere(['$eq', 'amount', intval($this->search)]);
            }
        }


        if ($this->date_range) {
            if ($fdate = strtotime($this->from_date)) {
                $query->andFilterWhere(['$gte', 'expense_on', new Timestamp(1, $fdate)]);
            }
            if ($tdate = strtotime($this->to_date)) {
                $query->andFilterWhere(['$lte', 'expense_on', new Timestamp(1, $tdate)]);
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $dataProvider;
    }

}