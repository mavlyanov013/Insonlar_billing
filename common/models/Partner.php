<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace common\models;

use common\components\Config;

/**
 * Class Partner
 * @package common\models
 * @property string   description
 * @property string   status
 * @property string[] logo
 * @property mixed    name
 */
class Partner extends MongoModel
{
    public $_translatedAttributes = ['name', 'description'];
    public $_timestampAttributes  = ['from_date', 'to_date'];
    public $_integerAttributes    = ['position'];

    const STATUS_ENABLE  = 'enable';
    const STATUS_DISABLE = 'disable';

    public static function collectionName()
    {
        return 'partner';
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'name',
            'description',
            'address',
            'logo',
            'status',
            'position',
        ]);
    }

    public function rules()
    {
        return [
            [[
                 'name',
                 'logo',
                 'status',
             ], 'required'],
            [['status', 'logo', 'description'], 'safe'],
            [['status'], 'default', 'value' => self::STATUS_ENABLE],
            [['position'], 'number'],
        ];
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE  => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public function getStatusLabel()
    {
        $options = self::getStatusOptions();
        return isset($options[$this->status]) ? $options[$this->status] : $this->status;
    }

    public function beforeSave($insert)
    {
        return parent::beforeSave($insert);
    }

    public function getCroppedLogo($width = 200, $height = 200)
    {
        return parent::getCropImage($this->logo, $width, $height, 'inset', false, 95);
    }

    public function getLogo()
    {
        $img  = $this->logo;
        $path = preg_replace('/[\d]{2,4}_[\d]{2,4}_/', '', $img['path']);
        return \Yii::getAlias("@staticUrl/uploads/{$path}");
    }

    /**
     * @return self[]|array|\yii\mongodb\ActiveRecord
     */
    public static function getActive($shuffle = false)
    {
        $lang = Config::getLanguageShortName();

        $data = self::find()
                    ->where(['status' => self::STATUS_ENABLE])
                    ->orderBy(["position" => SORT_ASC])
                    ->all();

        if ($shuffle)
            shuffle($data);

        return $data;
    }
}