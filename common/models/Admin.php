<?php

namespace common\models;

use common\components\Config;
use Yii;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\bootstrap\Html;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "admin".
 *
 * @property integer $_id
 * @property integer $id
 * @property string  $fullname
 * @property string  $login
 * @property string  $password
 * @property string  $email
 * @property string  $telephone
 * @property string  $auth_key
 * @property string  $password_reset_token
 * @property string  $password_reset_date
 * @property string  $resource
 * @property string  $language
 * @property string  $status
 * @property string  $created_at
 * @property string  $updated_at
 */
class Admin extends MongoModel implements IdentityInterface
{
    public function attributes()
    {
        return ['_id', 'fullname', 'login', 'password', 'email', 'telephone', 'auth_key', 'resource', 'language', 'status', 'created_at', 'updated_at', 'password_reset_token', 'password_reset_date'];
    }


    public $search;
    public $confirmation;
    public $change_password;
    public $isSuperAdmin = false;

    const STATUS_ENABLE  = 'enable';
    const STATUS_DISABLE = 'disable';
    const STATUS_BLOCKED = 'blocked';

    const SUPER_ADMIN_LOGIN = 'admin';

    const CACHE_KEY_ADMIN_MENU = 'admin_menu';
    const CACHE_TAG_ADMIN_MENU = 'admin_menu';

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE  => __('Enabled'),
            self::STATUS_DISABLE => __('Disabled'),
            self::STATUS_BLOCKED => __('Blocked'),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'admin';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'value' => $this->getTimestampValue(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fullname', 'login', 'email', 'status'], 'required', 'on' => ['insert', 'update']],

            [['fullname', 'email', 'language', 'telephone'], 'required', 'on' => ['profile']],

            [['password', 'confirmation'], 'required', 'on' => ['insert']],

            [['password', 'confirmation'], 'required', 'on' => ['update', 'profile'], 'when' => function ($model) {
                return $model->change_password == 1;
            }, 'whenClient'                                 => "function (attribute, value) {return $('#change_password').is(':checked');}"],

            [['confirmation'], 'compare', 'on' => ['insert'], 'compareAttribute' => 'password', 'skipOnEmpty' => false, 'message' => __('Confirmation does not match')],

            [['confirmation'], 'compare', 'on' => ['update', 'profile'], 'compareAttribute' => 'password', 'skipOnEmpty' => false, 'message' => __('Confirmation does not match'), 'when' => function ($model) {
                return $model->change_password == 1;
            }],


            [['language'], 'in', 'range' => array_keys(Config::getLanguageOptions())],
            [['resource'], 'safe', 'on' => ['update']],

            [['login', 'email'], 'unique', 'on' => ['update', 'insert']],
            [['email'], 'email'],

            [['password_reset_date', 'created_at', 'updated_at', 'change_password'], 'safe'],

            [['fullname', 'password'], 'string', 'max' => 128],
            [['email'], 'string', 'max' => 64],
            [['telephone', 'auth_key'], 'string', 'max' => 32],
            [['password_reset_token'], 'string', 'max' => 255],


            [['search'], 'safe', 'on' => 'search'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                   => __('ID'),
            'fullname'             => __('Fullname'),
            'login'                => __('Login'),
            'password'             => __('Password'),
            'email'                => __('Email'),
            'telephone'            => __('Telephone'),
            'auth_key'             => __('Auth Key'),
            'password_reset_token' => __('Password Reset Token'),
            'password_reset_date'  => __('Password Reset Date'),
            'language'             => __('Language'),
            'status'               => __('Status'),
            'created_at'           => __('Created At'),
            'updated_at'           => __('Updated At'),
            'search'               => __('Search by Login / Name / Email'),
            'confirmation'         => __('Password Confirmation'),
        ];
    }

    /**
     * Finds user by login
     *
     * @param string $login
     * @return Admin|null
     */
    public static function findByLogin($login)
    {
        return static::findOne(['login' => $login, 'status' => self::STATUS_ENABLE]);
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    public function setPassword($password)
    {
        $this->password = Yii::$app->security->generatePasswordHash($password);
        $this->removePasswordResetToken();

    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['_id' => $id, 'status' => self::STATUS_ENABLE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function afterFind()
    {
        $this->isSuperAdmin = $this->login == self::SUPER_ADMIN_LOGIN;

        return parent::afterFind();
    }

    public function beforeDelete()
    {
        if ($this->isSuperAdmin) {
            throw new Exception(__('Can not delete supper admin'));
        }

        TagDependency::invalidate(Yii::$app->cache, [self::CACHE_TAG_ADMIN_MENU]);

        return parent::beforeDelete();
    }

    public function beforeSave($insert)
    {
        if ($this->change_password || $this->isNewRecord) $this->setPassword($this->confirmation);
        if ($this->isSuperAdmin) {
            $this->status = self::STATUS_ENABLE;
            $this->login  = self::SUPER_ADMIN_LOGIN;
            $this->email  = 'admin@activemedia.uz';
        }

        if ($this->isNewRecord) {
            $this->resource = [];
        }

        if ($this->isAttributeChanged('resource')) {
            $resources = [];
            foreach ($this->resource as $resource) {
                $resource             = trim($resource, ' /');
                $resources[$resource] = $resource;
                foreach (explode(',', $resource) as &$item) {
                    $resources[$item] = $item;
                }
            }
            $this->resource = array_values($resources);
        }

        TagDependency::invalidate(Yii::$app->cache, [self::CACHE_TAG_ADMIN_MENU]);

        return parent::beforeSave($insert);
    }

    public function canAccessToResource($path)
    {
        $path = trim($path, '/');
        return $this->isSuperAdmin || isset($this->resource[$path]) || is_array($this->resource) && in_array($path, $this->resource);
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

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        if ($this->search) {
            $query->orFilterWhere(['like', 'fullname', $this->search]);
            $query->orFilterWhere(['like', 'login', $this->search]);
            $query->orFilterWhere(['like', 'email', $this->search]);
        }

        return $dataProvider;
    }

    /**
     * @param $token
     * @return Admin
     */
    public static function findByPasswordResetToken($token)
    {
        $admin = static::findOne([
                                     'password_reset_token' => $token,
                                     'status'               => self::STATUS_ENABLE,
                                 ]);

        return $admin && $admin->isPasswordResetTokenValid() ? $admin : null;
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString();
        $this->password_reset_date  = new Expression('NOW()');
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
        $this->password_reset_date  = null;
    }

    public function isPasswordResetTokenValid()
    {
        if ($this->password_reset_date && $this->password_reset_token) {
            $expire = Yii::$app->params['admin.passwordResetTokenExpire'];

            return strtotime($this->password_reset_date) + $expire >= time();
        }

        return false;
    }


    public function getFullname()
    {
        return Html::encode($this->fullname ?: $this->login);
    }

}
