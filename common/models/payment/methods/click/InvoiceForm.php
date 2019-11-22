<?php
namespace common\models\payment\methods\click;

use common\models\payment\methods\Click;
use common\models\payment\Payment;
use common\models\User;
use common\models\UserBalanceFund;
use common\models\UserBalanceHistory;
use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * Created by PhpStorm.
 * User: complex
 * Date: 10/14/15
 * Time: 4:57 PM
 */
class InvoiceForm extends Model
{
    const ACTION_INVOICE       = 'invoice';
    const ACTION_INVOICE_CHECK = 'invoice_check';


    public $action;
    public $service_id;
    public $merchant_id;

    public $merchant_user_id;
    public $amount;
    public $transaction_param;
    public $transaction_note;

    public $phone;
    public $sign_time;
    public $sign_string;

    /**
     * @var Click
     */
    private $_method;

    public function init()
    {
        $this->_method          = Payment::getMethodInstance(Click::METHOD_CODE);
        $this->merchant_id      = $this->getMethod()->getMerchantId();
        $this->merchant_user_id = $this->getMethod()->getMerchantUserId();
        $this->service_id       = $this->getMethod()->getMerchantServiceId();

        parent::init();
    }


    /**
     * @return Click
     * @throws \yii\base\Exception
     */
    protected function getMethod()
    {
        return $this->_method;
    }

    public function attributeLabels()
    {
        return [
            'MERCHANT_TRANS_AMOUNT' => __('PaymentAmount'),
        ];
    }

    public function rules()
    {
        return [
            [['phone', 'amount',], 'required', 'on' => [self::ACTION_INVOICE],],
            [['amount'], 'number',
             'tooSmall' => __('Minimal summa {min}', ['min' => Yii::$app->formatter->asCurrency($this->getMethod()->getMinAmount())]),
             'tooBig'   => __('Maksimal summa {max}', ['max' => Yii::$app->formatter->asCurrency($this->getMethod()->getMaxAmount())]),
             'min'      => $this->getMethod()->getMinAmount(),
             'max'      => $this->getMethod()->getMaxAmount(), 'integerOnly' => true, 'skipOnEmpty' => false],
        ];
    }


    public function getError()
    {
        $errors = $this->getFirstErrors();
        return array_shift($errors);
    }

    public function sendInvoice(User $user)
    {
        if ($this->validate()) {
            $this->action            = self::ACTION_INVOICE;
            $this->transaction_param = $user->mobile;
            $this->transaction_note  = __('Payment for abt.uz account balance.');
            $this->sign_time         = date("Y-m-d H:i:s");
            $this->phone             = '998' . str_replace('-', '', $this->phone);
            $this->amount            = number_format(intval($this->amount), 2, '.', '');

            $this->sign_string = md5(
                $this->sign_time .
                $this->getMethod()->getSecretKey() .
                $this->service_id .
                $this->transaction_param .
                $this->amount
            );

            $form = get_object_vars($this);
            unset($form['_method']);

            if ($data = $this->request($form)) {
                if (isset($data['error']) && $data['error'] > 0) {
                    $this->addError('phone', $data['error_note']);
                } else {
                    $fund          = new UserBalanceFund();
                    $fund->user_id = $user->id;
                    $fund->method  = $this->_method->getCode();
                    $fund->amount  = $this->amount;

                    $fund->addInformation([
                                              'click_error'      => $data['error'],
                                              'click_error_note' => $data['error_note'],
                                          ]);

                    $fund->transaction_id = $data['invoice_id'];
                    $fund->status         = UserBalanceFund::STATUS_PENDING;
                    if ($fund->save()) {
                        return true;
                    }
                }
            }
        }
    }

    protected function request($data)
    {

        $handler = curl_init();
        $result  = false;

        for ($i = 0; $i < 4 && $result === false; $i++) {
            $handler = curl_init();

            curl_setopt_array($handler, array(
                CURLOPT_URL            => $this->getMethod()->getInvoiceUrl(),
                CURLOPT_POST           => 1,
                CURLOPT_HEADER         => 0,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS     => $data,
                CURLOPT_SSL_VERIFYPEER => false,
            ));

            $response = curl_exec($handler);
            if ($data = @json_decode($response, 1)) {
                $result = $data;
            }
        }

        curl_close($handler);
        return $result;
    }

    public function actionComplete()
    {
        if ($this->validate()) {
            $user = $this->getStoreUser();

            if ($fund = $this->getFundByFundId($this->merchant_prepare_id)) {
                //we have already created invoice for given click_trans_id
                if ($fund->user_id != $user->id) {
                    throw new ClickRequestException('This invoice has already registered for another user', -8);
                }

                if (round($fund->amount) != round($this->amount)) {
                    throw new ClickRequestException('Incorrect amount', -2);
                }

                if ($fund->status == UserBalanceFund::STATUS_PENDING) {
                    if ($this->error == 0) {
                        $fund->status = UserBalanceFund::STATUS_SUCCESS;
                        $fund->addInformation([
                                                  'click_paydoc_id'   => $this->click_paydoc_id,
                                                  'click_sign_time'   => $this->sign_time,
                                                  'click_sign_string' => $this->sign_string,
                                              ]);


                        $transaction = Yii::$app->db->beginTransaction();

                        if ($fund->save()) {
                            $balance          = new UserBalanceHistory(['scenario' => 'insert']);
                            $balance->delta   = floatval($fund->amount);
                            $balance->comment = __('Click orqali to‘lov, chek #{check_id}', ['check_id' => $this->click_paydoc_id]);

                            if ($id = $user->changeBalance($balance)) {
                                $transaction->commit();
                                return [
                                    'click_trans_id'      => $fund->transaction_id,
                                    'merchant_trans_id'   => $fund->user->mobile,
                                    'merchant_prepare_id' => $fund->id,
                                    'merchant_confirm_id' => $fund->id,
                                    'error'               => 0,
                                    'error_note'          => 'success',
                                ];
                            }
                        }
                        $transaction->rollBack();

                    } else {
                        $fund->status = UserBalanceFund::STATUS_CANCELLED;
                        $fund->addInformation([
                                                  'click_error'      => $this->error,
                                                  'click_error_note' => $this->error_note,
                                              ]);
                        if ($fund->save()) {
                            throw new ClickRequestException('Transaction cancelled', -9);
                        }
                    }

                    throw new ClickRequestException('Failed to update user', -7);
                } elseif ($fund->status == UserBalanceFund::STATUS_SUCCESS) {
                    throw new ClickRequestException('Already paid', -4);
                }

                throw new ClickRequestException('Transaction cancelled', -9);
            }

            throw new ClickRequestException('Transaction does not exist', -6);
        }

        throw new ClickRequestException($this->getValidationError(), -8);
    }

    /**
     * @return User
     * @throws ClickRequestException
     */
    public function getStoreUser()
    {

        if ($user = User::findByMobile($this->merchant_trans_id)) {
            return $user;
        }

        throw new ClickRequestException('User does not exist', -5);
    }

    /**
     * @param $clickTransId int Click Transaction ID
     * @return UserBalanceFund
     */
    public function getFundByClickTransId($clickTransId)
    {
        return UserBalanceFund::findOne([
                                            'method'         => $this->_method->getCode(),
                                            'transaction_id' => $clickTransId,
                                        ]);
    }

    /**
     * @param $id int UserBalanceFund id
     * @return UserBalanceFund
     */
    public function getFundByFundId($id)
    {
        return UserBalanceFund::findOne([
                                            'id'     => $id,
                                            'method' => $this->_method->getCode(),
                                        ]);
    }


    protected function getValidationError()
    {
        $errors  = $this->getFirstErrors();
        $message = array_shift($errors);
        return $message ? $message : 'Error in request from click';
    }

}
