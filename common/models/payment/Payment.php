<?php

namespace common\models\payment;

use common\models\Admin;
use common\models\Counters;
use common\models\payment\methods\Cash;
use common\models\payment\methods\Click;
use common\models\payment\methods\paycom\api\PaycomMethod;
use DateTime;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Timestamp;
use MongoDB\BSON\UTCDateTime;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\mongodb\ActiveRecord;
use yii\mongodb\Query;
use yii\web\Application;

/**
 * This is the model class for table "payment".
 *
 * @property integer $_id
 * @property integer $local_id
 * @property string $increment_id
 * @property string $transaction_id
 * @property string $category
 * @property string $method
 * @property string $status
 * @property string $user_data
 * @property boolean $live_mode
 * @property int $_user
 * @property int $time
 * @property int $version
 * @property string $transactionTime
 * @property int $create_time
 * @property int $perform_time
 * @property int $cancel_time
 * @property int $click_paydoc_id
 * @property int $click_service_id
 * @property float $amount
 * @property float $amount_usd
 * @property float $refunded
 * @property string $information
 * @property Timestamp $created_at
 * @property Timestamp $updated_at
 * @property string $receivers
 * @property Admin $admin
 */
class Payment extends ActiveRecord
{
    public $search;
    public $datetime_range;
    public $datetime_min;
    public $datetime_max;
    const STATUS_PENDING   = 'pending';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_SUCCESS   = 'success';
    const STATUS_FAILED    = 'failed';
    const STATUS_FUNDED    = 'funded';

    const SCENARIO_TRANSFER = 'transfer';
    const SCENARIO_CASH     = 'cash';
    const SCENARIO_INSERT   = 'insert';
    const SCENARIO_UPDATE   = 'update';
    const SCENARIO_PAY      = 'pay';
    const SCENARIO_DELETE   = 'delete';
    const SCENARIO_SEARCH   = 'search';

    protected $_booleanAttributes = ['live_mode'];
    protected $_integerAttributes = ['amount', 'refunded', 'perform_time', 'create_time', 'cancel_time', 'time'];
    protected $_doubleAttributes  = [];
    protected $_idAttributes      = ['_user', '_admin'];

    const CATEGORY_GENERAL   = 'general';
    const CATEGORY_EDUCATION = 'education';
    const CATEGORY_MEDICINE  = 'medicine';
    const CATEGORY_SOCIAL    = 'social';

    public static function getPaymentCategories()
    {
        return [
            self::CATEGORY_GENERAL   => __('Umimiy'),
            self::CATEGORY_EDUCATION => __('Ta\'lim'),
            self::CATEGORY_MEDICINE  => __('Tibbiyot'),
            self::CATEGORY_SOCIAL    => __('Ijtimoiy himoya'),
        ];
    }


    public function attributes()
    {
        return [
            '_id',
            'local_id',
            '_user',
            '_admin',
            'user_data',
            'category',
            'transaction_id',
            'click_paydoc_id',
            'click_service_id',
            'method',
            'status',
            'amount',
            'amount_usd',
            'refunded',
            'transactionTime',
            'time',
            'receivers',
            'create_time',
            'perform_time',
            'cancel_time',
            'information',
            'created_at',
            'updated_at',
            'live_mode',
            'details',
            'version',
            'image',
        ];
    }


    public static function getCashStatusOptions()
    {
        return [
            self::STATUS_SUCCESS   => __('To\'landi'),
            self::STATUS_CANCELLED => __('Qaytarildi'),
            self::STATUS_FUNDED    => __('Ishlatildi'),
        ];
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_PENDING   => __('PS Pending'),
            self::STATUS_SUCCESS   => __('PS Success'),
            self::STATUS_CANCELLED => __('PS Cancelled'),
            self::STATUS_FUNDED    => __('PS Funded'),
            self::STATUS_FAILED    => __('PS Failed'),
        ];
    }

    public function getStatusLabel()
    {
        $status = self::getStatusOptions();
        return isset($status[$this->status]) ? $status[$this->status] : __(ucfirst($this->status));
    }

    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'payment';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'value' => function () {
                    $dt = new DateTime();
                    return new Timestamp(1, $dt->getTimestamp());
                },
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['method', 'status', 'transaction_id'], 'required', 'on' => [self::SCENARIO_INSERT]],

            [['amount'], 'validateAmount'],

            [['status', 'amount', 'user_data'], 'required', 'on' => [self::SCENARIO_CASH]],
            [['details', 'time', 'image'], 'safe', 'on' => [self::SCENARIO_CASH]],
            [['details'], 'string', 'max' => 2000, 'on' => [self::SCENARIO_CASH]],

            [['information', 'status', 'time'], 'safe', 'on' => [self::SCENARIO_INSERT, self::SCENARIO_UPDATE]],
            [['search', 'status', 'method', 'datetime_min', 'datetime_max', 'datetime_range'], 'safe', 'on' => [self::SCENARIO_SEARCH]],
        ];
    }

    public function validateAmount($attribute)
    {
        if ($this->amount == 0 && $this->amount_usd == 0) {
            $this->addError($attribute, __('Amount or Amount USD required'));
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'           => __('ID'),
            'increment_id' => __('Increment ID'),
            'method'       => __('Payment Method'),
            'status'       => __('Status'),
            'user_id'      => __('User'),
            'app_id'       => __('Application'),
            'amount'       => __('Amount'),
            'description'  => __('Description'),
            'user_data'    => __('User Data'),
            'time'         => __('Time'),
            'information'  => __('Additional Information'),
            'created_at'   => __('Created At'),
            'updated_at'   => __('Updated At'),
            'search'       => __('Search by ID / Amount / Payment Method'),
        ];
    }

    /**
     * apps relation
     * @return \yii\db\ActiveQueryInterface
     */
    public function getAdmin()
    {
        return $this->hasOne(Admin::className(), ['_id' => '_admin']);
    }


    public function getId()
    {
        return (string)$this->_id;
    }

    public function beforeSave($insert)
    {
        if ($this->method == Cash::METHOD_CODE) {

            if ($date = DateTime::createFromFormat('d-m-Y H:i', $this->time)) {
                $this->time = $date->getTimestamp() * 1000;
            }
        }

        if (is_array($this->information)) {
            $this->information = serialize($this->information);
        }

        if (!$this->category) {
            $this->category = self::CATEGORY_GENERAL;
        }

        if (Yii::$app instanceof Application && Yii::$app->request->isPost) {
            foreach ($this->_booleanAttributes as $attribute) {
                $this->setAttribute($attribute, boolval($this->getAttribute($attribute)));
            }
            foreach ($this->_integerAttributes as $attribute) {
                $this->setAttribute($attribute, intval($this->getAttribute($attribute)));
            }
            foreach ($this->_doubleAttributes as $attribute) {
                $this->setAttribute($attribute, doubleval($this->getAttribute($attribute)));
            }
            foreach ($this->_idAttributes as $attribute) {
                $value = $this->getAttribute($attribute);
                if ($value && is_string($value))
                    $this->setAttribute($attribute, new ObjectId($value));
            }
        }

        if (!$this->perform_time) $this->perform_time = 0;
        if (!$this->cancel_time) $this->cancel_time = 0;
        if (!$this->create_time) $this->create_time = 0;

        if ($this->local_id == false || $this->isNewRecord) {
            $this->local_id = Counters::getNextSequence(self::collectionName());
        }

        if ($this->isNewRecord) {
            $this->live_mode = $this->methodInstance()->liveMode;
        }

        $this->user_data = trim(strip_tags($this->user_data));

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($this->method == Cash::METHOD_CODE && $this->transaction_id == null) {
            $this->updateAttributes(['transaction_id' => Counters::getNextSequence('cash_cheque_id')]);
        }
        parent::afterSave($insert, $changedAttributes);
    }


    public function addAllInformation($data)
    {
        if (is_string($this->information)) {
            $this->information = @unserialize($this->information);
        }
        if (!$this->information) {
            $this->information = [];
        }

        $this->information = array_merge($this->information, $data);

        return $this;
    }

    public function addInformation($code, $value)
    {
        if (is_string($this->information)) {
            $this->information = @unserialize($this->information);
        }
        if (!$this->information) {
            $this->information = [];
        }
        $data              = $this->information;
        $data[$code]       = $value;
        $this->information = $data;

        return $this;
    }

    /**
     * Retrieve all methods of payment
     * @param $filterActive bool
     * @return Method[]
     * @throws Exception
     */
    public static function getAllMethods($filterActive = false)
    {
        $methods = array();

        if (isset(Yii::$app->params['payment'])) {
            $config = Yii::$app->params['payment'];
            foreach ($config as $code => $methodConfig) {
                $data = self::getMethodInstance($code, $methodConfig);
                if ($data !== false) {
                    if (!$filterActive || $data->isEnabled()) {
                        $methods[$code] = $data;
                    }
                }
            }
        }

        return $methods;
    }

    public static function getMethodOptions()
    {
        $result  = [];
        $methods = self::getAllMethods(true);

        foreach ($methods as $method) {
            $result[$method->getCode()] = $method->getName();
        }

        return $result;
    }

    protected static $_methods;

    /**
     * @param      $code
     * @param bool $config
     * @return bool|Method|object
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     */
    public static function getMethodInstance($code, $config = false)
    {
        /**
         * @var $method Method
         */
        if (isset(self::$_methods[$code])) {
            return self::$_methods[$code];
        }
        if (!$code) {
            throw new Exception(__('Undefined payment method'));
        }
        if ($config == false) {
            if (!isset(Yii::$app->params['payment'][$code])) {
                throw new Exception(__('Payment configuration not found for {method}', ['method' => $code]));
            }
            $config = Yii::$app->params['payment'][$code];
        }

        if (empty($config['class'])) {
            return false;
        }

        $method = Yii::createObject($config);

        $method->setCode($code);
        self::$_methods[$code] = $method;

        return $method;
    }

    /**
     * @return bool|Method|object
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function methodInstance()
    {
        return self::getMethodInstance($this->method);
    }

    public function getAdditionalInformation()
    {
        return @unserialize($this->information);
    }

    public function search($params, $provider = true)
    {
        $limit = 300;

        $query = self::find();
        $this->load($params);

        if (!$this->status) {
            $this->status = self::STATUS_SUCCESS;
        }

        if (!$this->datetime_range && !Yii::$app->request->isAjax) {
            $from = new DateTime();
            $from->setTime(0, 0, 0);
            $to = new DateTime();
            $to->setTime(23, 59, 59);


            // $this->datetime_range = $from->format('d-m-Y H:i') . ' / ' . $to->format('d-m-Y H:i');
        }


        if ($this->search) {
            $method = Payment::getMethodInstance(Click::METHOD_CODE);

            if (is_numeric($this->search) && in_array($this->search, $method->getServiceIds())) {
                $query->orFilterWhere(['click_service_id' => $this->search]);
            } else {
                $query->orFilterWhere(['like', 'user_data', $this->search]);
                $query->orFilterWhere(['like', 'transaction_id', $this->search]);

                if (intval($this->search)) {
                    $query->orFilterWhere(['$eq', 'amount', intval($this->search)]);
                }
            }
        }

        if ($this->status) {
            $query->andFilterWhere(['status' => $this->status]);
            $limit = 300;
        }
        if ($this->method) {
            $query->andFilterWhere(['method' => $this->method]);
        }


        if ($this->datetime_range) {
            $ranges = explode(' / ', preg_replace('!\s+!', ' ', $this->datetime_range));
            if (count($ranges) == 2) {
                list($from, $to) = $ranges;
                if ($from && $to) {
                    if ($fromDate = DateTime::createFromFormat('d-m-Y H:i:s', $from . ':59')) {

                    } else if ($fromDate = DateTime::createFromFormat('d-m-Y H:i:s', $from)) {

                    }

                    if ($toDate = DateTime::createFromFormat('d-m-Y H:i:s', $to . ':59')) {

                    } else if ($toDate = DateTime::createFromFormat('d-m-Y H:i:s', $to)) {

                    }

                    if ($fromDate && $toDate) {
                        $query->andFilterWhere([
                            'time' => [
                                '$gte' => $fromDate->getTimestamp() * 1000,
                                '$lte' => $toDate->getTimestamp() * 1000,
                            ],
                        ]);
                    }
                }
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'sort'       => [
                'defaultOrder' => ['updated_at' => SORT_DESC],
            ],
            'pagination' => [
                'pageSize' => $limit,
            ],
        ]);


        return $provider ? $dataProvider : $query->all();
    }


    public function getPaycomState()
    {
        if ($this->status == self::STATUS_PENDING) {
            return PaycomMethod::TRANSACTION_STATE_PENDING;
        }
        if ($this->status == self::STATUS_SUCCESS) {
            return PaycomMethod::TRANSACTION_STATE_SUCCESS;
        }
        if ($this->status == self::STATUS_CANCELLED) {
            if ($this->perform_time == null) {
                return PaycomMethod::TRANSACTION_STATE_CANCELLED_BEFORE_PERFORM;
            }
            return PaycomMethod::TRANSACTION_STATE_CANCELLED_AFTER_PERFORM;
        }
        return PaycomMethod::TRANSACTION_STATE_CANCELLED_AFTER_PERFORM;
    }

    public function getPaynetState()
    {
        if ($this->status == self::STATUS_PENDING) {
            return PaycomMethod::TRANSACTION_STATE_PENDING;
        }
        if ($this->status == self::STATUS_SUCCESS) {
            return PaycomMethod::TRANSACTION_STATE_SUCCESS;
        }
        if ($this->status == self::STATUS_CANCELLED) {
            if ($this->perform_time == null) {
                return PaycomMethod::TRANSACTION_STATE_CANCELLED_BEFORE_PERFORM;
            }
            return PaycomMethod::TRANSACTION_STATE_CANCELLED_AFTER_PERFORM;
        }
        return PaycomMethod::TRANSACTION_STATE_CANCELLED_AFTER_PERFORM;
    }

    public function getAmount()
    {
        return floatval($this->amount);
    }

    public function getAmountCents()
    {
        return $this->getAmount() * 100;
    }

    const TIMEOUT = 43200000;

    public function hasTimeout()
    {
        return self::getCurrentTimeStamp() - $this->time > self::TIMEOUT;
    }

    public static function getCurrentTimeStamp()
    {
        return round(microtime(true) * 1000);
    }

    public function afterFind()
    {
        $this->information = $this->getAdditionalInformation();
        parent::afterFind();
    }

    public function getInfo($key)
    {
        return (isset($this->information[$key])) ? $this->information[$key] : null;
    }

    /**
     * @return float|int
     * @throws \yii\base\InvalidConfigException
     */
    public function getCreatedAtTimeStamp()
    {
        return Yii::$app->formatter->asDatetime($this->created_at, 'U') * 1000;
    }

    /**
     * @return float|int
     * @throws \yii\base\InvalidConfigException
     */
    public function getUpdatedAtTimeStamp()
    {
        return Yii::$app->formatter->asDatetime($this->updated_at, 'U') * 1000;
    }

    public function getMethodLabel()
    {
        return __(ucfirst($this->method));
    }

    public function getPaymentDateFormatted()
    {
        return Yii::$app->formatter->asDatetime(
            is_object($this->time) ? $this->time->getTimestamp() : $this->time / 1000
        );
    }

    public function getPaymentDateFormattedAsDay()
    {
        return Yii::$app->formatter->asDate(
            is_object($this->time) ? $this->time->getTimestamp() : $this->time / 1000
            , "php:d/m/Y");
    }

    public function getPaymentDateFormattedAsTime()
    {
        return Yii::$app->formatter->asTime(
            is_object($this->time) ? $this->time->getTimestamp() : $this->time / 1000
            , 'php:H:i:s');
    }

    /**
     * @return \yii\mongodb\Connection
     */
    private static function getConnection()
    {
        return Yii::$app->mongodb;
    }

    public static function getTodayPaymentAmount()
    {

        $today = new DateTime('now');
        $today->setTime(0, 0, 0);

        $sum = (new Query())
            ->from(self::collectionName())
            ->where([
                'time'   => [
                    '$gte' => ($today->getTimestamp()) * 1000,
                    '$lte' => ($today->getTimestamp() + 24 * 3600 - 1) * 1000,
                ],
                'status' => [
                    '$eq' => self::STATUS_SUCCESS,
                ],
            ])
            ->sum('amount', self::getConnection()->getDatabase());

        return $sum ? $sum : 0;
    }

    public static function getTodayPaymentCount()
    {

        $today = new DateTime('now');
        $today->setTime(0, 0, 0);

        $sum = (new Query())
            ->from(self::collectionName())
            ->where([
                'time'   => [
                    '$gte' => ($today->getTimestamp()) * 1000,
                    '$lte' => ($today->getTimestamp() + 24 * 3600 - 1) * 1000,
                ],
                'status' => [
                    '$eq' => self::STATUS_SUCCESS,
                ],
            ])
            ->count();

        return $sum ? $sum : 0;
    }


    public static function getTodayPayments()
    {

        $today = new DateTime('now');
        $today->setTime(0, 0, 0);

        $payments = self::find()
                        ->where([
                            'time'   => [
                                '$gte' => ($today->getTimestamp()) * 1000,
                                '$lte' => ($today->getTimestamp() + 24 * 3600 - 1) * 1000,
                            ],
                            'status' => [
                                '$eq' => self::STATUS_SUCCESS,
                            ],
                        ])
                        ->orderBy(['time' => SORT_ASC])
                        ->all();

        return $payments;
    }

    public static function getThisMonthPayments()
    {

        $today = new DateTime('now');
        $today->setTime(0, 0, 0);
        $today->setDate($today->format('Y'), $today->format('m'), 1);

        $sum = (new Query())
            ->from(self::collectionName())
            ->where([
                'time'   => [
                    '$gte' => ($today->getTimestamp()) * 1000,
                ],
                'status' => [
                    '$eq' => self::STATUS_SUCCESS,
                ],
            ])
            ->sum('amount', self::getConnection()->getDatabase());

        return $sum ? $sum : 0;
    }

    public static function getChartData()
    {
        $today = new DateTime('now');
        $today->setDate(date('Y') - 1, date('m'), date('d'));
        $data = self::getCollection()->aggregate([
            [
                '$match' => [
                    'time'   => ['$gte' => $today->getTimestamp() * 1000],
                    'status' => ['$eq' => self::STATUS_SUCCESS]
                ],
            ], [
                '$sort' => ['time' => -1],
            ], [
                '$group' => [
                    '_id'  => [
                        //'$floor' => ['$divide' => ['$time', 86400 * 1000]]
                        'year'  => ['$year' => ['date' => '$created_at', 'timezone' => 'Asia/Tashkent']],
                        'month' => ['$month' => ['date' => '$created_at', 'timezone' => 'Asia/Tashkent']],
                        'day'   => ['$dayOfMonth' => ['date' => '$created_at', 'timezone' => 'Asia/Tashkent']],
                    ],
                    'data' => ['$sum' => '$amount'],
                ],
            ],
        ]);
        return array_map(function ($item) {
            return [$item['_id'], $item['data']];
        }, $data);
    }

}
