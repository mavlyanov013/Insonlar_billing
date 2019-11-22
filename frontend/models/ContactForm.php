<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class ContactForm extends Model
{
    public $name;
    public $email;
    public $message;
    public $reCaptcha;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // name, email, subject and body are required
            [['name', 'email', 'message'], 'required'],
            // email has to be a valid email address
            ['email', 'email'],
            ['message', 'string', 'max' => 12000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'verifyCode' => 'Verification Code',
        ];
    }

    /**
     * Sends an email to the specified email address using the information collected by this model.
     *
     * @return bool whether the email was sent
     */
    public function sendEmail()
    {
        $this->name = strip_tags($this->name);

        $emails = [
            getenv('CONTACT_EMAIL') => __('Mehrli qo\'llar'),
        ];

        return Yii::$app->mailer->compose([])
                                ->setTo($emails)
                                ->setFrom([getenv('EMAIL_LOGIN') => $this->name])
                                ->setReplyTo([$this->email => $this->name])
                                ->setSubject(__('Aloqa formasi'))
                                ->setTextBody(strip_tags($this->message))
                                ->send();
    }
}
