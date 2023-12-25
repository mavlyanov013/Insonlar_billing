<?php

namespace common\models\payment\methods\paycom\api\method;

use common\models\payment\methods\paycom\api\PaycomJsonRPCError;
use common\models\payment\methods\paycom\api\PaycomMerchantApiException;
use common\models\payment\methods\paycom\api\PaycomMethod;
use common\models\payment\Payment;

class CreateTransaction extends PaycomMethod
{
    public $id;
    public $time;
    public $amount;
    public $account;

    public function rules()
    {
        return [
            [['id', 'time', 'amount', 'account'], 'required'],
            [['time'], 'number', 'integerOnly' => true, 'min' => 0],
            [['amount'], 'validAmount'],
            [['account'], 'validAccount'],
        ];
    }

    /**
     * @throws PaycomJsonRPCError
     * @throws PaycomMerchantApiException
     * @throws \yii\base\ErrorException
     * @return array
     */
    protected function processMethod()
    {
        if ($transaction = $this->getTransaction($this->id)) {
            if ($transaction->getPaycomState() == self::TRANSACTION_STATE_PENDING) {
                if ($transaction->hasTimeout()) {
                    $transaction->cancel_time = self::getCurrentTimeStamp();
                    $transaction->status = Payment::STATUS_CANCELLED;
                    $transaction->addAllInformation([
                        'request_id' => $this->_requestId,
                        'state' => $transaction->getPaycomState(),
                        'reason' => PaycomMethod::REASON_4,
                        'reason_error' => self::getReasonError(PaycomMethod::REASON_4),
                    ]);

                    $transaction->save();

                    throw new PaycomMerchantApiException(self::MSG_METHOD_COULD_NOT_BE_PERFORMED, -31008);
                }

                return [
                    'create_time' => $transaction->create_time,
                    'transaction' => $transaction->getId(),
                    'state' => $transaction->getPaycomState(),
                    'receivers' => null,
                ];
            }

            throw new PaycomMerchantApiException(self::MSG_METHOD_COULD_NOT_BE_PERFORMED, -31008);
        }

        $transaction = new Payment();

        $transaction->create_time = self::getCurrentTimeStamp();
        $transaction->time = $this->time;
        $transaction->status = Payment::STATUS_PENDING;
        $transaction->method = $this->_method->getCode();
        $transaction->transaction_id = $this->id;
        $transaction->amount = $this->amount / 100;
        $transaction->user_data = $this->account['user_data'];

        $transaction->addAllInformation([
            'request_id' => $this->_requestId,
            'state' => $transaction->getPaycomState(),
        ]);

        if ($transaction->save()) {
            return [
                'create_time' => $transaction->create_time,
                'transaction' => $transaction->getId(),
                'state' => $transaction->getPaycomState(),
                'receivers' => $transaction->receivers,
            ];
        }

        \Yii::error($transaction->getFirstErrors(), 'paycom');

        throw new PaycomJsonRPCError(self::MSG_FAILED_CREATE_TRANSACTION, -32400);
    }
}