<?php

namespace common\models\payment\methods\paycom;

use common\components\Config;
use common\models\payment\methods\Paycom;
use common\models\payment\Payment;
use common\models\ShoppingCart;
use frontend\models\PaymentForm;
use Yii;
use yii\base\Model;

class PaycomWebForm extends Model
{
    const CURRENCY_UZS = 860;
    public $merchant;
    public $amount;
    public $lang;
    public $callback;
    public $description;
    public $currency = self::CURRENCY_UZS;

    /**
     * @var Paycom
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
            [['amount'], 'number',
             'tooSmall'    => __('Minimal to‘lov {min}', ['min' => Yii::$app->formatter->asCurrency($this->getMethod()->getMinAmount())]),
             'tooBig'      => __('Maksimal to‘lov {max}', ['max' => Yii::$app->formatter->asCurrency($this->getMethod()->getMaxAmount())]),
             'min'         => $this->getMethod()->getMinAmount(),
             'max'         => $this->getMethod()->getMaxAmount(),
             'integerOnly' => true,
             'skipOnEmpty' => false],
        ];
    }

    public function prepareForm(PaymentForm $data)
    {
        $this->amount = $data->amount;

        if ($this->validate()) {
            $this->merchant    = $this->getMethod()->getMerchantId();
            $this->lang        = Config::getLanguageShortName();
            $this->callback    = $this->getMethod()->getReturnUrl();
            $this->description = __('Donation on saxovat.uz');

            $form = get_object_vars($this);
            unset($form['_method']);

            $form['account[user_data]'] = trim(strip_tags($data->user_data));
            $form['amount']             = $form['amount'] * 100;

            return ['form' => $form, 'action' => $this->getMethod()->getPaymentUrl()];
        }

        return false;
    }

    public function prepareFormWithParams($amount, $name)
    {
        $this->amount = $amount;

        if ($this->validate()) {
            $this->merchant    = $this->getMethod()->getMerchantId();
            $this->lang        = Yii::$app->language == Config::LANGUAGE_RUSSIAN ? 'ru' : 'uz';
            $this->callback    = $this->getMethod()->getReturnUrl();
            $this->description = __('Donation on saxovat.uz');

            $form = get_object_vars($this);
            unset($form['_method']);

            $form['account[user_data]'] = $name ? $name : 'Saxovatli inson';
            $form['amount']             = $form['amount'] * 100;

            return ['form' => $form, 'action' => $this->getMethod()->getPaymentUrl()];
        }

        return false;
    }

    public function init()
    {
        $this->_method = Payment::getMethodInstance(Paycom::METHOD_CODE);
        parent::init();
    }

    public function isActive()
    {
        return $this->_method->isEnabled();
    }

    /**
     * @return Paycom
     * @throws \yii\base\Exception
     */
    protected function getMethod()
    {
        return $this->_method;
    }


}