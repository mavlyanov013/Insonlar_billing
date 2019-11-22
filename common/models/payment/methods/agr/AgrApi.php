<?php

namespace common\models\payment\methods\agr;

use common\models\Order;
use common\models\payment\methods\Agr;
use common\models\payment\Payment;
use common\models\UserBalanceFund;
use common\models\UserBalanceHistory;
use yii\base\Exception;
use yii\base\Model;


class AgrApi extends Model
{
    const ACTION_INFO      = 0;
    const ACTION_PAY       = 1;
    const ACTION_CANCEL    = 2;
    const ACTION_NOTIFY    = 3;
    const ACTION_STATEMENT = 4;

    const SCENARIO_INFO      = 'info';
    const SCENARIO_PAY       = 'pay';
    const SCENARIO_NOTIFY    = 'notify';
    const SCENARIO_CANCEL    = 'cancel';
    const SCENARIO_STATEMENT = 'statement';

    const STATUS_PAYED        = 2;
    const STATUS_CANCELLED    = 3;
    const ENVIRONMENT_LIVE    = 'live';
    const ENVIRONMENT_SANDBOX = 'sandbox';

    public $ACTION;

    public $VENDOR_ID;
    public $PAYMENT_ID;
    public $PAYMENT_NAME;
    public $AGR_TRANS_ID;
    public $MERCHANT_TRANS_ID;
    public $MERCHANT_TRANS_AMOUNT;
    public $VENDOR_TRANS_ID;
    public $STATUS;

    public $ENVIRONMENT;
    public $MERCHANT_TRANS_DATA;
    public $ERROR;
    public $ERROR_NOTE;
    public $SIGN_TIME;
    public $SIGN_STRING;
    public $FROM;
    public $TO;


    /**
     * @var Agr
     */
    private $_method;

    public function init()
    {
        $this->_method = Payment::getMethodInstance(Agr::METHOD_CODE);
        parent::init();
    }

    public function rules()
    {
        return [
            [[
                 'MERCHANT_TRANS_ID',
                 'SIGN_TIME',
                 'SIGN_STRING',
             ], 'required', 'on' => [self::SCENARIO_INFO]],

            [[
                 'AGR_TRANS_ID',
                 'VENDOR_ID',
                 'PAYMENT_ID',
                 'PAYMENT_NAME',
                 'MERCHANT_TRANS_ID',
                 'MERCHANT_TRANS_AMOUNT',
                 'ENVIRONMENT',
                 'SIGN_TIME',
                 'SIGN_STRING',
             ], 'required', 'on' => [self::SCENARIO_PAY]],

            [[
                 'AGR_TRANS_ID',
                 'VENDOR_TRANS_ID',
                 'STATUS',
                 'SIGN_TIME',
                 'SIGN_STRING',
             ], 'required', 'on' => [self::SCENARIO_NOTIFY]],

            [[
                 'AGR_TRANS_ID',
                 'VENDOR_TRANS_ID',
                 'SIGN_TIME',
                 'SIGN_STRING',
             ], 'required', 'on' => [self::SCENARIO_CANCEL]],

            [[
                 'FROM',
                 'TO',
                 'SIGN_TIME',
                 'SIGN_STRING',
             ], 'required', 'on' => [self::SCENARIO_STATEMENT]],

            [['SIGN_TIME'], 'number', 'integerOnly' => true],

            [['ENVIRONMENT'], 'in', 'range' => ['live', 'sandbox']],
            [['STATUS'], 'in', 'range' => [self::STATUS_PAYED, self::STATUS_CANCELLED]],

            [['MERCHANT_TRANS_DATA'], 'safe'],
            [['PAYMENT_NAME'], 'safe'],
            [['PAYMENT_ID'], 'safe'],
            [['VENDOR_ID'], 'safe'],

            [['MERCHANT_TRANS_ID'], 'validateMerchantTrans'],

            [[
                 'PAYMENT_ID',
                 'VENDOR_TRANS_ID',
                 'MERCHANT_TRANS_AMOUNT',
                 'SIGN_TIME',
                 'ERROR',
             ], 'number', 'integerOnly' => true],

            [[
                 'PAYMENT_NAME',
                 'ERROR_NOTE',
                 'MERCHANT_TRANS_DATA',
                 'ENVIRONMENT',
             ], 'safe'],
        ];
    }

    public function afterValidate()
    {
        if (!$this->hasErrors())
            $this->validateSignature();

        parent::afterValidate();
    }

    public function validateMerchantTrans()
    {
        if ($this->MERCHANT_TRANS_ID == "-1") {
            throw new AgrRequestException('Order does not exists', -5);
        }
    }

    public function validateSignature()
    {
        $sign = time();
        if ($this->ACTION == self::ACTION_INFO) {
            $sign = md5(
                $this->_method->getSecretKey() .
                $this->MERCHANT_TRANS_ID .
                $this->SIGN_TIME
            );
        } elseif ($this->ACTION == self::ACTION_PAY) {
            $sign = md5(
                $this->_method->getSecretKey() .
                $this->AGR_TRANS_ID .
                $this->VENDOR_ID .
                $this->PAYMENT_ID .
                $this->PAYMENT_NAME .
                $this->MERCHANT_TRANS_ID .
                $this->MERCHANT_TRANS_AMOUNT .
                $this->ENVIRONMENT .
                $this->SIGN_TIME
            );
        } elseif ($this->ACTION == self::ACTION_NOTIFY) {
            $sign = md5(
                $this->_method->getSecretKey() .
                $this->AGR_TRANS_ID .
                $this->VENDOR_TRANS_ID .
                $this->STATUS .
                $this->SIGN_TIME
            );
        } elseif ($this->ACTION == self::ACTION_CANCEL) {
            $sign = md5(
                $this->_method->getSecretKey() .
                $this->AGR_TRANS_ID .
                $this->VENDOR_TRANS_ID .
                $this->SIGN_TIME
            );
        } elseif ($this->ACTION == self::ACTION_STATEMENT) {
            $sign = md5(
                $this->_method->getSecretKey() .
                $this->FROM .
                $this->TO .
                $this->SIGN_TIME
            );
        }


        if ($sign != $this->SIGN_STRING) {
            throw new AgrRequestException('SIGN CHECK FAILED!', -1);
        }
        return true;
    }


    public static function processApiRequest($postData, $action)
    {
        switch ($action) {
            case self::ACTION_INFO:
                $model = new AgrApi(['scenario' => self::SCENARIO_INFO]);
                $model->load(['form' => $postData], 'form');
                $model->ACTION = $action;

                return $model->actionInfo();
            case self::ACTION_PAY:
                $model = new AgrApi(['scenario' => self::SCENARIO_PAY]);
                $model->load(['form' => $postData], 'form');
                $model->ACTION = $action;

                return $model->actionPay();
            case self::ACTION_NOTIFY:
                $model = new AgrApi(['scenario' => self::SCENARIO_NOTIFY]);
                $model->load(['form' => $postData], 'form');
                $model->ACTION = $action;

                return $model->actionNotify();
                break;

            case self::ACTION_CANCEL:
                $model = new AgrApi(['scenario' => self::SCENARIO_CANCEL]);
                $model->load(['form' => $postData], 'form');
                $model->ACTION = $action;

                return $model->actionCancel();
                break;

            case self::ACTION_STATEMENT:
                $model = new AgrApi(['scenario' => self::SCENARIO_STATEMENT]);
                $model->load(['form' => $postData], 'form');
                $model->ACTION = $action;

                return $model->actionStatement();
                break;

            default:
                throw new AgrRequestException('Action not found', -3);
        }
    }

    public function actionInfo()
    {

        if ($this->validate()) {
            return [
                'ERROR'      => 0,
                'ERROR_NOTE' => 'Success',
                'PARAMETERS' => [
                    'name' => $this->MERCHANT_TRANS_ID,
                ],
            ];
        }

        throw new AgrRequestException('Not enough parameters', -3);
    }

    public function actionPay()
    {
        if ($this->validate()) {
            if ($payment = $this->getPaymentTransId($this->AGR_TRANS_ID)) {
                //we have already created transaction for given AGR_TRANS_ID
                switch ($payment->status) {
                    case Payment::STATUS_PENDING:
                        return [
                            'VENDOR_TRANS_ID' => $payment->transaction_id,
                            'ERROR'           => 0,
                            'ERROR_NOTE'      => 'Success',
                        ];
                        break;
                    case Payment::STATUS_SUCCESS:
                        throw new AgrRequestException('Already paid', -4);
                        break;
                    case Payment::STATUS_CANCELLED:
                        throw new AgrRequestException('Transaction cancelled', -9);
                        break;
                    case Payment::STATUS_FAILED:
                        throw new AgrRequestException('Transaction cancelled', -9);
                        break;
                }

            } else {

                $payment = new Payment(['scenario' => Payment::SCENARIO_INSERT]);

                $payment->status         = Payment::STATUS_PENDING;
                $payment->time           = intval($this->SIGN_TIME);
                $payment->create_time    = self::getCurrentTimeStamp();
                $payment->amount         = $this->MERCHANT_TRANS_AMOUNT;
                $payment->method         = $this->_method->getCode();
                $payment->transaction_id = $this->AGR_TRANS_ID;
                $payment->agr_method     = $this->PAYMENT_NAME;
                $payment->user_data      = $this->MERCHANT_TRANS_ID;
                $payment->environment    = $this->ENVIRONMENT;
                $payment->live_mode      = $this->ENVIRONMENT == self::ENVIRONMENT_LIVE;

                $payment->addAllInformation([
                    'PAYMENT_ID'   => $this->PAYMENT_ID,
                    'PAYMENT_NAME' => $this->PAYMENT_NAME,
                    'AGR_TRANS_ID' => $this->AGR_TRANS_ID,
                    'SIGN_TIME'    => $this->SIGN_TIME,
                    'SIGN_STRING'  => $this->SIGN_STRING,
                    'ENVIRONMENT'  => $this->ENVIRONMENT,
                    'ERROR_NOTE'   => $this->ERROR_NOTE,
                ]);

                if ($payment->save()) {
                    return [
                        'VENDOR_TRANS_ID' => $payment->transaction_id,
                        'ERROR'           => 0,
                        'ERROR_NOTE'      => 'Success',
                    ];
                } elseif ($payment->hasErrors('amount')) {
                    throw new AgrRequestException('Incorrect parameter amount', -2);
                }

                throw new AgrRequestException('Failed to update user', -7);
            }
        }

        throw new AgrRequestException('Not enough parameters', -3);
    }

    public function actionNotify()
    {
        if ($this->validate()) {
            if ($payment = $this->getPaymentTransId($this->VENDOR_TRANS_ID)) {

                if ($this->STATUS == self::STATUS_PAYED) {
                    $payment->status       = Payment::STATUS_SUCCESS;
                    $payment->perform_time = self::getCurrentTimeStamp();

                    if ($payment->save()) {
                        return [
                            'ERROR'      => 0,
                            'ERROR_NOTE' => 'Success',
                        ];
                    }

                    throw new AgrRequestException('Failed to update user', -7);

                } else if ($this->STATUS == self::STATUS_CANCELLED) {
                    $payment->status      = Payment::STATUS_CANCELLED;
                    $payment->cancel_time = self::getCurrentTimeStamp();

                    if ($payment->save()) {
                        return [
                            'ERROR'      => 0,
                            'ERROR_NOTE' => 'Success',
                        ];
                    }

                    throw new AgrRequestException('Failed to update user', -7);
                }
                throw new AgrRequestException('Notify status not equals to 2 or 3', -8);
            }
            throw new AgrRequestException('The transaction does not exist', -6);
        }

        throw new AgrRequestException('Not enough parameters', -3);
    }

    public function actionCancel()
    {
        if ($this->validate()) {
            if ($payment = $this->getPaymentTransId($this->VENDOR_TRANS_ID)) {

                //TODO check if payment amount not spent for outgoings
                $canBeCancelled = true;
                if ($canBeCancelled && $payment->status == Payment::STATUS_SUCCESS) {
                    return [
                        'ERROR'      => 0,
                        'ERROR_NOTE' => 'Success',
                    ];
                }

                throw new AgrRequestException('Transaction cancelled', -9);
            }

            throw new AgrRequestException('The transaction does not exist', -6);
        }

        //throw new AgrRequestException($this->getValidationError(), -3);
        throw new AgrRequestException('Not enough parameters', -3);
    }

    public function actionStatement()
    {
        /**
         * @var $transaction Payment
         */
        if ($this->validate()) {
            $data   = Payment::find()
                             ->andWhere(['$eq', 'method', $this->_method->getCode()])
                             ->andWhere(['$gte', 'time', $this->FROM])
                             ->andWhere(['$lte', 'time', $this->TO])
                             ->orderBy(['time' => SORT_ASC])
                             ->all();
            $result = [];

            foreach ($data as $transaction) {
                $result[] = [
                    "ENVIRONMENT"           => $transaction->environment,
                    "AGR_TRANS_ID"          => $transaction->transaction_id,
                    "VENDOR_TRANS_ID"       => $transaction->transaction_id,
                    "MERCHANT_TRANS_ID"     => $transaction->user_data,
                    "MERCHANT_TRANS_AMOUNT" => $transaction->amount,
                    "STATE"                 => $transaction->status == Payment::STATUS_SUCCESS ? 2 : 3,
                    "DATE"                  => $transaction->time,
                ];
            }

            return [
                'ERROR'        => 0,
                'ERROR_NOTE'   => 'Success',
                'TRANSACTIONS' => $result,
            ];
        }

        //throw new AgrRequestException($this->getValidationError(), -3);
        throw new AgrRequestException('Not enough parameters', -3);
    }


    /**
     * @param $transactionId
     * @return Payment
     */
    public function getPaymentTransId($transactionId)
    {
        return Payment::findOne([
            'method'         => Agr::METHOD_CODE,
            'transaction_id' => $transactionId,
        ]);
    }

    protected function getValidationError()
    {
        $errors  = $this->getFirstErrors();
        $message = array_shift($errors);
        return $message ? $message : 'Error in request from AGR';
    }


    public static function getCurrentTimeStamp()
    {
        return round(microtime(true) * 1000);
    }

}

class AgrRequestException extends Exception
{

}