<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace frontend\models;

use common\models\payment\Payment;
use yii\data\ActiveDataProvider;

/**
 * Class Expense
 * @package common\models
 * @property string comment
 * @property string status
 * @property mixed  expense_on
 * @property mixed  name
 * @property mixed  amount
 */
class Payments extends Payment
{
    public $date_range;
    public $from_date;
    public $to_date;
    public $search;

    public function rules()
    {
        return [
            [['date_range', 'from_date', 'to_date', 'search'], 'safe', 'on' => 'search'],
        ];
    }

    public function search($params, $provider = true)
    {
        $this->load($params);


        $query = self::find()->orderBy(['time' => SORT_DESC]);


        if ($this->search) {
            $query->orFilterWhere(['user_data' => ['$regex' => $this->search, '$options' => 'si']]);
            $query->orFilterWhere(['transaction_id' => ['$regex' => $this->search, '$options' => 'si']]);


            if (intval($this->search)) {
                $query->orFilterWhere(['$eq', 'amount', intval($this->search)]);
            }
        }

        $query->andFilterWhere(['status' => self::STATUS_SUCCESS]);

        if ($this->date_range) {
            if ($fdate = strtotime($this->from_date)) {
                $query->andFilterWhere(['$gte', 'time', $fdate * 1000]);
            }
            if ($tdate = strtotime($this->to_date)) {
                $query->andFilterWhere(['$lte', 'time', $tdate * 1000]);
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'pageSize' => 30,
            ],
        ]);

        return $dataProvider;
    }

}