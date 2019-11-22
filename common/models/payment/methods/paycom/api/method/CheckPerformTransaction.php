<?php

namespace common\models\payment\methods\paycom\api\method;

use common\models\payment\methods\paycom\api\PaycomMethod;

class CheckPerformTransaction extends PaycomMethod
{

    public function rules()
    {
        return [
            [['amount', 'account'], 'required'],
            [['amount'], 'validAccount'],
        ];
    }


    protected function processMethod()
    {
        return [
            'allow' => true,
        ];
    }


}