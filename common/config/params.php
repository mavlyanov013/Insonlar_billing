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
        'click'   => [
            'class'    => Click::className(),
            'enabled'  => true,
            'liveMode' => CLICK_LIVE,
            'config'   => [
                'servicePercent'   => 5,
                'merchant_id'      => 7994,
                'service_id'       => 11854,
                'merchant_user_id' => 10145,
                'secret_key'       => 'hh4PcbaDQLd4D*lda$IT6uRHt',
                'payment_url'      => 'https://my.click.uz/pay/',
                'invoice_url'      => 'https://merchant.click.uz/api/',
                'return_url'       => 'https://www.mehrli.uz/donate/success',
                'check_signature'  => true,
                'minAmount'        => 500,
                'maxAmount'        => 100000000,
            ],
        ],
        'paycom'  => [
            'class'     => Paycom::className(),
            'enabled'   => true,
            'liveMode'  => PAYCOM_LIVE,
            'merchants' => [
                '59eebda7f1e0edb90a8b4567' => '5b0fb0af0d258d1f23f1c73e',
            ],
            'config'    => [
                'minAmount'    => 500,
                'maxAmount'    => 100000000,
                'merchant_id'  => '5b31f08cc161ccb940856918',
                'merchant_key' => PAYCOM_LIVE ? 'CsOJ7?47jhcE%zc?ITeHIjiHpjzXf?y8PFpC' : '?g3SdOZU98xmiX1ks#&4HCDA8TvHuH2sYcMq',
                'payment_url'  => PAYCOM_LIVE ? 'https://checkout.paycom.uz' : 'https://test.paycom.uz',
                'return_url'   => 'https://www.mehrli.uz?payme=success',
            ],
        ],
        'paymo'   => [
            'class'    => Paymo::className(),
            'enabled'  => true,
            'liveMode' => PAYMO_LIVE,
            'config'   => [
                'minAmount'   => 500,
                'maxAmount'   => 100000000,
                'store_id'    => 38,
                'api_key'     => PAYMO_LIVE ? 'Lz3eMzChgRurJx8e9sQGGzKQPxniAfYj' : 'CrjRsu1ytedVb2Xu50PmtKS4ABZGqFEN',
                'payment_url' => PAYMO_LIVE ? 'https://checkout.paycom.uz' : 'https://test.paycom.uz',
            ],
        ],
        'cash'    => [
            'class'   => Cash::className(),
            'enabled' => false,
            'config'  => [
                'minAmount' => 500,
                'maxAmount' => 100000000,
            ],
        ],
        'paynet'  => [
            'class'    => Paynet::className(),
            'enabled'  => false,
            'liveMode' => PAYNET_LIVE,
            'config'   => [
                'minAmount'       => 500,
                'maxAmount'       => 100000000,
                'allowedIps'      => ["213.230.106.112/28", "213.230.65.80/28", "80.80.218.158"],
                'serviceLocation' => 'https://api.saxovat.uz/v1/paynet',
                'wsdlLocation'    => 'https://api.saxovat.uz/v1/paynet/wsdl',
                'xsdLocation'     => 'https://api.saxovat.uz/v1/paynet/xsd',
            ],
        ],
        'kapital' => [
            'class'    => Kapital::className(),
            'enabled'  => true,
            'liveMode' => KAPITAL_LIVE,
            'config'   => [
                'allowedIps' => KAPITAL_LIVE ? ["87.237.236.236", "87.237.235.235", "80.80.218.158"] : [],
                'minAmount'  => 500,
                'maxAmount'  => 100000000,
            ],
        ],
        'agr'     => [
            'class'    => Agr::className(),
            'enabled'  => true,
            'liveMode' => AGR_LIVE,
            'config'   => [
                'minAmount'   => 500,
                'maxAmount'   => 100000000,
                'vendor_id'   => '100253',
                'secret_key'  => 'B1HIWj2ZO+kwAvchchO93@8Nu4xU64j9',
                'payment_url' => AGR_LIVE ? 'https://agr.uz/pay' : 'https://agr.uz/sandbox',
                'return_url'  => AGR_LIVE ? 'http://www.qurbonlik.uz/order/success' : 'http://www.qurbonlik.uz/order/success',
            ],
        ],
    ],
];
