<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace common\models;

use common\components\Config;
use MongoDB\BSON\Timestamp;
use Yii;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\IdentityInterface;

/**
 * Class Volunteer
 * @property string   text
 * @property mixed    _appeal
 */
class AppealComment extends MongoModel
{
    protected $_translatedAttributes = [];

    public function attributes()
    {
        return [
            '_id',
            '_appeal',
            'text',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'appeal_comment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['text'], 'required'],

            [['text'], 'string', 'max' => 1024],

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

}
