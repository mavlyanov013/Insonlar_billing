<?php

namespace common\models\payment;

use common\models\App;
use common\models\Order;
use common\models\User;
use common\models\UserApp;
use Yii;
use yii\base\BaseObject;
use yii\base\Component;
use yii\base\ErrorException;
use yii\base\Model;

/**
 * Created by PhpStorm.
 * User: complex
 * Date: 10/6/15
 * Time: 5:01 PM
 */
abstract class Method extends BaseObject
{
    protected $_code;

    public $enabled  = false;
    public $liveMode = false;
    public $name;
    public $description;
    public $config;

    public function preparePayment()
    {
        $payment = new Payment(['scenario' => Payment::SCENARIO_INSERT]);
        return $payment;
    }

    public abstract function getAdditionalInformation(Model $payment);

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function isLiveMode()
    {
        return $this->liveMode;
    }

    public function getName()
    {
        return __('Payment ' . $this->name);
    }

    public function getDescription()
    {
        return __($this->description);
    }

    public function setCode($code)
    {
        $this->_code = $code;
    }

    /**
     * Retrieve payment method code
     * @return string
     */
    public function getCode()
    {
        return $this->_code;
    }

    public function getConfig($key)
    {
        return array_key_exists($key, $this->config) ? $this->config[$key] : null;
    }


    public static function getCurrentTimeStamp()
    {
        return round(microtime(true) * 1000);
    }
}