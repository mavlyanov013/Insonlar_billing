<?php

use yii\helpers\Url;

define('DS', DIRECTORY_SEPARATOR);

Yii::setAlias('common', dirname(__DIR__));
Yii::setAlias('root', dirname(dirname(__DIR__)) . DS);
Yii::setAlias('frontend', dirname(dirname(__DIR__)) . '/frontend');
Yii::setAlias('backend', dirname(dirname(__DIR__)) . '/backend');
Yii::setAlias('console', dirname(dirname(__DIR__)) . '/console');
Yii::setAlias('static', dirname(dirname(__DIR__)) . '/static');
Yii::setAlias('api', dirname(dirname(__DIR__)) . '/api');
Yii::setAlias('backups', dirname(dirname(__DIR__)) . '/system');

Yii::setAlias('@frontendUrl', getenv('FRONTEND_URL'));
Yii::setAlias('@backendUrl', getenv('BACKEND_URL'));
Yii::setAlias('@staticUrl', getenv('STATIC_URL'));

if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
    $_SERVER['HTTPS'] = 'on';

if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ips                    = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $_SERVER['REMOTE_ADDR'] = trim($ips[0]);
} elseif (isset($_SERVER['HTTP_X_REAL_IP']) && !empty($_SERVER['HTTP_X_REAL_IP'])) {
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_REAL_IP'];
} elseif (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP'])) {
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CLIENT_IP'];
}

function __($message, $params = array(), $language = false)
{
    $tags = [
        'br'  => '<br>',
        'b'   => '<b>',
        'bc'  => '</b>',
        'sp'  => '<span>',
        'spc' => '</span>',
    ];

    return Yii::t('app.ui', trim($message), array_merge($tags, $params), $language);
}


function linkTo($params, $schema = false)
{
    return Url::to($params, $schema);
}

function isRussian()
{
    return Yii::$app->language == \common\components\Config::LANGUAGE_RUSSIAN;
}

function isEnglish()
{
    return Yii::$app->language == \common\components\Config::LANGUAGE_ENGLISH;
}

function toDate($date)
{
    $format = 'php:j-F, Y h:i';
    if (isEnglish()) {
        $format = 'php:F j, Y h:i';
    } elseif (isRussian()) {
        $format = 'php:j F, Y h:i';
    }
    return Yii::$app->formatter->asDatetime($date, $format);
}

?>