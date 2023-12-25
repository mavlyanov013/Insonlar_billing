<?php

namespace common\models\payment\methods\paynet;

use common\models\payment\Method;
use common\models\payment\methods\Apelsin;
use common\models\payment\methods\Paynet;
use common\models\payment\methods\paynet\types\CancelTransactionArguments;
use common\models\payment\methods\paynet\types\CancelTransactionResult;
use common\models\payment\methods\paynet\types\ChangePasswordArguments;
use common\models\payment\methods\paynet\types\ChangePasswordResult;
use common\models\payment\methods\paynet\types\CheckTransactionArguments;
use common\models\payment\methods\paynet\types\CheckTransactionResult;
use common\models\payment\methods\paynet\types\GenericArguments;
use common\models\payment\methods\paynet\types\GenericParam;
use common\models\payment\methods\paynet\types\GetInformationArguments;
use common\models\payment\methods\paynet\types\GetInformationResult;
use common\models\payment\methods\paynet\types\GetStatementArguments;
use common\models\payment\methods\paynet\types\GetStatementResult;
use common\models\payment\methods\paynet\types\PerformTransactionArguments;
use common\models\payment\methods\paynet\types\PerformTransactionResult;
use common\models\payment\methods\paynet\types\TransactionStatement;
use common\models\payment\Payment;
use DateTime;
use yii\mongodb\ActiveRecord;

class PaynetMethod
{

    const VERSION_2 = 2;

    const PARAMS_BALANCE_FIELD = "balance";
    const SERVICE_ID           = 1;

    private $allowedIps = [];

    const STATUS_OK                          = 0;
    const STATUS_SERVICE_UNAVAILABLE         = 100;
    const STATUS_SYSTEM_ERROR                = 102;
    const STATUS_UNKNOWN_ERROR               = 103;
    const STATUS_TRANSACTION_ALREADY_CREATED = 201;
    const STATUS_TRANSACTION_NOT_FOUND       = 305;
    const STATUS_TRANSACTION_CANCELED        = 202;
    const STATUS_TRANSACTION_CANNOT_CANCEL   = 77;
    const STATUS_UNKNOWN_USER                = 302;
    const STATUS_INVALID_PASSWORD            = 401;
    const STATUS_MISSING_PARAMETERS          = 411;
    const STATUS_USER_NOT_FOUND              = 412;
    const STATUS_INVALID_AMOUNT              = 413;
    const STATUS_INVALID_DATE                = 414;
    const STATUS_OUTSIDE_THE_SERVICE_ARIA    = 502;
    const STATUS_ACCESS_DENIED               = 601;

    private $status = self::STATUS_UNKNOWN_ERROR;

    private $username;
    private $password;
    private $minAmount;
    private $maxAmount;
    private $version;

    /**
     * @var Paynet
     */
    protected $paymentMethod;

    public function __construct($methodCode, $version = 0)
    {
        /**
         * @var $paymentMethod Paynet | Apelsin
         */
        $paymentMethod       = Payment::getMethodInstance($methodCode);
        $this->allowedIps    = $paymentMethod->getAllowedIps();
        $this->username      = $paymentMethod->getUserName();
        $this->password      = $paymentMethod->getPassword();
        $this->minAmount     = $paymentMethod->getMinAmount();
        $this->maxAmount     = $paymentMethod->getMaxAmount();
        $this->paymentMethod = $paymentMethod;
        $this->version       = $version;
    }

    /**
     * @param $status
     * @return mixed
     */
    protected function getMessage($status)
    {
        $messages = [
            static::STATUS_OK                          => "Ok",
            static::STATUS_SERVICE_UNAVAILABLE         => "Услуга временно не поддерживается",
            static::STATUS_SYSTEM_ERROR                => "Системная ошибка",
            static::STATUS_UNKNOWN_ERROR               => "Неизвестная ошибка",
            static::STATUS_TRANSACTION_ALREADY_CREATED => "Транзакция уже существует",
            static::STATUS_TRANSACTION_NOT_FOUND       => "Транзакция не найден",
            static::STATUS_TRANSACTION_CANCELED        => "Транзакция уже отменена",
            static::STATUS_UNKNOWN_USER                => "Пользователь не найден",
            static::STATUS_MISSING_PARAMETERS          => "Не задан один или несколько обязательных параметров",
            static::STATUS_INVALID_DATE                => "Неверный формат даты и времени",
            static::STATUS_USER_NOT_FOUND              => "Пользователь не найден",
            static::STATUS_INVALID_AMOUNT              => "Неверная сумма",
            static::STATUS_OUTSIDE_THE_SERVICE_ARIA    => "Клиент вне зоны обслуживания провайдера",
            static::STATUS_ACCESS_DENIED               => "Доступ запрещен",
            static::STATUS_TRANSACTION_CANNOT_CANCEL   => "Недостаточно средств на счету клиента для отмены платежа ",
            static::STATUS_INVALID_PASSWORD            => "Пароль должен содержать не менее 7 символов",
        ];

        return $messages[$status];
    }

    /**
     * Service Call: PerformTransaction
     * Parameter options:
     * @param PerformTransactionArguments
     * @return PerformTransactionResult
     */
    public function PerformTransaction($arguments)
    {
        $arguments  = PerformTransactionArguments::create($arguments);
        $result     = new PerformTransactionResult();
        $parameters = [];

        if ($this->validateArguments($arguments)) {
            if ($transactionTime = $this->getTransactionTime($arguments->transactionTime)) {
                if ($transactionId = $this->getTransactionId($arguments->transactionId)) {
                    $result->providerTrnId = $transactionId;

                    if ($amount = $this->getAmount($arguments->amount)) {
                        $transaction = $this->getTransactionByTransId($arguments->transactionId);

                        if ($transaction) {
                            $this->status = self::STATUS_TRANSACTION_ALREADY_CREATED;
                        } else {

                            $transaction = new Payment();

                            $transaction->create_time    = Method::getCurrentTimeStamp();
                            $transaction->time           = $transactionTime->getTimestamp() * 1000;
                            $transaction->status         = Payment::STATUS_SUCCESS;
                            $transaction->method         = $this->paymentMethod->getCode();
                            $transaction->transaction_id = $transactionId;
                            $transaction->amount         = $amount;
                            $transaction->user_data      = $arguments->getUserData();

                            if ($this->version) {
                                $transaction->version = $this->version;
                            }

                            $transaction->addAllInformation([
                                'amount'          => $arguments->amount,
                                'transactionTime' => $arguments->transactionTime,
                            ]);

                            if ($transaction->save()) {
                                $this->status = self::STATUS_OK;
                                $parameter    = new GenericParam();

                                $parameter->paramKey   = self::PARAMS_BALANCE_FIELD;
                                $parameter->paramValue = $amount;

                                $parameters[] = $parameter;
                            } else {
                                $this->status = self::STATUS_SYSTEM_ERROR;
                            }
                        }
                    }
                }
            }
        }

        $result->errorMsg   = $this->getMessage($this->status);
        $result->status     = $this->status;
        $result->timeStamp  = date("c");
        $result->parameters = $parameters;

        return $result;
    }


    /**
     * @param $arguments
     * @return CheckTransactionResult
     */
    public function CheckTransaction($arguments)
    {
        $arguments = CheckTransactionArguments::create($arguments);

        // default values
        $result                              = new CheckTransactionResult();
        $result->providerTrnId               = 0;
        $result->timeStamp                   = date('c');
        $result->transactionState            = 0;
        $result->transactionStateErrorStatus = 1;
        $result->transactionStateErrorMsg    = "Транзакция не существует";

        if ($this->validateArguments($arguments)) {
            if ($transactionId = $this->getTransactionId($arguments->transactionId)) {
                if ($transaction = $this->getTransactionByTransId($transactionId)) {
                    $this->status          = self::STATUS_OK;
                    $result->providerTrnId = $transaction->transaction_id;

                    if ($transaction->status == Payment::STATUS_SUCCESS) {
                        $result->transactionState            = 1;
                        $result->transactionStateErrorStatus = 0;
                        $result->transactionStateErrorMsg    = "Проведено успешно";
                    } else if ($transaction->status == Payment::STATUS_CANCELLED) {
                        $result->transactionState            = 2;
                        $result->transactionStateErrorStatus = 1;
                        $result->transactionStateErrorMsg    = "Транзакция отменена";
                    }
                } else {
                    $this->status = self::STATUS_TRANSACTION_NOT_FOUND;
                }
            }
        }

        $result->errorMsg = $this->getMessage($this->status);
        $result->status   = $this->status;

        return $result;
    }


    /**
     * Service Call: CancelTransaction
     * Parameter options:
     * @param $arguments
     * @return CancelTransactionResult
     */
    public function CancelTransaction($arguments)
    {
        $result                   = new CancelTransactionResult();
        $result->transactionState = 0;

        $arguments = CancelTransactionArguments::create($arguments);

        if ($this->validateArguments($arguments)) {
            if ($transactionId = $this->getTransactionId($arguments->transactionId)) {
                if ($transaction = $this->getTransactionByTransId($transactionId)) {
                    //TODO check if amount refundable
                    if ($transaction->status == Payment::STATUS_SUCCESS) {

                        $transaction->status      = Payment::STATUS_CANCELLED;
                        $transaction->cancel_time = self::getCurrentTimeStamp();
                        $transaction->addAllInformation([$arguments->getParamsAsArray()]);

                        if ($transaction->save()) {
                            $result->transactionState = 2;
                            $this->status             = self::STATUS_OK;
                        } else {
                            $this->status = self::STATUS_SYSTEM_ERROR;
                        }

                    } else if ($transaction->status == Payment::STATUS_CANCELLED) {
                        $result->transactionState = 2;
                        $this->status             = self::STATUS_TRANSACTION_CANCELED;
                    } else {
                        $this->status = self::STATUS_TRANSACTION_CANNOT_CANCEL;
                    }
                } else {
                    $this->status = self::STATUS_TRANSACTION_NOT_FOUND;
                }
            }
        }

        $result->errorMsg  = $this->getMessage($this->status);
        $result->status    = $this->status;
        $result->timeStamp = date("c");

        return $result;
    }

    /**
     * @param $arguments
     * @return GetInformationResult
     */
    public function GetInformation($arguments)
    {

        $arguments  = GetInformationArguments::create($arguments);
        $parameters = [];

        if ($this->validateArguments($arguments)) {
            $this->status = self::STATUS_OK;

            $parameter             = new GenericParam();
            $parameter->paramKey   = $arguments->getUserKey();
            $parameter->paramValue = $arguments->getUserData();
            $parameters[]          = $parameter;

        }

        $result             = new GetInformationResult();
        $result->errorMsg   = $this->getMessage($this->status);
        $result->status     = $this->status;
        $result->timeStamp  = date("c");
        $result->parameters = $parameters;

        return $result;
    }

    /**
     * @param $arguments
     * @return GetStatementResult
     */
    public function GetStatement($arguments)
    {
        /**
         * @var $payment Payment
         */
        $arguments = GetStatementArguments::create($arguments);

        $statements = [];
        if ($this->validateArguments($arguments)) {
            if ($this->validateDateRange($arguments->dateFrom, $arguments->dateTo)) {
                if ($from = $this->getTransactionTime($arguments->dateFrom)) {
                    if ($to = $this->getTransactionTime($arguments->dateTo)) {
                        $this->status = self::STATUS_OK;

                        $payments = Payment::find()
                                           ->where([
                                               'status' => Payment::STATUS_SUCCESS,
                                               'method' => $this->paymentMethod->getCode(),
                                               'time'   => [
                                                   '$gte' => $from->getTimestamp() * 1000,
                                                   '$lte' => $to->getTimestamp() * 1000,
                                               ],
                                           ])
                                           ->addOrderBy(['transaction_id' => SORT_ASC]);

                        if ($this->version) {
                            $payments->andFilterWhere(['version' => $this->version]);
                        }

                        foreach ($payments->all() as $payment) {
                            $statement                = new TransactionStatement();
                            $statement->amount        = $payment->getAmountCents();
                            $statement->providerTrnId = $payment->transaction_id;
                            $statement->transactionId = $payment->transaction_id;
                            if ($this->paymentMethod->getCode() == Paynet::METHOD_CODE) {
                                $statement->transactionTime = date('c', $payment->time / 1000);
                            } else {
                                $statement->transactionTime = $payment->getInfo('transactionTime');
                            }
                            $statements[] = $statement;
                        }
                    }
                }
            }
        }

        $result             = new GetStatementResult();
        $result->errorMsg   = $this->getMessage($this->status);
        $result->status     = $this->status;
        $result->timeStamp  = date("c");
        $result->statements = $statements;

        return $result;
    }

    /**
     * @param  $arguments
     * @return ChangePasswordResult
     */
    public function ChangePassword($arguments)
    {
        $arguments = ChangePasswordArguments::create($arguments);

        if ($this->validateArguments($arguments, false)) {
            if ($newPassword = $this->getPassword($arguments->newPassword)) {
                if ($this->paymentMethod->setPassword($newPassword)) {
                    $this->status = self::STATUS_OK;
                }
            }
        }

        $result            = new ChangePasswordResult();
        $result->errorMsg  = $this->getMessage($this->status);
        $result->status    = $this->status;
        $result->timeStamp = date("c");

        return $result;
    }


    /**
     * @param GenericArguments $arguments
     * @return bool
     */
    protected function validateArguments(GenericArguments $arguments, $service = true)
    {
        if (!$this->isAllowedIp()) {
            $this->status = static::STATUS_ACCESS_DENIED;
        } elseif (!$this->paymentMethod->validateUser($arguments->username, $arguments->password)) {
            $this->status = self::STATUS_ACCESS_DENIED;
        } elseif ($service && $arguments->serviceId != self::SERVICE_ID) {
            $this->status = self::STATUS_OUTSIDE_THE_SERVICE_ARIA;
        } else {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function isAllowedIp()
    {
        if (empty($this->allowedIps)) {
            return true;
        }

        if (!$this->paymentMethod->isEnabled()) {
            return false;
        }

        if (!$this->paymentMethod->isLiveMode()) {
            return true;
        }

        $clientIp = $this->getRealClientIp();

        foreach ($this->allowedIps as $allowedIp) {
            if ($this->ip_in_range($clientIp, $allowedIp)) {
                return true;
            }
        }
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

    /**
     * @param $amount
     * @return float|int
     */
    protected function getAmount($amount)
    {
        $amount = intval($amount);
        if ($amount && $amount > 0) {
            return $amount / 100;
        }

        $this->status = self::STATUS_INVALID_AMOUNT;
        return false;
    }


    /**
     * @param $transactionId string
     * @return bool | null|Payment | ActiveRecord
     */
    protected function getTransactionByTransId($transactionId)
    {

        if ($transaction = Payment::find()
                                  ->where([
                                      'transaction_id' => $transactionId,
                                      'method'         => $this->paymentMethod->getCode(),
                                  ])
                                  ->one()) {
            return $transaction;
        }

        return false;
    }


    protected function getTransactionId($transactionId)
    {
        if ($transactionId && is_numeric($transactionId) && $transactionId > 0) {
            return $transactionId;
        }

        $this->status = self::STATUS_MISSING_PARAMETERS;
        return false;
    }


    protected function getPassword($password)
    {
        if ($password && mb_strlen($password) > 6) {
            return $password;
        }

        $this->status = self::STATUS_INVALID_PASSWORD;
        return false;
    }

    /**
     * @param $transactionTime
     * @return bool|DateTime
     */
    protected function getTransactionTime($transactionTime)
    {
        if (strlen($transactionTime) > 32) {
            $date = explode('+', $transactionTime);
            if (isset($date[0]) && isset($date[1])) {
                $transactionTime = substr($date[0], 0, 26) . '+' . $date[1];
            }
        }

        if ($transactionTime && $this->validateDate($transactionTime)) {
            $timestamp = DateTime::createFromFormat('Y-m-d\TH:i:s.uP', $transactionTime);

            return $timestamp;
        }

        $this->status = self::STATUS_INVALID_DATE;
        return false;
    }


    protected function validateDateRange($dateFrom, $dateTo)
    {
        if ($dateFrom && $dateTo) {
            if (strlen($dateFrom) > 32) {
                $date = explode('+', $dateFrom);
                if (isset($date[0]) && isset($date[1])) {
                    $dateFrom = substr($date[0], 0, 26) . '+' . $date[1];
                }
            }
            if (strlen($dateTo) > 32) {
                $date = explode('+', $dateTo);
                if (isset($date[0]) && isset($date[1])) {
                    $dateTo = substr($date[0], 0, 26) . '+' . $date[1];
                }
            }

            if ($this->validateDate($dateFrom) && $this->validateDate($dateTo)) {
                return true;
            }
            $this->status = self::STATUS_INVALID_DATE;
        } else {
            $this->status = self::STATUS_MISSING_PARAMETERS;
        }

        return false;
    }

    protected function validateDate($date, $format = 'Y-m-d\TH:i:s.uP')
    {
        $d = DateTime::createFromFormat($format, $date);
        return ($d && $d->format($format) !== false);
    }

    protected function validateDateFull($date, $format = 'Y-m-d\TH:i:s.uP')
    {
        $d = DateTime::createFromFormat($format, $date);
        return ($d && $d->format($format) !== false);
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


    public static function getCurrentTimeStamp()
    {
        return round(microtime(true) * 1000);
    }
}