<?php
namespace common\models\payment\methods\paycom\api;

use common\components\Config;
use Yii;
use yii\web\IdentityInterface;

class PaycomApiUser implements IdentityInterface
{
    const CONFIG_PAYCOM_API_PASSWORD = 'conf_paycom_api_password';
    const CONFIG_PAYCOM_API_TOKEN    = 'conf_paycom_api_token';

    const API_USER_NAME = 'Paycom';

    protected $password;
    protected $auth_key;

    public function __construct()
    {
        $this->password = Config::get(self::CONFIG_PAYCOM_API_PASSWORD);
        $this->auth_key = Config::get(self::CONFIG_PAYCOM_API_TOKEN);
    }

    public static function getUser($userName, $password)
    {
        $user = self::findIdentity($userName);
        if ($user && $user->validatePassword($password)) {
            return $user;
        }
        return null;
    }

    public static function changeApiPassword($newPassword)
    {
        if (Config::set(self::CONFIG_PAYCOM_API_PASSWORD, Yii::$app->security->generatePasswordHash($newPassword))) {
            Config::set(self::CONFIG_PAYCOM_API_TOKEN, Yii::$app->security->generateRandomString());
            return true;
        }
        return false;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    public static function findIdentity($id)
    {
        if ($id == self::API_USER_NAME) {
            return new PaycomApiUser();
        }
        return null;
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        $user = self::findIdentity(self::API_USER_NAME);
        if ($user && $user->auth_key == $token) {
            return $user;
        }
        return null;
    }


    public function getId()
    {
        return self::API_USER_NAME;
    }


    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }


}