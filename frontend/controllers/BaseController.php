<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace frontend\controllers;

use common\models\User;
use frontend\components\ContextInterface;
use Yii;
use yii\web\Controller;
use yii\web\IdentityInterface;
use yii\web\Response;

/**
 * Site controller
 */
class BaseController extends Controller implements ContextInterface
{
    public  $layout = 'site';
    private $_user;

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error'   => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * @return User|IdentityInterface|Response
     */
    public function _user()
    {
        if (!Yii::$app->user->isGuest && !$this->_user) {
            $this->_user = Yii::$app->user->identity;
            if ($this->_user == null) {
                Yii::$app->user->logout();

                return $this->goHome();
            }
        }

        return $this->_user;

    }

    public function post($name = null, $default = null)
    {
        return \Yii::$app->request->post($name, $default);
    }

    public function get($name = null, $default = null)
    {
        return \Yii::$app->request->get($name, $default);
    }

    public function addSuccess($message)
    {
        if ($message) {
            Yii::$app->session->addFlash('success', $message);
        }
    }

    public function addError($message)
    {
        if ($message) {
            Yii::$app->session->addFlash('danger', $message);
        }
    }

    public function addInfo($message)
    {
        if ($message) {
            Yii::$app->session->addFlash('info', $message);
        }
    }

    public function addWarning($message)
    {
        if ($message) {
            Yii::$app->session->addFlash('warning', $message);
        }
    }
}
