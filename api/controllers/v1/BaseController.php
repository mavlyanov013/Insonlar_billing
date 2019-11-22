<?php
namespace api\controllers\v1;

use Yii;
use yii\rest\Controller;

class BaseController extends Controller
{

    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }


    protected function get($param, $default = null)
    {
        return Yii::$app->request->get($param, $default);
    }

    protected function post($param, $default = null)
    {
        return Yii::$app->request->post($param, $default);
    }
}