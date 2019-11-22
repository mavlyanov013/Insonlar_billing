<?php

namespace common\models\payment\methods\paycom\api\method;

use common\models\payment\methods\paycom\api\PaycomJsonRPCError;
use common\models\payment\methods\paycom\api\PaycomMerchantApiException;
use common\models\payment\methods\paycom\api\PaycomMethod;
use common\models\payment\Payment;
use Yii;

class PerformTransaction extends PaycomMethod
{
    public $id;

    public function rules()
    {
        return [
            [['id'], 'required'],
        ];
    }

    /**
     * @return array
     * @throws PaycomJsonRPCError
     * @throws PaycomMerchantApiException
     */
    protected function processMethod()
    {
        if ($transaction = $this->getTransaction($this->id)) {
            if ($transaction->getPaycomState() == self::TRANSACTION_STATE_PENDING) {

                if ($transaction->hasTimeout()) {
                    $transaction->cancel_time = $this->getCurrentTimeStamp();
                    $transaction->status      = Payment::STATUS_CANCELLED;
                    $transaction->addAllInformation([
                                                        'request_id'   => $this->_requestId,
                                                        'state'        => $transaction->getPaycomState(),
                                                        'reason'       => PaycomMethod::REASON_4,
                                                        'reason_error' => $this->getReasonError(PaycomMethod::REASON_4),
                                                    ]);

                    $transaction->save();
                    throw new PaycomMerchantApiException(self::MSG_METHOD_COULD_NOT_BE_PERFORMED, -31008);
                }

                $transaction->status       = Payment::STATUS_SUCCESS;
                $transaction->perform_time = $this->getCurrentTimeStamp();

                $transaction->addAllInformation([
                                                    'request_id' => $this->_requestId,
                                                    'state'      => $transaction->getPaycomState(),
                                                ]);

                if ($transaction->save()) {
                    return [
                        'transaction'  => $transaction->getId(),
                        'perform_time' => $transaction->perform_time,
                        'state'        => $transaction->getPaycomState(),
                    ];
                }

                throw new PaycomJsonRPCError(self::MSG_FAILED_CREATE_TRANSACTION, -32400);

            } else if ($transaction->getPaycomState() == self::TRANSACTION_STATE_SUCCESS) {
                return [
                    'transaction'  => $transaction->getId(),
                    'perform_time' => $transaction->perform_time,
                    'state'        => $transaction->getPaycomState(),
                ];
            } else {
                throw new PaycomMerchantApiException(self::MSG_METHOD_COULD_NOT_BE_PERFORMED, -31008);
            }
        }

        throw new PaycomMerchantApiException(self::MSG_TRANSACTION_NOT_FOUND, -31003);
    }

}