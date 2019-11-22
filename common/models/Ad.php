<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace common\models;

use MongoDB\BSON\Timestamp;
use Yii;

/**
 * Class Currency
 * @package common\models
 * @property string title
 * @property array  image
 * @property array  image_tablet
 * @property array  image_mobile
 * @property string status
 * @property string code
 * @property string type
 * @property string code_mobile
 * @property string url
 * @property mixed  date_from
 * @property mixed  date_to
 * @property string place
 * @property string user
 * @property string views
 * @property string clicks
 * @property string limit_click
 * @property string limit_view
 */
class Ad extends MongoModel
{
    protected $_integerAttributes    = ['limit_click', 'limit_view'];
    protected $_searchableAttributes = ['title'];
    protected $_timestampAttributes  = ['date_from', 'date_to'];

    public $clickable = false;

    const TYPE_IMAGE = 'image';
    const TYPE_CODE  = 'code';

    const STATUS_ENABLE        = 'enable';
    const STATUS_DISABLE       = 'disable';
    const STATUS_EXPIRE        = 'expire';
    const STATUS_PENDING       = 'pending';
    const STATUS_LIMITED_VIEW  = 'limited_view';
    const STATUS_LIMITED_CLICK = 'limited_click';

    public static function collectionName()
    {
        return 'ad';
    }

    public static function getTypeOptions()
    {
        return [
            self::TYPE_IMAGE => __('Image'),
            self::TYPE_CODE  => __('Code'),
        ];
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE        => __('Enable'),
            self::STATUS_DISABLE       => __('Disable'),
            self::STATUS_EXPIRE        => __('Expired'),
            self::STATUS_PENDING       => __('Pending'),
            self::STATUS_LIMITED_VIEW  => __('View Completed'),
            self::STATUS_LIMITED_CLICK => __('Click Completed'),
        ];
    }

    public function getStatusLabel()
    {
        $options = self::getStatusOptions();
        return isset($options[$this->status]) ? $options[$this->status] : $this->status;
    }

    public function attributes()
    {
        return [
            '_id',
            'title',
            'image',
            'image_tablet',
            'image_mobile',
            'code',
            'type',
            'code_mobile',
            'url',
            'date_from',
            'date_to',
            'limit_click',
            'limit_view',
            'status',
            'views',
            'clicks',
            'created_at',
            'updated_at',
        ];
    }

    public function rules()
    {
        return [
            [['title', 'status', 'type'], 'required', 'on' => [self::SCENARIO_INSERT, self::SCENARIO_UPDATE]],
            [['date_from', 'date_to', 'limit_click', 'limit_view'], 'safe'],
            [['limit_click', 'limit_view'], 'default', 'value' => 0],
            [['image', 'image_mobile'], 'safe'],
            [['code', 'code_mobile'], 'safe'],
            [['url'], 'url'],
            [['url'], 'safe'],
            [['search'], 'safe'],
        ];
    }

    public function beforeSave($insert)
    {
        return parent::beforeSave($insert);
    }


    public function getDateFromSeconds()
    {
        return $this->date_from instanceof Timestamp ? $this->date_from->getTimestamp() : $this->date_from;
    }


    public function getDateToSeconds()
    {
        return $this->date_to instanceof Timestamp ? $this->date_to->getTimestamp() : $this->date_to;
    }

    public function getUpdatedAtFormatted()
    {
        return Yii::$app->formatter->asDatetime($this->updated_at->getTimestamp(), 'php:d/M, H:i');
    }

    public function getRateDateFormatted()
    {
        if ($date = date_create_from_format('d.m.Y', $this->date)) {
            return mb_convert_case(Yii::$app->formatter->asDatetime($date->getTimestamp(), 'php:l, d/m/Y'), MB_CASE_TITLE, 'UTF-8');
        }
    }

    public function getImage($width, $height)
    {
        if ($this->image) {
            return self::getCropImage($this->image, $width, $height);
        }
        return false;
    }

    public function getDesktopImageUrl()
    {
        if ($this->image && is_array($this->image)) {
            return Yii::getAlias('@staticUrl/uploads/') . $this->image['path'] . '?t=' . $this->updated_at->getTimestamp();
        }

        return false;
    }

    public function getMobileImageUrl()
    {
        if ($this->image_mobile && is_array($this->image_mobile)) {
            return Yii::getAlias('@staticUrl/uploads/') . $this->image_mobile['path'] . '?t=' . $this->updated_at->getTimestamp();
        }

        return false;
    }


    public function checkStatus()
    {
        $from = is_object($this->date_from) ? $this->date_from->getTimestamp() : $this->date_from;
        $to   = is_object($this->date_to) ? $this->date_to->getTimestamp() : $this->date_to;

        $time = time();

        if (!empty($from)) {
            if (!empty($to)) {
                if ($from <= $time && $time <= $to) {
                    $this->updateAttributes(['status' => self::STATUS_ENABLE]);
                } elseif ($time > $to) {
                    $this->updateAttributes(['status' => self::STATUS_EXPIRE]);
                } elseif ($time < $from) {
                    $this->updateAttributes(['status' => self::STATUS_PENDING]);
                }
            } else {
                if ($time > $from) {
                    $this->updateAttributes(['status' => self::STATUS_ENABLE]);
                } else {
                    $this->updateAttributes(['status' => self::STATUS_PENDING]);
                }
            }
        } else {
            if (!empty($to)) {
                if ($time < $to) {
                    $this->updateAttributes(['status' => self::STATUS_ENABLE]);
                } else {
                    $this->updateAttributes(['status' => self::STATUS_EXPIRE]);
                }
            } else {
                if ($this->status != self::STATUS_DISABLE)
                    $this->updateAttributes(['status' => self::STATUS_ENABLE]);
            }
        }
    }

    public function checkLimit()
    {
        if ($this->limit_view && $this->views > $this->limit_view) {
            $this->updateAttributes(['status' => self::STATUS_LIMITED_VIEW]);
        }

        if ($this->limit_click && $this->clicks > $this->limit_click) {
            $this->updateAttributes(['status' => self::STATUS_LIMITED_CLICK]);
        }
    }

    public static function reindexStatuses()
    {
        echo "reindexStatuses====================\n";
        $ads = self::find()
                   ->where(['status' => ['$in' => [self::STATUS_ENABLE, self::STATUS_PENDING]]])
                   ->all();
        /** @var self[] $ads */

        foreach ($ads as $ad) {
            $ad->checkStatus();
            $ad->checkLimit();
        }
    }
}