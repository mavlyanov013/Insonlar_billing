<?php

use common\components\Config;
use kartik\mpdf\Pdf;

$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id'                  => 'backend',
    'name'                => 'Saxovat',
    'basePath'            => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'defaultRoute'        => 'payment/index',
    'language'            => Config::LANGUAGE_DEFAULT,
    'bootstrap'           => ['log', 'config'],
    'components'          => [
        'request'      => [
            'enableCsrfValidation' => false,
        ],
        'urlManager'   => [
            'class'                     => 'codemix\localeurls\UrlManager',
            'languages'                 => ['uz' => 'uz-UZ'],
            'enableLanguageDetection'   => false,
            'enableLanguagePersistence' => false,
            'showScriptName'            => false,
            'enablePrettyUrl'           => true,
            'rules'                     => [
                '<controller:\w+>/<id:[a-z0-9]{24,24}>'                                   => '<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:[a-z0-9]{24,24}>'                      => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>'                                           => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>/<type:[a-z]{3,16}>'                        => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>/<id:[a-z0-9]{24,24}>/<social:[a-z]{3,16}>' => '<controller>/<action>',
            ],
        ],
        'user'         => [
            'identityClass'   => 'common\models\Admin',
            'enableAutoLogin' => true,
            'loginUrl'        => '/dashboard/login',
            'identityCookie'  => [
                'name' => '_backendUser',
            ],
        ],
        'view'         => [
            'class' => 'backend\components\View',
        ],
        'log'          => [
            'traceLevel' => YII_DEBUG ? 3 : 3,
            'targets'    => [
                [
                    'class'  => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'dashboard/error',
        ],
        'assetManager' => array(
            'linkAssets'      => true,
            'appendTimestamp' => true,
            'bundles'         => [
                'yii\web\JqueryAsset' => [
                    'sourcePath' => '@app/assets/backend',
                    'js'         => [
                        'js/jquery.min.js',
                    ],
                ],
            ],
        ),
        'pdf'          => [
            'class'       => Pdf::classname(),
            'format'      => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_PORTRAIT,
            'destination' => Pdf::DEST_DOWNLOAD,
        ],
    ],
    'params'              => $params,
];
