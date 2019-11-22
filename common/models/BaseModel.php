<?php
namespace common\models;

use common\components\Config;
use Yii;
use yii\db\ActiveRecord;

class BaseModel extends ActiveRecord
{
    public $search;

    public static function getNameAttribute()
    {
        return 'name_' . Config::getLanguageShortName();
    }

    public $name;

    public function afterFind()
    {
        parent::afterFind();
        $lang       = self::getLangPrefix();
        $this->name = ($this->{"name_$lang"}) ?: $this->name_uz;
    }

    public static function getLangPrefix()
    {
        return substr(Yii::$app->language, 0, 2);
    }

    public function rules()
    {
        return [
            [['name', 'search'], 'safe']
        ];
    }
}