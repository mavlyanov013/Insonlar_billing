<?php

namespace common\models\payment\methods\click;

use common\models\payment\methods\Click;
use common\models\payment\Payment;
use common\models\User;
use Yii;
use yii\base\Model;
use yii\helpers\Url;

/**
 * Created by PhpStorm.
 * User: complex
 * Date: 10/14/15
 * Time: 4:57 PM
 */
class ClickWebForm extends Model
{
    public $MERCHANT_ID;
    public $MERCHANT_SERVICE_ID;

    public $MERCHANT_USER_ID;
    public $MERCHANT_TRANS_ID;
    public $MERCHANT_USER_PHONE;

    public $MERCHANT_TRANS_AMOUNT;
    public $MERCHANT_TRANS_NOTE;

    public $SIGN_TIME;
    public $SIGN_STRING;
    public $RETURN_URL;

    /**
     * @var Click
     */
    private $_method;


    public function attributeLabels()
    {
        return [
            'MERCHANT_TRANS_AMOUNT' => __('PaymentAmount'),
        ];
    }

    public function rules()
    {
        return [
            [['MERCHANT_TRANS_AMOUNT'], 'required'],
            [['MERCHANT_TRANS_AMOUNT'], 'number',
             'tooSmall' => __('Minimal to‘lov {min}', ['min' => Yii::$app->formatter->asCurrency($this->getMethod()->getMinAmount())]),
             'tooBig'   => __('Maksimal to‘lov {max}', ['max' => Yii::$app->formatter->asCurrency($this->getMethod()->getMaxAmount())]),
             'min'      => $this->getMethod()->getMinAmount(),
             'max'      => $this->getMethod()->getMaxAmount(), 'integerOnly' => true, 'skipOnEmpty' => false],
        ];
    }

    public function prepareFormWithParams($amount, $name)
    {
        $name = mb_substr(strip_tags($name), 0, 40);
        if (!$name) $name = 'Mehrli';

        $this->MERCHANT_ID         = $this->getMethod()->getMerchantId();
        $this->MERCHANT_SERVICE_ID = $this->getMethod()->getMerchantServiceId();

        $this->MERCHANT_USER_ID      = $this->getMethod()->getMerchantUserId();
        $this->MERCHANT_TRANS_ID     = mb_substr(strip_tags($name), 0, 40);
        $this->MERCHANT_TRANS_AMOUNT = number_format(intval($amount), 2, '.', '');
        $this->MERCHANT_TRANS_NOTE   = __('Donation on saxovat.uz');
        $this->SIGN_TIME             = Yii::$app->formatter->asDatetime(time(), 'Y-MM-dd HH:mm:ss');
        $this->SIGN_TIME             = date("Y-m-d h:i:s");
        $this->RETURN_URL            = $this->getMethod()->getReturnUrl();

        $this->SIGN_STRING = md5(
            $this->SIGN_TIME .
            $this->getMethod()->getSecretKey() .
            $this->MERCHANT_SERVICE_ID .
            $this->MERCHANT_TRANS_ID .
            $this->MERCHANT_TRANS_AMOUNT
        );
        $form              = get_object_vars($this);
        unset($form['_method']);
        return ['form' => $form, 'action' => $this->getMethod()->getPaymentUrl()];
    }


    public function init()
    {
        $this->_method = Payment::getMethodInstance(Click::METHOD_CODE);
        parent::init();
    }

    /**
     * @return Click
     */
    protected function getMethod()
    {
        return $this->_method;
    }

    public function isActive()
    {
        return $this->_method->isEnabled();
    }


}