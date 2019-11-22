<?php

use common\components\Config;
use yii\base\Event;
use yii\web\Controller;
ini_set('max_execution_time', 1800);
Event::on(Controller::className(), Controller::EVENT_BEFORE_ACTION, function ($event) {

    if ($lang = Yii::$app->request->get('lang')) {
        if (isset(Config::$languages[$lang])) {
            $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . (isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : "");
            if ($id = Yii::$app->request->get('id')) {
                $url .= '?id=' . $id;
            }
            Yii::$app->language = $lang;
            Yii::$app->session->set('lang', $lang);
            Yii::$app->response->redirect($url);
            Yii::$app->end();
        }
    } else {
        if ($lang = Yii::$app->session->get('lang')) {
            if (isset(Config::$languages[$lang])) {
                Yii::$app->language = $lang;
            }
        }
    }
});
