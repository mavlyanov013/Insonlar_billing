<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace common\models;

use MongoDB\BSON\Timestamp;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Class Volunteer
 * @property string          $fullname
 * @property string          to_whom
 * @property string          passport
 * @property string          email
 * @property string          address
 * @property string          phone
 * @property string          phone2
 * @property string          $intro
 * @property string[]        attachments
 * @property string          number
 * @property string          amount
 * @property string          year
 * @property string          $status
 * @property string[]        images
 * @property AppealComment[] comments
 */
class Appeal extends MongoModel
{
    protected $_translatedAttributes = [];
    protected $_integerAttributes    = ['age', 'year'];
    public    $agreement;

    const COOKIE_NAME         = '_af';
    const CAPTCHA_COOKIE_NAME = '_ac';

    public function attributes()
    {
        return [
            '_id',
            'fullname',
            'diagnose',
            'to_whom',
            'phone',
            'phone2',
            'images',
            'age',
            'address',
            'email',
            'text',
            'attachments',
            'number',
            'year',
            'status',
            'created_at',
            'updated_at',
            '_translations',
            '_ip',
        ];
    }


    public $search;

    const STATUS_NEW        = 'new';
    const STATUS_PROCESSING = 'processing';
    const STATUS_APPROVED   = 'approved';
    const STATUS_DECLINED   = 'declined';
    const STATUS_DELETED    = 'deleted';
    const STATUS_CLOSED     = 'closed';


    public static function getStatusOptions()
    {
        return [
            self::STATUS_NEW        => __('New'),
            self::STATUS_PROCESSING => __('Processing'),
            self::STATUS_APPROVED   => __('Approved'),
            self::STATUS_DECLINED   => __('Declined'),
            self::STATUS_CLOSED     => __('Closed'),
            self::STATUS_DELETED    => __('Deleted'),
        ];
    }

    public function getStatusLabel()
    {
        $arr = self::getStatusOptions();
        return isset($arr[$this->status]) ? $arr[$this->status] : $this->status;
    }

    public function getNextStatusArray()
    {
        $options = self::getStatusOptions();
        $result  = [];
        foreach (self::getNextStatus($this->status) as $status) {
            $result[$status] = (isset($options[$status])) ? $options[$status] : __($status);
        }

        return $result;
    }

    public static function getNextStatus($status)
    {
        switch ($status) {
            case self::STATUS_NEW:
                return [
                    self::STATUS_NEW,
                    self::STATUS_PROCESSING,
                ];
            case self::STATUS_PROCESSING:
                return [
                    self::STATUS_PROCESSING,
                    self::STATUS_APPROVED,
                    self::STATUS_DECLINED,
                ];
            case self::STATUS_APPROVED:
                return [
                    self::STATUS_DECLINED,
                    self::STATUS_CLOSED,
                ];
            case self::STATUS_DECLINED:
                return [
                    self::STATUS_DECLINED,
                    self::STATUS_APPROVED,
                ];
            case self::STATUS_CLOSED:
                return [
                    self::STATUS_CLOSED,
                ];
        }

        return [$status];
    }

    public function attributeLabels()
    {
        return [
            'fullname'    => __('Bolaning ism-sharifi'),
            'to_whom'     => __('Kimga'),
            'attachments' => __('Rasm va hujjatlar ilova qiling'),
            'text'        => __('Xabar mazmuni'),
            'agreement'   => __('Foydalanish shartlari bilan tanishdim va roziman'),
            'phone'       => __('Telefon raqamingiz'),
            'phone2'      => __('Qo\'shimcha telefon'),
            'address'     => __('Yashash manzili'),
            'age'         => __('Yoshi'),
            'year'        => __('Tug\'ilgan yili'),
            'diagnose'    => __('Tashxiz'),
            'email'       => __('Elektron pochta'),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'appeal';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $date = intval((new \DateTime())->format('Y'));
        return [
            [['fullname', 'year', 'phone', 'email', 'address', 'text', 'diagnose', 'agreement'], 'required', 'on' => 'insert'],

            [['fullname', 'address', 'diagnose'], 'string', 'max' => 128],
            [['_ip'], 'default', 'value' => Yii::$app->request->getUserIP()],
            [['phone2', 'attachments'], 'safe', 'on' => ['insert']],
            [['phone', 'phone2'], 'string', 'max' => 19],
            [['email'], 'email'],

            [['year'], 'number', 'integerOnly' => true, 'min' => $date - 20, 'max' => $date],
            [['status'], 'safe', 'on' => 'update'],
            [['search'], 'safe', 'on' => 'search'],

            [['phone'], 'match', 'on' => 'insert', 'pattern' => '/^\+\(?998\)?[ ]{0,1}[0-9]{2}[- ]{0,1}[0-9]{3}[- ]{0,1}[0-9]{2}[- ]{0,1}[0-9]{2}$/', 'message' => __('Wrong phone number')],

        ];
    }

    public function afterFind()
    {
        parent::afterFind();
    }

    public function beforeDelete()
    {
        return parent::beforeDelete();
    }

    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            do {
                $number = rand(100, 999) . '-' . rand(100, 999) . '-' . rand(100, 999);

            } while (self::findOne(['number' => $number]) != null);

            $this->number = $number;
            $this->status = self::STATUS_NEW;
        }

        foreach ($this->getAttributes() as $attribute => $value) {
            if (is_string($value) || is_numeric($value))
                $this->$attribute = strip_tags($value);
        }

        return parent::beforeSave($insert);
    }

    public function search($params)
    {
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'sort'       => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ],
                'attributes'   => [
                    'status',
                    'number',
                    'fullname',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if ($this->search) {
            $query->orFilterWhere(['like', 'text', $this->search]);
            $query->orFilterWhere(['like', 'address', $this->search]);
            $query->orFilterWhere(['like', 'diagnose', $this->search]);
            $query->orFilterWhere(['like', 'fullname', $this->search]);
            $query->orFilterWhere(['like', 'phone', $this->search]);
            $query->orFilterWhere(['like', 'email', $this->search]);
            $query->orFilterWhere(['like', 'number', $this->search]);
        }

        return $dataProvider;
    }

    public function getViewUrl($scheme = true)
    {
        return Url::to(['appeal/view', 'number' => $this->number], $scheme);
    }

    public function sendEmail()
    {

        $admin  = getenv('EMAIL_LOGIN');
        $emails = [
            $admin => __('Murojaat {number}', ['number' => $this->number]),
        ];

        try {
            $mail = Yii::$app->mailer
                ->compose(
                    [
                        'html' => 'notification-html',
                        'text' => 'notification-text',
                    ],
                    ['appeal' => $this]
                )
                ->setSubject($this->fullname)
                ->setFrom($emails)
                ->setTo([$admin => $this->fullname]);

            if ($this->email) {
                $mail->setReplyTo([$this->email => $this->fullname]);
            }

            $mail->send();

            $this->notifyOwner();

        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
            Yii::error($e->getMessage());
        }
    }

    public function notifyOwner()
    {
        if ($this->email) {
            try {
                $admin  = getenv('EMAIL_LOGIN');
                $emails = [
                    $admin => 'Mehrli.uz',
                ];

                $mail = Yii::$app->mailer
                    ->compose(
                        [
                            'html' => 'appeal-new-html',
                            'text' => 'appeal-new-text',
                        ],
                        [
                            'appeal' => $this,
                        ]
                    )
                    ->setSubject(__('Murojaat {number} ro\'yxatga olindi', ['number' => $this->number]))
                    ->setFrom($emails)
                    ->setTo([$this->email => $this->fullname])
                    ->setReplyTo($emails);


                return $mail->send();

            } catch (\Exception $e) {
                Yii::error($e->getMessage());
            }
        }

        return false;
    }

    public function displayAttachments()
    {
        return implode(', ', array_map(function ($item) {
            return Html::a($item['name'], Url::current(['attachment' => $item['name']]), ['class' => 'btn-info btn-outline']);
        }, $this->attachments ?: []));
    }

    public static function checkLimitLastTime($minutes = 30)
    {
        return self::find()
                   ->where(['_ip' => Yii::$app->request->getUserIP()])
                   ->andWhere(['created_at' => ['$gte' => new Timestamp(1, time() - $minutes * 60)]])
                   ->count() >= 3;
    }

    public function getNumberToken()
    {
        return hash('sha256', $this->number . '$uniqueSalt@2019--');
    }
}
