<?php
namespace common\models\payment\methods\paycom\api\method;

use common\models\payment\methods\paycom\api\PaycomApiUser;
use common\models\payment\methods\paycom\api\PaycomJsonRPCError;
use common\models\payment\methods\paycom\api\PaycomMethod;
use Yii;

class ChangePassword extends PaycomMethod
{
    public $password;

    public function rules()
    {
        return [
            [['password'], 'required'],
        ];
    }

    /**
     * @return array
     * @throws PaycomJsonRPCError
     */
    protected function processMethod()
    {
        if (PaycomApiUser::changeApiPassword($this->password)) {
            return [
                'success' => true,
            ];
        }
        throw new PaycomJsonRPCError(self::MSG_SYSTEM_UNEXPECTED_BEHAVIOUR, -32400);
    }


}