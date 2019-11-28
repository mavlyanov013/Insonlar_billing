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
        'agr'     => [
            'class'    => Agr::className(),
            'enabled'  => true,
            'liveMode' => AGR_LIVE,
            'config'   => [
                'minAmount'   => 500,
                'maxAmount'   => 100000000,
                'vendor_id'   => '100418',
                'secret_key'  => '-CFt&KnL$DDd_!ATB#iiQq@PCGTk57$w',
                'payment_url' => AGR_LIVE ? 'https://agr.uz/pay' : 'https://agr.uz/sandbox',
                'return_url'  => AGR_LIVE ? 'http://www.qurbonlik.uz/order/success' : 'http://www.qurbonlik.uz/order/success',
            ],
        ],
    ],
];
