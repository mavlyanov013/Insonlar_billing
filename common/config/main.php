<?php

use common\components\Config;

$params = array_merge(
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);
$config = [
    'id'         => 'advanced',
    'basePath'   => dirname(__DIR__),
    'timeZone'   => 'Asia/Tashkent',
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'bootstrap'  => ['log'],
    'language'            => Config::LANGUAGE_DEFAULT,
    'components' => [

        'i18n'   => [
            'translations' => [
                'app*' => [
                    'class'                 => 'common\components\MongoMessageSource',
                    'forceTranslation'      => true,
                    'enableCaching'         => true,
                    'cachingDuration'       => 3600,
                    'sourceLanguage'        => 'en-US',
                    'collection'            => '_system_message',
                    'on missingTranslation' => [
                        'common\components\MongoMessageSource',
                        'handleMissingTranslation',
                    ],
                ],
                'yii'  => [
                    'class'                 => 'common\components\MongoMessageSource',
                    'forceTranslation'      => true,
                    'enableCaching'         => true,
                    'cachingDuration'       => 3600,
                    'sourceLanguage'        => 'en-US',
                    'collection'            => '_system_message',
                    'on missingTranslation' => [
                        'common\components\MongoMessageSource',
                        'handleMissingTranslation',
                    ],
                ],
            ],
        ],
        'mailer' => [
            'class'            => 'yii\swiftmailer\Mailer',
            'viewPath'         => '@common/mail',
            'transport'        => [
                'class'      => 'Swift_SmtpTransport',
                'host'       => getenv('SMTP_HOST'),
                'username'   => getenv('EMAIL_LOGIN'),
                'password'   => getenv('EMAIL_PASSWORD'),
                'port'       => getenv('SMTP_PORT'),
                'encryption' => getenv('SMTP_ENC'),
            ],
            'useFileTransport' => YII_DEBUG,
        ],

        'cache'        => [
            'class'    => 'yii\caching\FileCache',
            'fileMode' => 0777,
            'dirMode'  => 0777,
        ],
        'assetManager' => array(
            'linkAssets'      => true,
            'appendTimestamp' => true,
        ),
        'config'       => [
            'class' => 'common\components\Config',
        ],
        'fcm'          => [
            'class'  => 'understeam\fcm\Client',
            'apiKey' => 'AAAAuAqpsSs:APA91bHcNF1nWtay23rt1fQ8dwz1egjNl_OrKbm44kSPDim_aSX5VdqTkmho3nzAJ8oSZpV6cJkXBZhZdfOHMV4j8E6ef0kMdwjybFh9FfN11wIG8o5XAYv1qChMqv4vZ-ieKKHry6kl',
        ],
        'formatter'    => [
            'class'             => 'common\components\Formatter',
            'currencyCode'      => 'UZS',
            'datetimeFormat'    => 'dd/MM/yyyy HH:mm:ss',
            'decimalSeparator'  => '.',
            'thousandSeparator' => ' ',
            'timeZone'          => 'Asia/Tashkent',
            'defaultTimeZone'   => 'Asia/Tashkent',
        ],
        /*'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
            'redis' => [
                'hostname' => 'localhost',
                'port' => 6379,
                'database' => 0,
            ],
        ],
        'session' => [
            'class' => 'yii\redis\Session',
            'redis' => [
                'hostname' => 'localhost',
                'port' => 6379,
                'database' => 0,
            ],
        ],*/

        'mongodb'     => [
            'class' => '\yii\mongodb\Connection',
            'dsn'   => getenv('MONGODB_DSN'),
            //'defaultDatabaseName' => 'base'
            /* 'enableLogging'   => YII_DEBUG,
             'enableProfiling' => YII_DEBUG,*/
        ],
        'fileStorage' => [
            'class'       => '\trntv\filekit\Storage',
            'baseUrl'     => '@staticUrl/uploads',
            'maxDirFiles' => 1024,
            'filesystem'  => [
                'class' => 'common\components\LocalFileSystemBuilder',
                'path'  => '@static/uploads',
            ],
        ],
        'reCaptcha'   => [
            'name'    => 'reCaptcha',
            'class'   => 'himiklab\yii2\recaptcha\ReCaptcha',
            'siteKey' => '6LdIEV4UAAAAAKxtm5fleRpWXQEcQ6IfJBL9pIXg',
            'secret'  => '6LdIEV4UAAAAAHwY0zx8cid1HFRpQ6AY488PsyQH',

        ],
    ],
    'params'     => $params,
];

return $config;
