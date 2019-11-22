<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace common\models;

use Imagine\Image\ManipulatorInterface;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Class Volunteer
 * @property string $fullname
 * @property string $login
 * @property string $facebook
 * @property string $password
 * @property string $email
 * @property string $phone
 * @property string $intro
 * @property string $job
 * @property string $auth_key
 * @property string $access_token
 * @property string $access_token_date
 * @property string $password_reset_token
 * @property mixed $password_reset_date
 * @property string $resource
 * @property string $language
 * @property string $status
 * @property string $about
 * @property string $posts
 * @property array image
 * @property mixed description
 */
class Volunteer extends MongoModel
{
    protected $_translatedAttributes = ['fullname', 'description', 'about'];
    protected $_integerAttributes    = ['position'];

    public static function getArrayOptions()
    {
        return array_merge(['' => ''], ArrayHelper::map(self::findAll(['status' => self::STATUS_ENABLE]), 'id', 'fullname'));
    }

    public function attributes()
    {
        return [
            '_id',
            'fullname',
            'description',
            'about',
            'gender',
            'email',
            'phone',
            'image',
            'birthday',
            'job',
            'status',
            'type',
            'position',
            'created_at',
            'updated_at',
            '_translations',
        ];
    }


    public $search;

    const STATUS_ENABLE  = 'enable';
    const STATUS_DISABLE = 'disable';
    const STATUS_BLOCKED = 'blocked';
    const TYPE_VOLUNTEER = 'volunteer';
    const TYPE_EMPLOYEE  = 'employee';

    const GENDER_MALE   = 'male';
    const GENDER_FEMALE = 'female';

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE  => __('Enabled'),
            self::STATUS_DISABLE => __('Disabled'),
            self::STATUS_BLOCKED => __('Blocked'),
        ];
    }

    public static function getTypeOptions()
    {
        return [
            self::TYPE_EMPLOYEE  => __('Employee'),
            self::TYPE_VOLUNTEER => __('Volunteer'),
        ];
    }

    public static function getGenderOptions()
    {
        return [
            self::GENDER_MALE   => __('Male'),
            self::GENDER_FEMALE => __('Female'),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'volunteer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fullname', 'email', 'phone', 'status', 'image', 'description', 'type'], 'required'],

            [['fullname', 'job'], 'string', 'max' => 128],
            [['email'], 'string', 'max' => 64],
            [['phone'], 'string', 'max' => 32],
            [['description'], 'string', 'max' => 1024],
            [['about'], 'string', 'max' => 10024],

            [['position'], 'number'],
            [['search'], 'safe', 'on' => 'search'],
        ];
    }

    public function afterFind()
    {
        return parent::afterFind();
    }

    public function beforeDelete()
    {
        return parent::beforeDelete();
    }

    public function beforeSave($insert)
    {
        return parent::beforeSave($insert);
    }

    public function search($params)
    {
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
                                                   'query'      => $query,
                                                   'pagination' => [
                                                       'pageSize' => 20,
                                                   ],
                                               ]);

        $this->load($params);

        if ($this->search) {
            $query->orFilterWhere(['_translations.fullname_uz' => ['$regex' => $this->search, '$options' => 'si']]);
            $query->orFilterWhere(['_translations.fullname_cy' => ['$regex' => $this->search, '$options' => 'si']]);
            $query->orFilterWhere(['_translations.fullname_ru' => ['$regex' => $this->search, '$options' => 'si']]);
            $query->orFilterWhere(['like', 'login', $this->search]);
            $query->orFilterWhere(['like', 'email', $this->search]);
        }

        return $dataProvider;
    }

    public function getViewUrl($scheme = true)
    {
        return Url::to(['volunteer/view', 'id' => $this->id], $scheme);
    }

    public static function dataProvider($limit = 10, $type = Volunteer::TYPE_VOLUNTEER)
    {
        $query = self::find()
                     ->addOrderBy(['fullname' => SORT_ASC])
                     ->andWhere(['status' => self::STATUS_ENABLE, 'type' => $type]);
        if ($type == Volunteer::TYPE_EMPLOYEE) {
            $query->orderBy(['position' => SORT_ASC]);
        }
        return new ActiveDataProvider([
                                          'query'      => $query,
                                          'pagination' => [
                                              'pageSize' => intval(\Yii::$app->request->get('load', $limit)),
                                          ],
                                      ]);
    }

    public function getCroppedImage($width = 870, $height = 260, $watermark = false)
    {
        if ($this->image) {
            return parent::getCropImage($this->image, $width, $height, ManipulatorInterface::THUMBNAIL_OUTBOUND, $watermark);
        }

        return false;
    }

    /**
     * @param $limit
     * @return Post[]
     */
    public static function getLast($limit)
    {
        return self::find()
                   ->andWhere(['status' => self::STATUS_ENABLE])
                   ->orderBy(['created_at' => SORT_DESC])
                   ->limit($limit)
                   ->all();
    }
}
