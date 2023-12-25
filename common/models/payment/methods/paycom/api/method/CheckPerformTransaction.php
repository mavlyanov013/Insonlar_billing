<?php

namespace common\models\payment\methods\paycom\api\method;

use common\models\payment\methods\paycom\api\PaycomMethod;

class CheckPerformTransaction extends PaycomMethod
{

    public function rules()
    {
        return [
            [['amount', 'account'], 'required'],
            [['account'], 'validAccount'],
            [['amount'], 'validAmount'],
        ];
    }


    protected function processMethod()
    {
        return [
            'allow' => true,
        ];
    }
}