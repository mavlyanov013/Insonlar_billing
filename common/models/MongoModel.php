<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace common\models;


use common\components\Config;
use common\components\InterlacedImage;
use common\components\Translator;
use DateTime;
use Imagine\Image\ManipulatorInterface;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Timestamp;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\mongodb\ActiveRecord;

/**
 * Class MongoModel
 * @property ObjectID _id
 * @property string   id
 * @property string[] _translations
 * @property mixed    created_at
 * @property mixed    created_by
 * @property mixed    updated_at
 * @property mixed    updated_by
 * @package common\models
 */
class MongoModel extends ActiveRecord
{
    public $search;

    protected $_searchableAttributes = [];
    protected $_booleanAttributes    = [];
    protected $_integerAttributes    = [];
    protected $_doubleAttributes     = [];
    protected $_translatedAttributes = [];
    protected $_timestampAttributes  = [];
    protected $_idAttributes         = [];

    const SCENARIO_INSERT = 'insert';
    const SCENARIO_UPDATE = 'update';


    public function getBooleanAttributes()
    {
        return $this->_booleanAttributes;
    }

    public final function getTimestampValue()
    {
        return function () {
            $dt = new DateTime();
            return new Timestamp(1, $dt->getTimestamp());
        };
    }

    public function attributes()
    {
        return [
            '_id',
            '_translations',
            'created_at',
            'updated_at',
        ];
    }

    public function rules()
    {
        return [
            ['search', 'safe', 'on' => 'search'],
            ['_id', 'yii\mongodb\validators\MongoIdValidator'],
            [['created_at', 'updated_at'], 'yii\mongodb\validators\MongoDateValidator'],
        ];
    }

    public function attributeLabels()
    {
        $labels = [];
        foreach ($this->attributes() as $attribute) {
            $labels[$attribute] = __(Inflector::camel2words($attribute));
        }
        return $labels;
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'value' => $this->getTimestampValue(),
            ],
        ];
    }

    public function getId()
    {
        return (string)$this->_id;
    }

    public function search($params)
    {
        $this->load($params);
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
                                                   'query'      => $query,
                                                   'sort'       => [
                                                       'defaultOrder' => ['created_at' => SORT_DESC],
                                                   ],
                                                   'pagination' => [
                                                       'pageSize' => 50,
                                                   ],
                                               ]);


        if ($this->search) {
            foreach ($this->_searchableAttributes as $attribute) {
                $query->orFilterWhere(['like', $attribute, $this->search]);
            }
        }
        return $dataProvider;

    }

    public function beforeSave($insert)
    {
        foreach ($this->getAttributes() as $attribute => $value) {
            if ($this->isAttributeChanged($attribute)) {
                if (in_array($attribute, $this->_doubleAttributes)) {
                    $this->setAttribute($attribute, doubleval($value));
                } else if (in_array($attribute, $this->_booleanAttributes)) {
                    $this->setAttribute($attribute, boolval($value));
                } else if (in_array($attribute, $this->_integerAttributes)) {
                    $this->setAttribute($attribute, intval($value));
                } else if (
                    in_array($attribute, $this->_idAttributes)
                    && !($value instanceof ObjectId)
                    && strlen($value) == 24
                ) {
                    $this->setAttribute($attribute, new ObjectId(trim($value)));
                } else if (
                    in_array($attribute, $this->_timestampAttributes)
                    && !($value instanceof Timestamp)
                    && strlen($value) == 10
                ) {
                    $this->setAttribute($attribute, new Timestamp(1, intval($value)));
                }
            }
        }

        if ($this->hasAttribute('_translations')) {
            $translations = $this->_translations;
            $language     = Config::getLanguageShortName();
            foreach ($this->_translatedAttributes as $attributeCode) {
                $translations[$attributeCode . '_' . $language] = $this->getAttribute($attributeCode);
            }

            $this->_translations = $translations;
        }

        return parent::beforeSave($insert);
    }


    public function afterFind()
    {
        if ($this->hasAttribute('_translations')) {
            $translations = $this->_translations;
            $language     = Config::getLanguageShortName();

            foreach ($this->_translatedAttributes as $attributeCode) {
                $t = $attributeCode . '_' . $language;
                if (isset($translations[$t])) {
                    $this->setAttribute($attributeCode, $translations[$t]);
                }
            }
        }

        parent::afterFind();
    }

    public function setTranslation($attributeCode, $value, $language)
    {
        if ($this->hasAttribute('_translations')) {
            if (strlen($language) > 2) $language = substr($language, 0, 2);
            $translations                                   = $this->_translations;
            $translations[$attributeCode . '_' . $language] = $value;
            $this->_translations                            = $translations;
        }
    }

    public function getTranslation($attributeCode, $language)
    {
        if ($this->hasAttribute('_translations')) {
            if (strlen($language) > 2) $language = substr($language, 0, 2);

            $t            = $attributeCode . '_' . $language;
            $translations = $this->_translations;
            if (isset($translations[$t])) {
                return $translations[$t];
            }
        }

        return $this->$attributeCode;
    }

    public function getAllTranslations($attributeCode)
    {
        $result = [];
        foreach (Config::getLanguageOptions() as $language => $languageOption) {
            $result[$language] = $this->getTranslation($attributeCode, $language);
        }

        return $result;
    }

    protected $_relations = [];

    protected function getRelatedModel($attribute, $class)
    {
        if (!isset($this->_relations[$attribute])) {
            if ($this->hasAttribute($attribute) && $this->$attribute) {
                $model                        = new $class;
                $this->_relations[$attribute] = $model::findOne((string)$this->$attribute);
            } else {
                return null;
            }
        }
        return $this->_relations[$attribute];
    }


    public function dataToImage($match)
    {
        list(, $img, $type, $base64, $end) = $match;
        $bin  = base64_decode($base64);
        $name = uniqid() . '.' . $type;

        $path = chr(96 + rand(1, 26)) . DS . chr(96 + rand(1, 26)) . DS;
        $dir  = Yii::getAlias("@static/uploads/") . DS;

        if (!is_dir($dir . $path)) {
            FileHelper::createDirectory($dir . $path, 0777);
        }

        file_exists($dir . $path . $name) or file_put_contents($dir . $path . $name, $bin);

        $url = Yii::getAlias("@staticUrl/uploads/") . $path . $name;

        return "$img$url$end";
    }


    public static function getCropImage($img = [], $width = 270, $height = 347, $manipulation = ManipulatorInterface::THUMBNAIL_INSET, $watermark = false, $quality = 80)
    {
        $dir     = \Yii::getAlias("@static") . DS . 'uploads' . DS;
        $cropDir = \Yii::getAlias("@static") . DS . 'crop' . DS;

        $img = (array)$img;

        if (!empty(array_filter($img, 'is_array'))) {
            //$img = array_shift(array_filter($img, 'is_array'));
        }

        if (!is_dir($cropDir)) {
            FileHelper::createDirectory($cropDir, 0777);
        }

        if (!empty($img) && is_array($img) && isset($img['path'])) {

            $img['path'] = preg_replace('/[\d]{2,4}_[\d]{2,4}_/', '', $img['path']);
            $imagePath   = $dir . $img['path'];
            $info        = pathinfo($imagePath);

            $imageName = crc32($img['name']) . '.' . $info['extension'];

            $cropPath = $imageName[0] . DS . $imageName[1] . DS;
            $cropName = $width . '_' . $height . '_' . $quality . '_' . $imageName;
            $cropFull = $cropDir . $cropPath . $cropName;

            $cropUrl = \Yii::getAlias('@staticUrl/crop/') . $cropPath . $cropName;

            if (!file_exists($cropFull)) {
                if (!is_dir($cropDir . $cropPath)) {
                    FileHelper::createDirectory($cropDir . $cropPath, 0777);
                }

                if (file_exists($imagePath)) {
                    if ($watermark) {
                        InterlacedImage::thumbnailWithWatermark($imagePath, $width, $height, $manipulation)
                                       ->save($cropFull, ['quality' => $quality]);
                    } else {
                        InterlacedImage::thumbnail($imagePath, $width, $height, $manipulation)
                                       ->save($cropFull, ['quality' => $quality]);
                    }
                }
            }
            return $cropUrl;
        } else {
            return false;
        }
    }


    protected function convertLatinQuotas($value)
    {
        if (is_string($value)) {
            return $this->_convertLatinQuotas($value);
        } elseif (is_array($value)) {
            $result = [];

            foreach ($value as $key => $item) {
                $result[$key] = $this->_convertLatinQuotas($item);
            }

            return $result;
        }

        return $value;
    }

    private function _convertLatinQuotas($value)
    {
        foreach ([
                     "o'"       => "o‘",
                     "o`"       => "o‘",
                     "o’"       => "o‘",
                     "o&rsquo;" => "o‘",
                     "o&lsquo;" => "o‘",
                     "O'"       => "O‘",
                     "O`"       => "O‘",
                     "O’"       => "O‘",
                     "O&rsquo;" => "O‘",
                     "O&lsquo;" => "O‘",
                     "g'"       => "g‘",
                     "g`"       => "g‘",
                     "g’"       => "g‘",
                     "g&rsquo;" => "g‘",
                     "g&lsquo;" => "g‘",
                     "G'"       => "G‘",
                     "G`"       => "G‘",
                     "G’"       => "G‘",
                     "G&rsquo;" => "G‘",
                     "G&lsquo;" => "G‘",
                     "`"        => "’",
                     "'"        => "’",
                     "&lsquo;"  => "’",
                     "&rsquo;"  => "’",
                 ] as $s => $r) {
            $value = str_replace($s, $r, $value);
        }
        return $value;
    }


    public function syncLatinCyrill($toLanguage, $update = false)
    {
        if (Config::isLatinCyrill() && $this->hasAttribute('_translations')) {
            if ($toLanguage == Config::LANGUAGE_UZBEK) {
                foreach ($this->_translatedAttributes as $attribute) {
                    $value = $this->getTranslation($attribute, Config::LANGUAGE_CYRILLIC);
                    $value = Translator::getInstance()->translateToLatin($value);
                    $this->setTranslation($attribute, $value, Config::LANGUAGE_UZBEK);
                }
                if ($update)
                    $this->updateAttributes(['_translations' => $this->_translations]);

                return 1;
            } elseif ($toLanguage == Config::LANGUAGE_CYRILLIC) {
                foreach ($this->_translatedAttributes as $attribute) {
                    $value = $this->getTranslation($attribute, Config::LANGUAGE_UZBEK);
                    $value = Translator::getInstance()->translateToCyrillic($value);
                    $this->setTranslation($attribute, $value, Config::LANGUAGE_CYRILLIC);
                }
                if ($update)
                    $this->updateAttributes(['_translations' => $this->_translations]);

                return 1;
            }
        }
    }


    public function getFileUrl($attribute)
    {
        if ($this->hasAttribute($attribute)) {
            $attribute = $this->$attribute;
            if (is_array($attribute) && isset($attribute['path']))
                return Yii::getAlias("@staticUrl/uploads/") . $attribute['path'];
        }

        return false;
    }

    public function getFilePath($attribute)
    {
        if ($this->hasAttribute($attribute)) {
            $attribute = $this->$attribute;
            if (is_array($attribute) && isset($attribute['path']))
                return Yii::getAlias("@static/uploads/") . $attribute['path'];
        }

        return false;
    }

    protected static $_alpha = [
        '1', '2', '3', '4', '5', '6', '7', '8', '9',
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i',
        'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'j',
        's', 'u', 'u', 'v', 'w', 'x', 'y', 'z', '0',
    ];

    public static function offerRandomSequence($length = 3)
    {
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= self::$_alpha[rand(0, count(self::$_alpha) - 1)];
        }

        return $result;
    }

    public function getTimeSeconds($att)
    {
        return $this->$att instanceof Timestamp ? $this->$att->getTimestamp() : (int)$this->$att;
    }
}