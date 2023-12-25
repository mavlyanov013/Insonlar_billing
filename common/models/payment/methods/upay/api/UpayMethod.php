<?php

namespace common\models\payment\methods\upay\api;

use common\models\payment\methods\Upay;
use common\models\payment\Payment;
use DateTime;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

/**
 * Created by PhpStorm.
 * User: complex
 * Date: 10/28/15
 * Time: 2:57 PM
 */
class UpayMethod extends Model
{

    /**
     * @var Upay
     */
    protected $_method;
    protected $_requestId;

    public $accessToken;
    public $personalAccount;
    public $upayPaymentAmount;
    public $upayTransId;
    public $upayTransTime;


    public function rules()
    {
        return [
            [['personalAccount', 'upayTransId', 'upayTransTime', 'upayPaymentAmount'], 'required'],
            [['upayPaymentAmount'], 'number', 'integerOnly' => false, 'min' => 500],
            [['accessToken'], 'validSignature'],
            [['accessToken'], 'isAllowedIp'],
        ];
    }

    public function init()
    {
        $this->_method = Payment::getMethodInstance(Upay::METHOD_CODE);
        parent::init();
    }

    public function validSignature($attribute)
    {
        //accessToken = md {upayTransId + upayPaymentAmount + upayPaymentAmount + serviceId}
        $sign = md5(
            $this->upayTransId .
            $this->upayPaymentAmount .
            $this->upayTransTime .
            $this->_method->getServiceId()
        );

        if ($sign != $this->accessToken) {
            $this->addError($attribute, __('Invalid signature'));
        }
    }

    /***
     * @param $data
     * @return array
     * @throws UpayError
     */
    public static function processApiRequest($data)
    {
        $method = new self($data);

        if ($method->validate()) {
            if ($oldTransaction = $method->getTransaction($method->upayTransId)) {
                return [
                    'success' => 1,
                    'message' => __('Transaction with this id already registered on our billing'),
                ];
            } else {
                $transaction = new Payment();

                $transaction->create_time    = $method->getCurrentTimeStamp();
                $transaction->time           = $method->getTransactionDate();
                $transaction->status         = Payment::STATUS_SUCCESS;
                $transaction->method         = $method->_method->getCode();
                $transaction->transaction_id = $method->upayTransId;
                $transaction->amount         = intval($method->upayPaymentAmount);
                $transaction->user_data      = $method->personalAccount;

                $transaction->addAllInformation([
                                                    'upayPaymentAmount' => $method->upayPaymentAmount,
                                                    'accessToken'       => $method->accessToken,
                                                ]);

                if ($transaction->save()) {
                    return [
                        'status'  => 1,
                        'message' => __('Payment registered successfully'),
                    ];
                } else {
                }

                $errors = $transaction->getFirstErrors();

                throw new UpayError(array_pop($errors), -2);
            }
        }

        if ($method->hasErrors('upayPaymentAmount')) {
            throw new UpayError($method->getFirstError('upayPaymentAmount'), -1);
        }

        throw new UpayError($method->getValidationError(), -2);
    }

    public function getTransactionDate()
    {
        if ($this->upayTransTime) {
            try {
                $timestamp = DateTime::createFromFormat('Y-m-d H:i:s', $this->upayTransTime);
                return $timestamp->getTimestamp() * 1000;
            } catch (\Exception $e) {

            }
        }

        return self::getCurrentTimeStamp();
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


    /**
     * @return bool
     */
    public function isAllowedIp($attirbute, $options = [])
    {
        $ips      = $this->_method->getAllowedIps();
        $clientIp = $this->getRealClientIp();

        if (empty($ips)) {
            return true;
        }

        if (!$this->_method->isEnabled()) {
            return false;
        }

        if (!$this->_method->isLiveMode()) {
            return true;
        }


        foreach ($ips as $allowedIp) {
            if ($this->ip_in_range($clientIp, $allowedIp)) {
                return true;
            }
        }

        $this->addError($attirbute, __('{ip} is not allowed', ['ip' => $clientIp]));
        return false;
    }


    protected function ip_in_range($ip, $range)
    {
        if (strpos($range, '/') == false) {
            $range .= '/32';
        }
        // $range is in IP/CIDR format eg 127.0.0.1/24
        list($range, $netmask) = explode('/', $range, 2);
        $range_decimal    = ip2long($range);
        $ip_decimal       = ip2long($ip);
        $wildcard_decimal = pow(2, (32 - $netmask)) - 1;
        $netmask_decimal  = ~$wildcard_decimal;
        return (($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal));
    }

    protected function getRealClientIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }
}
