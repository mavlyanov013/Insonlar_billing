<?php

use common\components\Config;

$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id'                  => 'app-frontend',
    'name'                => 'Saxovat Qo\'qon',
    'basePath'            => dirname(__DIR__),
    'bootstrap'           => [
        'log',
        'config',
    ],
    'defaultRoute'        => '/',
    'language'            => Config::LANGUAGE_UZBEK,
    'controllerNamespace' => 'frontend\controllers',
    'components'          => [
        'request'      => [
            'csrfParam' => '_csrf-frontend',
        ],
        'user'         => [
            'identityClass'       => 'common\models\User',
            'enableAutoLogin'     => true,
            'absoluteAuthTimeout' => 84600,
            'loginUrl'            => ['account/login'],
            'identityCookie'      => ['name' => '_identity-frontend', 'httpOnly' => true],
        ],
        'session'      => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-frontend',
        ],
        'view'         => [
            'class' => 'frontend\components\View',
        ],
        'log'          => [
            'traceLevel' => YII_DEBUG ? 8 : 3,
            'targets'    => [
                [
                    'class'       => 'yii\log\FileTarget',
                    'levels'      => ['error'],
                    'enabled'     => true,
                    'logVars'     => ['_GET'],
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 20,
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'assetManager' => [
            'linkAssets' => true,
            'appendTimestamp' => true,
            'basePath' => '@webroot/assets',
            'baseUrl' => '@web/assets',
            'bundles' => [
                'yii\bootstrap\BootstrapAsset' => [
                    'sourcePath' => '@frontend/assets/app',
                    'css' => [
                        'vendor/bootstrap/css/bootstrap.min.css',
                    ],
                ],
                'yii\bootstrap\BootstrapPluginAsset' => [
                    'sourcePath' => '@frontend/assets/app',
                    'js' => [
                        'vendor/bootstrap/js/bootstrap.min.js',
                    ],
                ],
            ],
        ],
        /*'assetsAutoCompress' => [
            'class'        => \common\components\AssetCompress::class,
            //'enabled'         => true,
            //'cssFileCompile'  => true,
            //'cssFileCompress' => true,
            //'jsCompress'      => true,
            //'jsFileCompile'   => true,
            //'jsFileCompress'  => true,
            'htmlCompress' => !YII_DEBUG,
        ],*/
        'urlManager'   => [
            'class'                     => 'codemix\localeurls\UrlManager',
            'languages'                 => ['uz' => 'uz-UZ', 'en' => 'en-US', 'ru' => 'ru-RU'],
            'enableLanguageDetection'   => false,
            'enableLanguagePersistence' => true,
            'showScriptName'            => false,
            'enablePrettyUrl'           => true,
            'rules'                     => [
                '/'                      => 'site/index',
                'page/<slug:[a-z0-9-]+>' => 'site/page',

                /*'/donate'       => 'site/donate',
                '/payments-dpf' => 'site/t',

                'account/reset-password'                      => 'account/reset-password',
                'file-storage/upload'                         => 'file-storage/upload',
                'account/request-password-reset'              => 'account/request-password-reset',
                'account/<action:\w+>'                        => 'account/<action>',
                '/image'                                      => 'site/image',
                '/search'                                     => 'category/search',
                '/appeal'                                     => 'site/appeal',
                '/appeal/<number:[0-9]{3}-[0-9]{3}-[0-9]{3}>' => 'site/appeal-view',
                '/add-case'                                   => 'site/appeal',
                '/payment'                                    => 'site/payment',
                '/captcha'                                    => 'site/captcha',
                '/contact'                                    => 'site/contact',
                '/aloqa'                                      => 'site/contact',
                '/yangiliklar'                                => 'post/index',
                '/posts'                                      => 'post/index',
                '/events'                                     => 'event/index',
                '/report/<type:(expenses|payments)>'          => 'site/report',
                '/report'                                     => 'site/report',
                '/education'                                  => 'course/index',
                '/education/<id:[a-z0-9]{24,24}>'             => 'course/view',
                '/projects'                                   => 'project/index',
                '/partners'                                   => 'site/partner',
                '/rahbariyat'                          => 'site/management',
                '/<type:(expenses|payments)>'                 => 'site/expense',
                '/volunteers'                                 => 'volunteer/index',
                'volunteer/<id:[a-z0-9]{24,24}>'              => 'volunteer/view',
                'event/<id:[a-z0-9]{24,24}>'                  => 'event/view',
                'course/<id:[a-z0-9]{24,24}>'                 => 'course/view',
                'project/<id:[a-z0-9]{24,24}>'                => 'project/view',
                'preview/<id:[a-z0-9]{24,24}>'                => 'post/preview',
                'p/<id:[a-z0-9-]+>'                           => 'post/short',
                '/<slug:[a-z0-9-]{3,4}>'                      => 'post/short',
                '<slug:[a-z0-9-]+>'                           => 'category/view',
                'post/<slug:[a-z0-9-]+>'                      => 'post/view',
                'yangiliklar/<slug:[a-z0-9-]+>'               => 'post/view',

                'site/typo'                                   => 'site/typo',
                'category/<category:[a-z0-9-]+>'              => 'post/index',
                '<controller:\w+>/<action:\w+>'               => '<controller>/<action>',
                '<controller:\w+>/<id:\d+>'                   => '<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>'      => '<controller>/<action>',*/
            ],
        ],
    ],
    'params'              => $params,
];
