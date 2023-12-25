<?php

namespace common\models\payment\methods\oson;

use common\components\Config;
use common\models\payment\methods\Oson;
use common\models\payment\Payment;
use GuzzleHttp\Client;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\Model;

class WebForm extends Model
{
    public $merchant_id;
    public $transaction_id;
    public $amount;
    public $phone = '';
    public $currency = 'UZS';
    public $user_account;
    public $lang;
    public $lifetime;
    public $return_url;
    public $comment;

    /**
     * @var Oson
     */
    private $_method;

    public function attributeLabels()
    {
        return [
            'amount' => __('Payment Amount'),
        ];
    }

    public function rules()
    {
        return [
            [
                ['amount'],
                'number',
                'tooSmall' => __(
                    'Minimal to‘lov {min}',
                    ['min' => Yii::$app->formatter->asCurrency($this->getMethod()->getMinAmount())]
                ),
                'tooBig' => __(
                    'Maksimal to‘lov {max}',
                    ['max' => Yii::$app->formatter->asCurrency($this->getMethod()->getMaxAmount())]
                ),
                'min' => $this->getMethod()->getMinAmount(),
                'max' => $this->getMethod()->getMaxAmount(),
                'integerOnly' => true,
                'skipOnEmpty' => false,
            ],
        ];
    }

    public function prepareFormWithParams($amount, $name)
    {
        $this->amount = number_format((int)$amount, 2, '.', '');
        $this->user_account = $name;

        if ($this->validate()) {
            $invoice = $this->createInvoice();
            $this->merchant_id = $this->getMethod()->getMerchantId();
            $this->lang = Config::getLanguageShortName();
            $this->return_url = $this->getMethod()->getReturnUrl();
            $this->comment = __('Donation on mehrli.uz');
            $this->transaction_id = $invoice->getId();

            $form = get_object_vars($this);
            unset($form['_method']);

            $client = new Client(['base_uri' => $this->getMethod()->getPaymentUrl()]);

            $res = $client->post('invoice/create', [
                'body' => json_encode($form),
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'token' => $this->getMethod()->getSecretToken()
                ]
            ]);

            $result = new OsonPaymentResult();
            $result->setAttributes(json_decode($res->getBody()->getContents()));

            return $result;
        }

        return false;
    }

    public function init()
    {
        $this->_method = Payment::getMethodInstance(Oson::METHOD_CODE);
        parent::init();
    }

    public function isActive()
    {
        return $this->_method->isEnabled();
    }

    /**
     * @return Oson
     * @throws \yii\base\Exception
     */
    protected function getMethod()
    {
        return $this->_method;
    }

    public function createInvoice()
    {
        $payment = new Payment();

        $payment->status = Payment::STATUS_PENDING;
        $payment->time = ApiForm::getCurrentTimeStamp();
        $payment->user_data = $this->user_account;
        $payment->method = $this->_method->getCode();
        $payment->create_time = ApiForm::getCurrentTimeStamp();
        $payment->transaction_id = 0;
        $payment->amount = $this->amount;

        if ($payment->save()) {
            return $payment;
        }

        throw new InvalidArgumentException(__('To\'lov yaratishda xatolik. Iltimos qaytadan urinib ko\'ring.'));
    }
}