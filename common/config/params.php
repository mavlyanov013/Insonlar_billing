<?php

use common\models\payment\methods\Agr;
use common\models\payment\methods\Cash;
use common\models\payment\methods\Click;
use common\models\payment\methods\Kapital;
use common\models\payment\methods\Paycom;
use common\models\payment\methods\Paymo;
use common\models\payment\methods\Paynet;

return [
    'adminEmail'                     => 'info@uzvisit.com',
    'supportEmail'                   => 'support@uzvisit.com',
    'user.passwordResetTokenExpire'  => 3600,
    'admin.passwordResetTokenExpire' => 3600,
    'payment'                        => [
        'upay'    => [
            'class'    => \common\models\payment\methods\Upay::className(),
            'enabled'  => true,
            'liveMode' => UPAY_LIVE,
            'config'   => [
                'minAmount'   => 500,
                'maxAmount'   => 100000000,
                'apiVersion'  => 1,
                'serviceId'   => 372,
                'allowedIps'  => ["91.212.89.86"],
                'api_key'     => UPAY_LIVE ? 'Lz3eMzChgRurJx8e9sQGGzKQPxniAfYj' : 'CrjRsu1ytedVb2Xu50PmtKS4ABZGqFEN',
                'payment_url' => UPAY_LIVE ? 'https://pay.smst.uz/prePay.do' : 'https://pay.smst.uz/prePay.do',
            ],
        ],
        'agr'     => [
            'class'    => Agr::className(),
            'enabled'  => true,
            'liveMode' => AGR_LIVE,
            'config'   => [
                'minAmount'   => 500,
                'maxAmount'   => 100000000,
                'vendor_id'   => '100304',
                'secret_key'  => 'RtKSIS2XskblblBgniFuXLgNEcNVgQN6',
                'payment_url' => AGR_LIVE ? 'https://agr.uz/pay' : 'https://agr.uz/sandbox',
                'return_url'  => AGR_LIVE ? 'http://www.qurbonlik.uz/order/success' : 'http://www.qurbonlik.uz/order/success',
            ],
        ],
        'paynet'  => [
            'class'    => Paynet::className(),
            'enabled'  => true,
            'liveMode' => PAYNET_LIVE,
            'config'   => [
                'minAmount'       => 500,
                'maxAmount'       => 100000000,
                'allowedIps'      => ["213.230.106.112/28", "213.230.65.80/28", "91.196.76.51"],
                'serviceLocation' => 'https://api.saxovat.uz/v1/paynet',
                'wsdlLocation'    => 'https://api.saxovat.uz/v1/paynet/wsdl',
                'xsdLocation'     => 'https://api.saxovat.uz/v1/paynet/xsd',
            ],
        ],
        'apelsin' => [
            'class'    => \common\models\payment\methods\Apelsin::className(),
            'enabled'  => true,
            'liveMode' => APELSIN_LIVE,
            'config'   => [
                'minAmount'  => 500,
                'maxAmount'  => 100000000,
                'allowedIps' => [],
            ],
        ],
    ],
];
