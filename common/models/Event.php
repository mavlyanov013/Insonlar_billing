<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace common\models;

use Imagine\Image\ManipulatorInterface;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;

/**
 * Class Event
 * @package common\models
 * @property string   description
 * @property string   address
 * @property string   status
 * @property string[] image
 * @property mixed    from_date
 * @property mixed    to_date
 * @property mixed    coordinates
 * @property mixed    gallery
 * @property mixed    assigner_phone
 * @property mixed    name
 * @property mixed    content
 */
class Event extends MongoModel
{
    public $_translatedAttributes = ['name', 'description', 'address', 'content'];
    public $_timestampAttributes  = ['from_date', 'to_date'];

    const STATUS_ENABLE  = 'enable';
    const STATUS_DISABLE = 'disable';
    const STATUS_EXPIRE  = 'expire';

    public static function collectionName()
    {
        return 'event';
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'name',
            'description',
            'address',
            'image',
            'from_date',
            'to_date',
            'coordinates',
            'gallery',
            'status',
            'content'
            //'assigner_name',
            //'assigner_phone',
        ]);
    }

    public function rules()
    {
        return [
            [[
                 'name',
                 'description',
                 'address',
                 'image',
                 //'coordinates',
                 //'assigner_name',
                 //'assigner_phone',
                 'from_date',
             ], 'required'],
            [['status', 'image', 'gallery', 'content', 'from_date', 'to_date'], 'safe'],
        ];
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE  => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
            self::STATUS_EXPIRE  => __('Expired'),
        ];
    }

    public function getStatusLabel()
    {
        $options = self::getStatusOptions();
        return isset($options[$this->status]) ? $options[$this->status] : $this->status;
    }

    public function beforeSave($insert)
    {
        if (empty($this->to_date) || strlen($this->to_date) != 10) {
            $this->to_date = (int)$this->from_date + 86400;
        }
        return parent::beforeSave($insert);
    }


    private function processGallery($function)
    {
        if (is_array($this->gallery) && count($this->gallery)) {
            $files = [];
            foreach ($this->gallery as $item) {
                $item['order'] = intval($item['order']);
                //todo process images
                if ($function != null) {
                    $files[] = call_user_func($function, $item);
                } else {
                    $files[] = $item;
                }
            }
            $this->gallery = $files;
        }
    }

    public static function dataProvider($limit = 10)
    {
        $query = self::find()
                     ->orderBy(['from_date' => SORT_DESC])
                     ->andWhere(['status' => self::STATUS_ENABLE]);

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

    public function getViewUrl($scheme = false)
    {
        return Url::to(['event/view', 'id' => $this->id], $scheme);
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