<?php

namespace common\models\payment\methods\paycom\api;

use common\models\Order;
use common\models\payment\methods\Paycom;
use common\models\payment\methods\paycom\api\method\CancelTransaction;
use common\models\payment\methods\paycom\api\method\ChangePassword;
use common\models\payment\methods\paycom\api\method\CheckPerformTransaction;
use common\models\payment\methods\paycom\api\method\CheckTransaction;
use common\models\payment\methods\paycom\api\method\CreateTransaction;
use common\models\payment\methods\paycom\api\method\GetStatement;
use common\models\payment\methods\paycom\api\method\PerformTransaction;
use common\models\payment\Payment;
use common\models\ShoppingCart;
use common\models\User;
use common\models\UserBalanceFund;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

/**
 * Created by PhpStorm.
 * User: complex
 * Date: 10/28/15
 * Time: 2:57 PM
 */
abstract class PaycomMethod extends Model
{
    const METHOD_CREATE_TRANSACTION        = 'CreateTransaction';
    const METHOD_PERFORM_TRANSACTION       = 'PerformTransaction';
    const METHOD_CHECK_PERFORM_TRANSACTION = 'CheckPerformTransaction';
    const METHOD_CANCEL_TRANSACTION        = 'CancelTransaction';
    const METHOD_CHECK_TRANSACTION         = 'CheckTransaction';
    const METHOD_GET_STATEMENT             = 'GetStatement';
    const METHOD_CHANGE_PASSWORD           = 'ChangePassword';

    const TRANSACTION_STATE_PENDING                  = 1;
    const TRANSACTION_STATE_SUCCESS                  = 2;
    const TRANSACTION_STATE_CANCELLED_BEFORE_PERFORM = -1;
    const TRANSACTION_STATE_CANCELLED_AFTER_PERFORM  = -2;


    const MSG_TRANSACTION_NOT_FOUND         = 'Transaction does not exists';
    const MSG_METHOD_COULD_NOT_BE_PERFORMED = 'This method could not be performed';
    const MSG_FAILED_CREATE_TRANSACTION     = 'Failed to create/update transaction';
    const MSG_SYSTEM_UNEXPECTED_BEHAVIOUR   = 'Unexpected behaviour of system';
    const MSG_METHOD_NOT_FOUND              = 'Method not found';
    const MSG_INVALID_JSON_OBJECT           = 'Invalid JSON object';
    const MSG_INVALID_ACCOUNT_ID            = 'Invalid user information';
    const MSG_INVALID_AMOUNT                = 'Invalid amount';
    const MSG_TRANSACTION_ALREADY_CREATED   = 'Transaction already created for this order';
    const MSG_ACCOUNT_NOT_FOUND             = 'Order not found';
    const MSG_CAN_NOT_CANCEL_TRANSACTION    = 'Transaction could not be cancelled, order has completed';

    const REASON_1  = 1;
    const REASON_2  = 2;
    const REASON_3  = 3;
    const REASON_4  = 4;
    const REASON_5  = 5;
    const REASON_10 = 10;

    /**
     * @var Paycom
     */
    protected $_method;
    protected $_requestId;
    public    $account;
    public    $amount;
    public    $user_data;

    public function init()
    {
        $this->_method = Payment::getMethodInstance(Paycom::METHOD_CODE);
        parent::init();
    }

    public static function getReasonArray()
    {
        return [
            self::REASON_1  => 'Один или несколько получателей не найдены или не активны в Paycom',
            self::REASON_2  => 'Ошибка при выполнении дебетовой операции в процессингом центре',
            self::REASON_3  => 'Ошибка выполнения транзакции',
            self::REASON_4  => 'Отменена по таймауту',
            self::REASON_5  => 'Возврат денег',
            self::REASON_10 => 'Неизвестная ошибка',
        ];
    }

    public static function getReasonError($reason)
    {
        $data = self::getReasonArray();
        return isset($data[$reason]) ? $data[$reason] : $data[self::REASON_10];
    }

    /***
     * @param $data
     * @return array
     * @throws PaycomJsonRPCError
     * @throws PaycomMerchantApiException
     */
    public static function processApiRequest($data)
    {
        switch ($data['method']) {
            case self::METHOD_CHECK_PERFORM_TRANSACTION:
                $method = new CheckPerformTransaction();
                break;
            case self::METHOD_CREATE_TRANSACTION:
                $method = new CreateTransaction();
                break;
            case self::METHOD_PERFORM_TRANSACTION:
                $method = new PerformTransaction();
                break;
            case self::METHOD_CANCEL_TRANSACTION:
                $method = new CancelTransaction();
                break;
            case self::METHOD_CHECK_TRANSACTION:
                $method = new CheckTransaction();
                break;
            case self::METHOD_GET_STATEMENT:
                $method = new GetStatement();
                break;
            case self::METHOD_CHANGE_PASSWORD:
                $method = new ChangePassword();
                break;
            default:
                throw new PaycomJsonRPCError(self::MSG_METHOD_NOT_FOUND, -32601);

        }

        $method->load($data, 'params');
        $method->_requestId = $data['id'];

        if ($method->validate()) {
            return [
                'result' => $method->processMethod(),
                'id'     => $data['id'],
            ];
        }
        throw new PaycomJsonRPCError(self::MSG_INVALID_JSON_OBJECT, -32600);
    }

    abstract protected function processMethod();


    public function afterValidate()
    {
        if ($this->hasErrors()) {
            throw new PaycomJsonRPCError($this->getValidationError(), -32600);
        }
    }


    public function validAccount($attribute, $options)
    {
        $data = $this->account;
        if (isset($data['user_data']) && (PAYCOM_LIVE || $data['user_data'] === 'test_user')) {
            return true;
        }

        throw new PaycomJsonRPCError(self::MSG_INVALID_ACCOUNT_ID, -31050);
    }

    public function validAmount($attribute, $options)
    {
        $amount = $this->amount / 100;
        if ($amount <= $this->_method->getMaxAmount() && $this->_method->getMinAmount() <= $amount) {
            return true;
        }

        throw new PaycomJsonRPCError(self::MSG_INVALID_AMOUNT, -31001);
    }

    protected function getValidationError()
    {
        $errors  = $this->getFirstErrors();
        $message = array_shift($errors);
        return $message ? $message : 'Error in request form';
    }


    /**
     * @param $transactionId string
     * @return bool | null|Payment | ActiveRecord
     */
    public function getTransaction($transactionId)
    {

        if ($transaction = Payment::find()
                                  ->where([
                                              'transaction_id' => $transactionId,
                                              'method'         => $this->_method->getCode(),
                                          ])
                                  ->one()) {
            return $transaction;
        }

        return false;
    }


    public static function getCurrentTimeStamp()
    {
        return round(microtime(true) * 1000);
    }
}
