<?php

namespace common\models\payment\methods\paycom\api\method;

use common\models\payment\methods\paycom\api\PaycomMethod;
use common\models\payment\Payment;
use common\models\UserBalanceFund;

class GetStatement extends PaycomMethod
{
    public $from;
    public $to;

    public function rules()
    {
        return [
            [['from', 'to'], 'required'],
            [['from', 'to'], 'number', 'integerOnly' => true, 'min' => 0],
        ];
    }

    protected function processMethod()
    {
        /**
         * @var $transaction Payment
         */
        $data   = Payment::find()
                         ->andWhere(['$eq', 'method', $this->_method->getCode()])
                         ->andWhere(['$gte', 'time', $this->from])
                         ->andWhere(['$lte', 'time', $this->to])
                         ->orderBy('time')
                         ->all();
        $result = [];

        foreach ($data as $transaction) {
            $result[] = [
                'id'           => $transaction->transaction_id,
                'time'         => $transaction->time,
                'amount'       => $transaction->getAmountCents(),
                'account'      => [
                    'user_data' => $transaction->user_data,
                ],
                'create_time'  => $transaction->create_time,
                'perform_time' => $transaction->perform_time,
                'cancel_time'  => $transaction->cancel_time,
                'transaction'  => $transaction->getId(),
                'state'        => $transaction->getPaycomState(),
                'reason'       => $transaction->getInfo('reason'),
                'receivers'    => null,
            ];
        }

        return ['transactions' => $result];

    }


}