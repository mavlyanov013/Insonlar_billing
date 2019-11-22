<?php

namespace common\models;

use common\components\Config;
use Imagine\Image\ManipulatorInterface;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\web\IdentityInterface;

/**
 * Class User
 * @property string          $fullname
 * @property string          $login
 * @property string          $password
 * @property string          $email
 * @property string          $telephone
 * @property string          $auth_key
 * @property string          $access_token
 * @property \MongoTimestamp $access_token_date
 * @property string          $password_reset_token
 * @property \MongoTimestamp $password_reset_date
 * @property string          $resource
 * @property string          $language
 * @property string          $status
 * @property string          name
 * @property string          image
 * @property mixed           twitter
 * @property mixed           facebook
 * @property mixed           google
 * @property mixed           avatar_url
 * @property Auth[]          authClients
 * @property Auth            authClient
 * @property Comment[]       comments
 */
class User extends MongoModel implements IdentityInterface
{
    public function attributes()
    {
        return [
            '_id',
            'fullname',
            'login',
            'password',
            'twitter',
            'facebook',
            'google',
            'email',
            'avatar_url',
            'telephone',
            'auth_key',
            'access_token',
            'access_token_date',
            'resource',
            'language',
            'status',
            'created_at',
            'updated_at',
            'password_reset_token',
            'password_reset_date',
            'image',
        ];
    }

    public function behaviors()
    {
        return parent::behaviors();
    }

    public $search;
    public $confirmation;
    public $change_password;
    public $isCenterAdmin = false;

    const STATUS_ENABLE  = 'enable';
    const STATUS_DISABLE = 'disable';
    const STATUS_BLOCKED = 'blocked';

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
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['image', 'safe', 'on' => ['upload']],

            [['fullname', 'email', 'status'], 'required', 'on' => ['insert', 'update']],

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
            [['login', 'email'], 'unique', 'on' => ['update', 'insert']],

            [['email'], 'email'],

            [['change_password'], 'safe'],

            [['fullname', 'password'], 'string', 'max' => 128],
            [['email'], 'string', 'max' => 64],
            [['telephone'], 'string', 'max' => 32],
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
     * @param $email
     * @return User|null
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email]);
    }

    /**
     * Finds user by attribute -> value
     * @param string $attribute
     * @param string $value
     * @return User|null
     */
    public static function findBy($attribute, $value)
    {
        return static::findOne([$attribute => $value, 'status' => self::STATUS_ENABLE]);
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    public function setPassword($password)
    {
        $this->password          = Yii::$app->security->generatePasswordHash($password);
        $this->auth_key          = Yii::$app->security->generateRandomString();
        $this->access_token      = Yii::$app->security->generateRandomString();
        $this->access_token_date = call_user_func($this->getTimestampValue());

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
        return static::findOne(['access_token' => $token, 'status' => self::STATUS_ENABLE]);
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
        return parent::afterFind();
    }

    public function beforeSave($insert)
    {
        if ($this->change_password || $this->isNewRecord) $this->setPassword($this->confirmation);

        if ($this->isAttributeChanged('fullname')) {
            $this->fullname = Html::encode($this->fullname);
        }
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
            $query->orFilterWhere(['like', 'fullname', $this->search]);
            $query->orFilterWhere(['like', 'login', $this->search]);
            $query->orFilterWhere(['like', 'email', $this->search]);
        }

        return $dataProvider;
    }

    /**
     * @param $token
     * @return User
     */
    public static function findByPasswordResetToken($token)
    {
        $user = static::findOne([
                                    'password_reset_token' => $token,
                                    'status'               => self::STATUS_ENABLE,
                                ]);

        return $user && $user->isPasswordResetTokenValid() ? $user : null;
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString();
        $this->password_reset_date  = call_user_func($this->getTimestampValue());
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
            $expire = Yii::$app->params['user.passwordResetTokenExpire'];

            return $expire + (($this->password_reset_date instanceof \MongoTimestamp) ? $this->password_reset_date->sec : intval($this->password_reset_date)) >= time();
        }

        return false;
    }


    public function getFullname()
    {
        return Html::decode($this->fullname ?: $this->email);
    }

    /**
     * @return \yii\db\ActiveQueryInterface
     */
    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['_user', 'id']);
    }

    public function afterDelete()
    {
        foreach ($this->comments as $comment) {
            $comment->delete();
        }
        foreach ($this->authClients as $client) {
            $client->delete();
        }
        parent::afterDelete(); // TODO: Change the autogenerated stub
    }

    public function login()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
        $this->save();
        return $this;
    }

    /**
     * @return \yii\db\ActiveQueryInterface
     */
    public function getAuthClient()
    {
        return $this->hasOne(Auth::className(), ['_user' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQueryInterface
     */
    public function getAuthClients()
    {
        return $this->hasMany(Auth::className(), ['_user' => 'id'])->indexBy('source');
    }

    public function getProfilePicture()
    {
        if ($img = $this->getCroppedImage()) {
            $link = $img;
        } else {
            $link = $this->getAvatarUrl();
        }

        return $link;
    }

    public static function getForPinging(Post $post)
    {
        $ids = array_map(function (Comment $comment) {
            return new \MongoId($comment->_user);
        }, $post->comments);
        /** @var self[] $all */
        $all   = self::find()
                     ->where(['status' => self::STATUS_ENABLE])
                     ->andWhere(['_id' => ['$in' => $ids]])
                     ->all();
        $users = [];
        foreach ($all as $user) {

            $users[] = [
                'id'                  => $user->getId(),
                'fullname'            => $user->getFullname(),
                'email'               => $user->email,
                'profile_picture_url' => $user->getProfilePicture(),
            ];
        }
        return $users;
    }

    public function saveImageFromSocial()
    {
        try {
            if (empty($this->image) && $this->avatar_url && strpos($this->avatar_url, 'static.blog.xabar.uz') === false) {
                if ($image = file_get_contents($this->avatar_url)) {
                    $path = Yii::getAlias('@static/uploads');

                    if ($this->authClient->source == 'facebook') {
                        $ext       = 'jpeg';
                        $imagePath = '1/' . Yii::$app->security->generateRandomString() . '.jpeg';
                    } else {
                        $ext       = explode('/', FileHelper::getMimeTypeByExtension($this->avatar_url));
                        $imagePath = '1/' . Yii::$app->security->generateRandomString() . '.' . $ext[1];
                    }

                    if (file_put_contents($path . DS . $imagePath, $image)) {
                        $img = [
                            'path'     => $imagePath,
                            'base_url' => Yii::getAlias('@staticUrl/uploads'),
                            'type'     => 'image/jpeg',
                            'name'     => StringHelper::basename($this->avatar_url),
                            'size'     => '',
                            'order'    => '',
                        ];
                        $this->updateAttributes([
                                                    'image' => $img,
                                                ]);
                        Yii::info(__('User image saved successfully'));
                        return true;
                    }
                    Yii::warning('Unable save user image :(');
                } else {
                    $this->updateAttributes([
                                                'avatar_url' => '',
                                            ]);
                }
            }
        } catch (\Exception $e) {
            $this->updateAttributes([
                                        'avatar_url' => '',
                                    ]);
            Yii::error($e->getMessage());
        }

        return false;
    }


    public function getCroppedImage($width = 200, $height = 200)
    {
        if ($this->image) {
            return parent::getCropImage($this->image, $width, $height, ManipulatorInterface::THUMBNAIL_OUTBOUND, false, 90);
        }

        return false;
    }


    public function getAvatarUrl()
    {
        if ($this->avatar_url) {
            return $this->avatar_url;
        }

        return Yii::$app->view->getImageUrl('user-icon.png');
    }
}
