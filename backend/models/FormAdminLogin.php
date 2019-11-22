<?php

namespace backend\models;

use common\models\Admin;
use common\models\Login;
use himiklab\yii2\recaptcha\ReCaptchaValidator;
use Yii;
use yii\base\Model;

/**
 * Login form
 */
class FormAdminLogin extends Model
{
    public  $login;
    public  $password;
    public  $rememberMe = false;
    public  $reCaptcha;
    private $_user      = false;

    public function rules()
    {
        return [
            [['login', 'password'], 'required'],
            ['password', 'validatePassword'],
            [['reCaptcha'], YII_DEBUG ? 'safe' : ReCaptchaValidator::className()],
        ];
    }

    public function attributeLabels()
    {
        return [
            'login'    => __('Login'),
            'password' => __('Password'),
        ];
    }


    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, __('Invalid Login or Password'));
            }
        }
    }


    public function login()
    {
        $login = new Login(
            [
                'ip'     => Yii::$app->request->getUserIP(),
                'login'  => $this->login,
                'status' => Login::STATUS_FAIL,
                'type'   => Login::TYPE_ADMIN,
            ]
        );

        $result = false;

        if ($this->validate()) {
            $login->status = Login::STATUS_SUCCESS;
            $result        = Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 3600 * 2);
        }

        $login->save();

        return $result;
    }


    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = Admin::findByLogin($this->login);
        }

        return $this->_user;
    }
}
