<?php

namespace common\models\payment\methods\click;

use common\models\payment\methods\Click;
use common\models\payment\Payment;
use common\models\User;
use common\models\UserBalanceFund;
use common\models\UserBalanceHistory;
use DateTime;
use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * Created by PhpStorm.
 * User: complex
 * Date: 10/14/15
 * Time: 4:57 PM
 */
class ApiForm extends Model
{
    const ACTION_PREPARE = 0;
    const ACTION_COMPLETE = 1;

    const SCENARIO_PREPARE = 'prepare';
    const SCENARIO_COMPLETE = 'complete';

    public $click_trans_id;
    public $service_id;
    public $click_paydoc_id;

    public $merchant_trans_id;
    public $merchant_prepare_id;
    public $merchant_confirm_id;

    public $amount;
    public $action;
    public $error;
    public $error_note;
    public $sign_time;
    public $sign_string;

    /**
     * @var Click
     */
    private $_method;

    public function init()
    {
        $this->_method = Payment::getMethodInstance(Click::METHOD_CODE);
        parent::init();
    }

    public function rules()
    {
        return [
            [[
                 'click_trans_id',
                 'service_id',
                 'click_paydoc_id',
                 'sign_time',
                 'sign_string',
                 'action',
                 'amount',
             ], 'required', 'on' => [self::SCENARIO_PREPARE, self::SCENARIO_COMPLETE]],

            [['merchant_prepare_id'], 'required', 'when' => function ($model) {
                return $model->action == self::ACTION_COMPLETE;
            }],

            [['merchant_prepare_id', 'action', 'error'], 'number', 'integerOnly' => true],

            [['action'], 'validateAction'],

            [['sign_string'], 'validateSignature'],

            [['error_note', 'merchant_trans_id'], 'safe'],
        ];
    }

    public function validateAction($attribute, $options)
    {
        if ($this->action == self::ACTION_PREPARE &&
            $this->scenario == self::SCENARIO_PREPARE ||
            $this->action == self::ACTION_COMPLETE &&
            $this->scenario == self::SCENARIO_COMPLETE
        ) {
            return true;
        }
        throw new ClickRequestException('Invalid action type', -8);
    }

    public function validateSignature($attribute, $options)
    {
        $fundId = ($this->action == self::ACTION_COMPLETE) ? $this->merchant_prepare_id : "";

        $clickConfig = $this->_method->getServiceData($this->service_id);

        $sign = md5(
            $this->click_trans_id .
            $this->service_id .
            $clickConfig->getSecretKey() .
            $this->merchant_trans_id .
            $fundId .
            $this->amount .
            $this->action .
            $this->sign_time
        );

        if ($sign != $this->sign_string && $this->_method->getConfig('check_signature')) {
            throw new ClickRequestException('Signature check failed!', -1);
        }
        return true;
    }


    public static function processApiRequest($postData)
    {
        $action = isset($postData['action']) ? $postData['action'] : 255;
        switch ($action) {
            case self::ACTION_PREPARE:
                $model = new ApiForm(['scenario' => self::SCENARIO_PREPARE]);
                $model->load(['form' => $postData], 'form');
                return $model->actionPrepare();
                break;
            case self::ACTION_COMPLETE;
                $model = new ApiForm(['scenario' => self::SCENARIO_COMPLETE]);
                $model->load(['form' => $postData], 'form');
                return $model->actionComplete();
                break;
        }

        throw new ClickRequestException('Action not found', -3);
    }


    /**
     * @return array
     * @throws ClickRequestException
     */
    public function actionPrepare()
    {
        if ($this->validate()) {
            $userData = $this->getStoreUser();

            if ($this->error == 0) {
                if ($payment = $this->getPaymentByClickTransId($this->click_trans_id)) {

                    //we have already created transaction for given click_trans_i
                    switch ($payment->status) {
                        case Payment::STATUS_PENDING:
                            return [
                                'click_trans_id'      => $payment->transaction_id,
                                'merchant_trans_id'   => $userData,
                                'merchant_prepare_id' => $payment->transaction_id,
                                'error'               => 0,
                                'error_note'          => 'success',
                            ];
                            break;
                        case Payment::STATUS_SUCCESS:
                            throw new ClickRequestException('Already paid', -4);
                            break;
                        case Payment::STATUS_CANCELLED:
                            throw new ClickRequestException('Transaction cancelled', -9);
                            break;
                        case Payment::STATUS_FAILED:
                            throw new ClickRequestException('Transaction cancelled', -9);
                            break;
                    }

                } else {
                    //OK, we should create invoice for this transaction
                    $payment = new Payment();

                    $payment->status           = Payment::STATUS_PENDING;
                    $payment->time             = $this->getTransactionTime();
                    $payment->user_data        = $userData;
                    $payment->method           = $this->_method->getCode();
                    $payment->create_time      = self::getCurrentTimeStamp();
                    $payment->transaction_id   = $this->click_trans_id;
                    $payment->click_paydoc_id  = $this->click_paydoc_id;
                    $payment->click_service_id = $this->service_id;
                    $payment->amount           = $this->amount;
                    $payment->addAllInformation([
                        'click_trans_id'    => $this->click_trans_id,
                        'click_paydoc_id'   => $this->click_paydoc_id,
                        'click_service_id'  => $this->service_id,
                        'click_sign_time'   => $this->sign_time,
                        'click_sign_string' => $this->sign_string,
                    ]);

                    if ($payment->save()) {
                        return [
                            'click_trans_id'      => $payment->transaction_id,
                            'merchant_trans_id'   => $payment->user_data,
                            'merchant_prepare_id' => $payment->transaction_id,
                            'error'               => 0,
                            'error_note'          => 'success',
                        ];
                    }

                    throw new ClickRequestException('Failed to update user', -7);
                }
            }
        }

        throw new ClickRequestException($this->getValidationError(), -8);
    }

    /**
     * @throws ClickRequestException
     */
    public function actionComplete()
    {
        if ($this->validate()) {
            if ($payment = $this->getPaymentByClickTransId($this->merchant_prepare_id)) {
                if (round($payment->amount) != round($this->amount)) {
                    throw new ClickRequestException('Incorrect amount', -2);
                }

                if ($payment->status == Payment::STATUS_PENDING) {
                    if ($this->error == 0) {
                        $payment->status = Payment::STATUS_SUCCESS;
                        $payment->addAllInformation([
                            'click_paydoc_id'   => $this->click_paydoc_id,
                            'click_sign_time'   => $this->sign_time,
                            'click_sign_string' => $this->sign_string,
                        ]);


                        if ($payment->save()) {
                            return [
                                'click_trans_id'      => $payment->transaction_id,
                                'merchant_trans_id'   => $payment->user_data,
                                'merchant_confirm_id' => $payment->transaction_id,
                                'error'               => 0,
                                'error_note'          => 'success',
                            ];
                        }
                    } else {
                        $payment->status = Payment::STATUS_CANCELLED;
                        $payment->addAllInformation([
                            'click_error'      => $this->error,
                            'click_error_note' => $this->error_note,
                        ]);
                        if ($payment->save()) {
                            throw new ClickRequestException('Transaction cancelled', -9);
                        }
                    }

                    throw new ClickRequestException('Failed to update user', -7);
                } elseif ($payment->status == Payment::STATUS_SUCCESS || $payment->status == Payment::STATUS_FUNDED) {
                    throw new ClickRequestException('Already paid', -4);
                }

                throw new ClickRequestException('Transaction cancelled', -9);
            }

            throw new ClickRequestException('Transaction does not exist', -6);
        }

        throw new ClickRequestException($this->getValidationError(), -8);
    }

    /**
     * @return string
     */
    protected function getStoreUser()
    {
        return iconv('windows-1251', 'utf-8', urldecode($this->merchant_trans_id));
    }

    /**
     * @return string
     */
    protected function getTransactionTime()
    {
        if ($this->sign_time) {
            try {
                $timestamp = DateTime::createFromFormat('Y-m-d H:i:s', $this->sign_time);
                return $timestamp->getTimestamp() * 1000;
            } catch (\Exception $e) {

            }
        }

        return self::getCurrentTimeStamp();
    }

    /**
     * @param $clickTransId int Click Transaction ID
     * @return Payment
     */
    public function getPaymentByClickTransId($clickTransId)
    {
        return Payment::findOne([
            'method'         => Click::METHOD_CODE,
            'transaction_id' => $clickTransId,
        ]);
    }

    protected function getValidationError()
    {
        $errors  = $this->getFirstErrors();
        $message = array_shift($errors);
        return $message ? $message : 'Error in request from click';
    }

    public static function getCurrentTimeStamp()
    {
        return round(microtime(true) * 1000);
    }

}
