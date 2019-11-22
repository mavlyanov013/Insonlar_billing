<?php
namespace common\models\payment\methods\paycom\api\method;

use common\models\payment\methods\paycom\api\PaycomMerchantApiException;
use common\models\payment\methods\paycom\api\PaycomMethod;
use Yii;

class CheckTransaction extends PaycomMethod
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
     * @throws PaycomMerchantApiException
     * @throws \yii\base\ErrorException
     */
    protected function processMethod()
    {
        if ($transaction = $this->getTransaction($this->id)) {
            return [
                'create_time'  => $transaction->create_time,
                'perform_time' => $transaction->perform_time,
                'cancel_time'  => $transaction->cancel_time,
                'transaction'  => $transaction->getId(),
                'state'        => $transaction->getPaycomState(),
                'reason'       => $transaction->getInfo('reason'),
            ];
        }

        throw new PaycomMerchantApiException(self::MSG_TRANSACTION_NOT_FOUND, -31003);
    }

}