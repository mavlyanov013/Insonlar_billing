<?php

namespace common\models\payment\methods\paycom\api\method;

use common\models\Order;
use common\models\OrderComment;
use common\models\payment\methods\paycom\api\PaycomJsonRPCError;
use common\models\payment\methods\paycom\api\PaycomMerchantApiException;
use common\models\payment\methods\paycom\api\PaycomMethod;
use common\models\payment\Payment;
use common\models\UserBalanceFund;
use common\models\UserBalanceHistory;
use Yii;

class CancelTransaction extends PaycomMethod
{
    public $id;
    public $reason;

    public function rules()
    {
        return [
            [['id', 'reason'], 'required'],
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
            $state = $transaction->getPaycomState();

            if ($state === self::TRANSACTION_STATE_PENDING) {
                $transaction->cancel_time = $this->getCurrentTimeStamp();
                $transaction->status      = Payment::STATUS_CANCELLED;
                $transaction->addAllInformation([
                                                    'request_id'   => $this->_requestId,
                                                    'state'        => $transaction->getPaycomState(),
                                                    'reason'       => $this->reason,
                                                    'reason_error' => $this->getReasonError($this->reason),
                                                ]);
                if ($transaction->save()) {
                    return [
                        'transaction' => $transaction->getId(),
                        'cancel_time' => $transaction->cancel_time,
                        'state'       => $transaction->getPaycomState(),
                    ];
                }

                throw new PaycomJsonRPCError(self::MSG_FAILED_CREATE_TRANSACTION, -32400);

            } elseif ($state === self::TRANSACTION_STATE_SUCCESS) {

                //TODO check if payment amount not spent for outgoings
                $canBeCancelled = true;
                if ($canBeCancelled) {
                    $transaction->cancel_time = $this->getCurrentTimeStamp();
                    $transaction->status      = Payment::STATUS_CANCELLED;
                    $transaction->addAllInformation([
                                                        'request_id'   => $this->_requestId,
                                                        'state'        => $transaction->getPaycomState(),
                                                        'reason'       => $this->reason,
                                                        'reason_error' => $this->getReasonError($this->reason),
                                                    ]);
                    if ($transaction->save()) {
                        return [
                            'transaction' => $transaction->getId(),
                            'cancel_time' => $transaction->cancel_time,
                            'state'       => $transaction->getPaycomState(),
                        ];
                    }

                    throw new PaycomJsonRPCError(self::MSG_FAILED_CREATE_TRANSACTION, -32400);
                }

                throw new PaycomMerchantApiException(self::MSG_CAN_NOT_CANCEL_TRANSACTION, -31007);

            } else {
                return [
                    'transaction' => $transaction->getId(),
                    'cancel_time' => $transaction->cancel_time,
                    'state'       => $transaction->getPaycomState(),
                ];
            }
        }

        throw new PaycomMerchantApiException(self::MSG_TRANSACTION_NOT_FOUND, -31003);
    }


}