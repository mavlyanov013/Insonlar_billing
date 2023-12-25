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
        'paycom'  => [
            'class'     => Paycom::className(),
            'enabled'   => true,
            'liveMode'  => PAYCOM_LIVE,
            'merchants' => [
            ],
            'config'    => [
                'minAmount'    => 500,
                'maxAmount'    => 100000000,
                'merchant_id'  => '6589656f5c8188fb6e90e859',
                'merchant_key' => PAYCOM_LIVE ? 'vARaWm5E0e9yd4RJiRES1#H&D1ngs6K7ou3H' : 'E#SVCXrO?qR6dugHUsI%ITk6VWE#jcD7EmA4',
                'payment_url'  => PAYCOM_LIVE ? 'https://checkout.paycom.uz' : 'https://test.paycom.uz',
                'return_url'   => 'https://www.mehrli.uz?payme=success',
            ],
        ],
        'click'   => [
            'class'    => Click::className(),
            'enabled'  => true,
            'liveMode' => CLICK_LIVE,
            'config'   => [
                'servicePercent'   => 5,
                'merchant_id'      => 7994,
                'service_id'       => 30916,
                'merchant_user_id' => 10145,
                'secret_key'       => '8aDq50z9F1',
                'payment_url'      => 'https://my.click.uz/pay/',
                'invoice_url'      => 'https://merchant.click.uz/api/',
                'return_url'       => 'https://saxovat.uz/?success=click',
                'check_signature'  => true,
                'minAmount'        => 500,
                'maxAmount'        => 100000000,
            ],
        ],
        'upay'    => [
            'class'    => \common\models\payment\methods\Upay::className(),
            'enabled'  => true,
            'liveMode' => UPAY_LIVE,
            'config'   => [
                'minAmount'   => 500,
                'maxAmount'   => 100000000,
                'apiVersion'  => 1,
                'serviceId'   => 372,
                'allowedIps'  => ["91.212.89.86","84.54.115.115"],
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
                'allowedIps'      => [],
                'serviceLocation' => 'https://insonlar.mehrli.uz/v1/paynet',
                'wsdlLocation'    => 'https://insonlar.mehrli.uz/v1/paynet/wsdl',
                'xsdLocation'     => 'https://insonlar.mehrli.uz/v1/paynet/xsd',
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
