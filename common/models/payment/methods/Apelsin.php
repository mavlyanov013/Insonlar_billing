<?php

namespace common\models\payment\methods;

use common\components\Config;
use common\models\payment\Method;
use common\models\payment\Payment;
use Yii;
use yii\base\Model;

class Apelsin extends Method
{

    const CONFIG_ALLOWED_IPS = 'allowedIps';
    const CONFIG_USER_NAME   = 'apelsinUserName';
    const CONFIG_PASSWORD    = 'apelsinPassword';
    const CONFIG_MIN_AMOUNT  = 'minAmount';
    const CONFIG_MAX_AMOUNT  = 'maxAmount';

    const METHOD_CODE = 'apelsin';

    public $name        = 'Apelsin';
    public $description = 'Users can pay using Apelsin payment system';

    public function getAllowedIps()
    {
        return $this->getConfig(self::CONFIG_ALLOWED_IPS);
    }

    public function getMinAmount()
    {
        return $this->getConfig(self::CONFIG_MIN_AMOUNT);
    }

    public function getMaxAmount()
    {
        return $this->getConfig(self::CONFIG_MAX_AMOUNT);
    }

    public function getUserName()
    {
        return Config::get(self::CONFIG_USER_NAME);
    }

    public function getPassword()
    {
        return Config::get(self::CONFIG_PASSWORD);
    }

    public function setPassword($password)
    {
        return Config::set(self::CONFIG_PASSWORD, Yii::$app->security->generatePasswordHash($password));
    }

    public function setUser($userName, $password = false)
    {
        Config::set(self::CONFIG_USER_NAME, $userName);

        if ($password)
            return $this->setPassword($password);

        return true;
    }

    public function validateUser($userName, $password)
    {
        return $userName == $this->getUserName() && Yii::$app->security->validatePassword($password, $this->getPassword());
    }

    public function getAdditionalInformation(Model $payment)
    {
        // TODO: Implement getAdditionalInformation() method.
    }
}