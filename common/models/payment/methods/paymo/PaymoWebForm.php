<?php

namespace common\models\payment\methods\paymo;

use common\components\Config;
use common\models\payment\methods\Paymo;
use common\models\payment\Payment;
use Yii;
use yii\base\Model;

class PaymoWebForm extends Model
{
    public $parent_id = 'paymo-container';
    public $store_id;
    public $account;
    public $amount;
    public $lang;
    public $theme     = 'blue';
    public $details;
    public $success_redirect;
    public $fail_redirect;
    public $key;

    /**
     * @var Paymo
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
            $this->store_id = $this->getMethod()->getStoreId();
            $this->lang     = Config::getLanguageShortName();
            $this->details  = __('Donation on saxovat.uz');

            $form = get_object_vars($this);
            unset($form['_method']);

            $form['success_redirect'] = linkTo(['/?status=success'], true);
            $form['fail_redirect']    = linkTo(['/?status=fail'], true);
            $form['account']          = trim(strip_tags($this->account));
            if (!$form['account']) $form['account'] = '-';

            $form['amount'] = $this->amount * 100;
            $form['data']   = $this->store_id . $this->amount . $this->account . $this->getMethod()->getPassword();
            $form['key']    = hash('sha256', $form['store_id'] . $form['amount'] . $form['account'] . $this->getMethod()->getPassword());

            return $form;
        }

        return false;
    }

    public function init()
    {
        $this->_method = Payment::getMethodInstance(Paymo::METHOD_CODE);
        parent::init();
    }

    public function isActive()
    {
        return $this->_method->isEnabled();
    }

    /**
     * @return Paymo
     * @throws \yii\base\Exception
     */
    protected function getMethod()
    {
        return $this->_method;
    }


}