<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace common\models;

use common\components\Config;
use common\components\Translator;
use MongoDB\BSON\Timestamp;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;

/**
 * Class Tag
 * @package common\models
 * @property string  name_uz
 * @property string  name_cy
 * @property string  name_ru
 * @property string  slug
 * @property integer count_l5d
 * @property integer count
 * @property string  name
 * @property Post[]  posts
 */
class Tag extends MongoModel
{
    protected $_booleanAttributes    = ['is_topic'];
    protected $_integerAttributes    = ['count'];
    protected $_searchableAttributes = ['name', 'slug'];

    const SCENARIO_INSERT = 'insert';
    const SCENARIO_UPDATE = 'update';

    public static function getTagsAsOption()
    {
        return array_map(function (Tag $tag) {
            return [
                'v' => $tag->getId(),
                't' => $tag->name,
            ];
        }, Tag::find()->where([])->limit(20)->addOrderBy(['count_l5d' => SORT_DESC])->all());
    }

    public static function createTag($name)
    {

        if ($name = trim($name)) {
            $slug = Translator::getInstance()->translateToLatin($name);
            $slug = trim(preg_replace('/[^A-Za-z0-9-_]+/', '-', strtolower($slug)), '-');

            $old = self::find()
                       ->orFilterWhere(['$eq', 'name_uz', $name])
                       ->orFilterWhere(['$eq', 'name_cy', $name])
                       ->orFilterWhere(['$eq', 'name_ru', $name])
                       ->orFilterWhere(['$eq', 'slug', $slug])
                       ->one();
            if ($old) {
                return $old->_id;
            }

            $tag = new Tag();

            if (Yii::$app->language == Config::LANGUAGE_CYRILLIC) {
                $tag->name_cy = $name;
                $tag->name_uz = Translator::getInstance()->translateToLatin($name);
                $tag->name_ru = $name;
            } else if (Yii::$app->language == Config::LANGUAGE_UZBEK) {
                $tag->name_uz = $name;
                $tag->name_cy = Translator::getInstance()->translateToCyrillic($name);
                $tag->name_ru = $tag->name_cy;
            } else {
                $tag->name_uz = $name;
                $tag->name_cy = $name;
                $tag->name_ru = $name;
            }

            $tag->slug      = $slug;
            $tag->count_l5d = 0;

            if ($tag->save()) {
                return $tag->_id;
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [
            '_id',
            'name',
            'name_uz',
            'name_cy',
            'name_ru',
            'slug',
            'count',
            'count_l5d',
            'is_topic',
            'created_at',
            'updated_at',
        ];
    }

    public static function collectionName()
    {
        return 'tag';
    }

    public function rules()
    {
        return [
            [['count'], 'default', 'value' => 0],
            [['name_uz', 'name_ru', 'slug'], 'required'],
            [['slug', 'name_uz', 'name_ru'], 'unique'],
            [['name_uz', 'name_cy', 'name_ru', 'slug', 'is_topic'], 'safe', 'on' => ['insert', 'update']],
            [['is_topic'], 'default', 'value' => false],
            [['search'], 'safe', 'on' => 'search'],
            //[['name_uz'], 'match', 'pattern' => '/^[a-zA-Z0-9-]{3,32}$/', 'message' => __('Use friendly character')],
            // [['name_ru'], 'match', 'pattern' => '/^[а-яА-Я0-9-]{3,32}$/', 'message' => __('Use russian character')],
        ];
    }

    /**
     * @param     $params
     * @param int $pageSize
     * @return ActiveDataProvider
     */
    public function dataProvider($params, $pageSize = 10)
    {
        $query = self::find();


        $dataProvider = new ActiveDataProvider([
                                                   'query'      => $query,
                                                   'pagination' => [
                                                       'pageSize' => $pageSize,
                                                   ],
                                                   'sort'       => [
                                                       'defaultOrder' => 'is_topic',
                                                   ],
                                               ]);

        $this->load($params);

        return $dataProvider;
    }

    /**
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = self::find()
                     ->addOrderBy(['is_topic' => -1]);

        $dataProvider = new ActiveDataProvider([
                                                   'query'      => $query,
                                                   'sort'       => [
                                                       'defaultOrder' => ['count_l5d' => SORT_DESC],
                                                   ],
                                                   'pagination' => [
                                                       'pageSize' => 30,
                                                   ],
                                               ]);

        $this->load($params);
        if ($this->search) {
            $query->orFilterWhere(['like', 'name_uz', $this->search]);
            $query->orFilterWhere(['like', 'name_ru', $this->search]);
            $query->orFilterWhere(['like', 'name_cy', $this->search]);
        }

        return $dataProvider;
    }

    public function afterDelete()
    {
        /**
         * @var $post Post
         */
        $posts = Post::find()->all();
        foreach ($posts as $post) {
            $tags = $post->getConvertedTags();
            if (in_array($this->getId(), $tags)) {
                if (($key = array_search($this->_id, $tags)) !== false) {
                    unset($tags[$key]);
                    $post->updateAttributes(['_tags' => $tags]);
                }
            }
        }
    }

    public function beforeSave($insert)
    {
        if (!preg_match('/^[a-z][-a-z0-9]*$/', $this->slug)) {
            $slug       = Translator::getInstance()->translateToLatin($this->name);
            $this->slug = trim(preg_replace('/[^A-Za-z0-9-_]+/', '-', strtolower($slug)), '-');
        }

        $this->name = $this->name_uz;
        return parent::beforeSave($insert);
    }

    public function afterFind()
    {
        $att        = 'name_' . Config::getLanguageShortName();
        $this->name = $this->$att;
        parent::afterFind();
    }

    /**
     * @return Post[]
     */
    public function getPosts()
    {
        return Post::find()
                   ->where(['_tags' => ['$elemMatch' => ['$in' => [$this->getId()]]]])
                   ->all();
    }

    public function getViewUrl($scheme = false)
    {
        return Url::to(['tag/' . $this->slug], $scheme);
    }

    public static function indexAllTags()
    {
        /**
         * @var $post Post
         * @var $tag  Tag
         */

        $posts = Post::find()
                     ->where(['status' => Post::STATUS_PUBLISHED])
                     ->orderBy(['published_on' => SORT_DESC])
                     ->all();

        self::updateAll(['count' => 0]);

        foreach ($posts as $post) {
            foreach ($post->getTags() as $tag) {
                $tag->updateCounters(['count' => 1]);
            }
        }
    }

    public static function indexTrendingTags()
    {
        echo "indexTrendingTags===================\n";
        /**
         * @var $post Post
         * @var $tag  Tag
         */
        $posts = Post::find()
                     ->where(['status' => Post::STATUS_PUBLISHED])
                     ->where([
                                 'published_on' => ['$gt' => new Timestamp(1, time() - 5 * 24 * 3600)],
                             ])
                     ->orderBy(['published_on' => SORT_DESC])
                     ->all();

        self::updateAll(['count_l5d' => 0]);

        foreach ($posts as $post) {
            foreach ($post->getTags() as $tag) {
                $tag->updateCounters(['count_l5d' => 1]);
            }
        }
    }

    /**
     * @param int $limit
     * @return self|array|\yii\mongodb\ActiveRecord
     */
    public static function getTrending($limit = 10)
    {
        return self::find()
                   ->where(['count_l5d' => ['$gt' => 0]])
                   ->orderBy(['count_l5d' => SORT_DESC])
                   ->limit($limit)
                   ->all();
    }

    /**
     * @param int $limit
     * @return self|array|\yii\mongodb\ActiveRecord
     */
    public static function getMostUsed($limit = 10)
    {
        return self::find()
                   ->where(['count' => ['$gt' => 3]])
                   ->orderBy(['count' => SORT_DESC])
                   ->limit($limit)
                   ->all();
    }
}