<?php
/**
 * Created by PhpStorm.
 * User: complex
 * Date: 6/13/15
 * Time: 12:46 PM
 */

namespace frontend\components;


use common\models\Developer;
use common\models\User;
use Yii;
use yii\base\ActionFilter;
use yii\web\ForbiddenHttpException;

class UserAccessControl extends ActionFilter
{
    public $user   = 'user';
    public $except = ['login', 'error', 'logout', 'reset', 'registration', 'confirm'];

    protected function getFullActionName()
    {
        return Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;
    }

    public function beforeAction($action)
    {
        /**
         * @var $user User
         */

        if ($user = Yii::$app->user->identity) {
            //TODO later we may do here RBAC

            /*if (Config::get(Config::CONFIG_DEV_ENABLE_DASHBOARD)) {
            }*/

            return parent::beforeAction($action);
        }

        return $this->denyAccess();

    }

    protected function denyAccess()
    {
        if (Yii::$app->user->getIsGuest()) {
            Yii::$app->user->loginRequired();
        } else {
            throw new ForbiddenHttpException(__('You are not allowed to perform this action.'));
        }

        return false;
    }
}