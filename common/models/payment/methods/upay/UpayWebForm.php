<?php

namespace common\models\payment\methods\upay;


use common\models\payment\methods\Upay;
use common\models\payment\Payment;
use Yii;
use yii\base\Model;

class UpayWebForm extends Model
{
    public $serviceId;
    public $personalAccount;
    public $apiVersion;
    public $amount;
    public $key;

    /**
     * @var Upay
     */
    private $_method;

    public function attributeLabels()
    {
        return [
            'account' => __('Name or phone number'),
            'amount'  => __('Payment Amount'),
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

    public function prepareForm()
    {
        if ($this->validate()) {
            $this->serviceId = $this->getMethod()->getServiceId();

            $form = get_object_vars($this);
            unset($form['_method']);


            $form['personalAccount'] = trim(strip_tags($this->personalAccount));
            if (!$form['personalAccount']) $form['personalAccount'] = '-';

            $form['amount'] = $this->amount;

            return ['form' => $form, 'action' => $this->getMethod()->getPaymentUrl()];
        }

        return false;
    }

    public function init()
    {
        $this->_method = Payment::getMethodInstance(Upay::METHOD_CODE);
        parent::init();
    }

    public function isActive()
    {
        return $this->_method->isEnabled();
    }

    /**
     * @return Upay
     * @throws \yii\base\Exception
     */
    protected function getMethod()
    {
        return $this->_method;
    }


}