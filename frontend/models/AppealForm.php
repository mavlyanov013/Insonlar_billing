<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace frontend\models;

use himiklab\yii2\recaptcha\ReCaptchaValidator;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class AppealForm extends Model
{
    public $reCaptcha;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['reCaptcha'], ReCaptchaValidator::class, 'message' => __('Tasdiqlash uchun katakni belgilang')],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'reCaptcha' => __('Men robot emasman'),
        ];
    }
}
