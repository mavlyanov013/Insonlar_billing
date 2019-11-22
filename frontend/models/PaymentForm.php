<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class PaymentForm extends Model
{
    public $user_data;
    public $amount;
    public $verifyCode;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['amount'], 'required'],
            [['amount'], 'number', 'integerOnly' => true, 'min' => 500, 'max' => 100000000],
            [['user_data'], 'string', 'max' => 200, 'min' => 0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_data'  => __('Ism, telefon raqam'),
        ];
    }
}
