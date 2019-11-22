<?php

namespace common\models\payment\methods\agr;

use common\models\Order;
use common\models\payment\methods\Agr;
use common\models\payment\Payment;
use Yii;
use yii\base\Model;

/**
 * Created by PhpStorm.
 * User: complex
 * Date: 10/14/15
 * Time: 4:57 PM
 */
class AgrWebForm extends Model
{
    public $VENDOR_ID;
    public $MERCHANT_TRANS_ID;

    public $MERCHANT_TRANS_AMOUNT;
    public $MERCHANT_CURRENCY;
    public $MERCHANT_TRANS_RETURN_URL;

    public $MERCHANT_TRANS_NOTE;

    public $SIGN_TIME;
    public $SIGN_STRING;
    public $MERCHANT_TRANS_DATA;

    /**
     * @var Agr
     */
    private $_method;


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

    public function prepareForm($amount, $name)
    {
        $this->VENDOR_ID         = $this->getMethod()->getVendorId();
        $this->MERCHANT_TRANS_ID = $name ?: 'Saxovatli inson';

        $this->MERCHANT_TRANS_AMOUNT     = intval($amount);
        $this->MERCHANT_CURRENCY         = 'sum';
        $this->SIGN_TIME                 = Payment::getCurrentTimeStamp();
        $this->MERCHANT_TRANS_RETURN_URL = linkTo(['/', 'success' => md5(Yii::$app->session->id)], true);

        $this->SIGN_STRING = md5(
            $this->getMethod()->getSecretKey() .
            $this->VENDOR_ID .
            $this->MERCHANT_TRANS_ID .
            $this->MERCHANT_TRANS_AMOUNT .
            $this->MERCHANT_CURRENCY .
            $this->SIGN_TIME
        );
        $form              = get_object_vars($this);
        unset($form['_method']);
        return ['form' => $form, 'action' => $this->getMethod()->getPaymentUrl()];
    }


    public function init()
    {
        $this->_method = Payment::getMethodInstance(Agr::METHOD_CODE);
        parent::init();
    }

    /**
     * @return Agr
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