<?php

namespace common\models;

use common\components\Config;
use common\components\Translator;
use DateTime;
use GuzzleHttp\Client;
use Imagine\Image\ManipulatorInterface;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\Timestamp;
use Yii;
use yii\base\Event;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\web\Application;

/**
 * Class Post
 * @property string     title
 * @property string     title_color
 * @property string     content
 * @property string     content_source
 * @property string     url
 * @property string     status
 * @property array      image
 * @property array      image_source
 * @property mixed      info
 * @property mixed      type
 * @property mixed      label
 * @property mixed      _tags
 * @property mixed      _similar
 * @property mixed      _similar_indexed
 * @property mixed      gallery
 * @property mixed      _categories
 * @property Category[] categories
 * @property Category   category
 * @property Tag[]      tags
 * @property mixed      video
 * @property mixed      youtube_url
 * @property mixed      mover_url
 * @property mixed      published_on
 * @property mixed      updated_on
 * @property Timestamp  pushed_on
 * @property Timestamp  pushed_on_ios
 * @property mixed      author_type
 * @property mixed      video_url
 * @property mixed      _author
 * @property mixed      _editor
 * @property integer    short_id
 * @property boolean    has_video
 * @property boolean    has_gallery
 * @property boolean    has_russian
 * @property boolean    has_info
 * @property boolean    is_main
 * @property boolean    is_push
 * @property boolean    mobile_image
 * @property integer    views
 * @property integer    views_l3d
 * @property integer    views_l7d
 * @property integer    views_l30d
 * @property integer    views_today
 * @property integer    comment_count
 * @property integer    gallery_items
 * @package common\models
 */
class Post extends MongoModel
{
    protected $_translatedAttributes = ['title', 'content', 'info', 'image_source'];
    protected $_booleanAttributes    = ['has_video', 'has_gallery', 'has_russian', 'is_main'];
    protected $_integerAttributes    = ['views'];
    protected $_searchableAttributes = ['title', 'info', 'category'];

    const LABEL_REGULAR       = 'regular';
    const LABEL_IMPORTANT     = 'important';
    const LABEL_EDITOR_CHOICE = 'editor_choice';
    const STATUS_DRAFT        = 'draft';
    const STATUS_PUBLISHED    = 'published';
    const STATUS_DISABLED     = 'disabled';
    const STATUS_IN_TRASH     = 'in_trash';

    const TYPE_NEWS    = 'news';
    const TYPE_GALLERY = 'gallery';
    const TYPE_VIDEO   = 'video';

    const SCENARIO_NEWS    = 'news';
    const SCENARIO_GALLERY = 'gallery';
    const SCENARIO_VIDEO   = 'video';
    const SCENARIO_CREATE  = 'create';

    const SOCIAL_FACEBOOK = 'facebook';
    const SOCIAL_TWITTER  = 'twitter';
    const SOCIAL_TELEGRAM = 'telegram';

    public $user;
    public $post_type;
    public $image_caption;
    public $category;
    public $has_updates;

    public static function collectionName()
    {
        return 'post';
    }

    public static function cyLatAttributes()
    {
        return [
            'title',
            'info',
            'content',
            'image_source',
        ];
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'title',
            '_categories',
            'info',
            'status',
            'content',
            'url',
            'type',
            '_author',
            'author_type',

            'published_on',
            'updated_on',
            'image',
            'label',
            '_tags',
            '_editor',
            '_related',
            '_similar',
            '_similar_indexed',
            'views',

            'title_color',
            'image_source',
            'content_source',
            'video',
            'youtube_url',
            'mover_url',
            'gallery',
            'gallery_items',
            'mobile_image',

            'short_id',
            'has_russian',
            'has_video',
            'has_gallery',
            'is_main',
            'video_url',
        ]);
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        return $behaviors;
    }

    public function rules()
    {
        return [
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            [['views'], 'default', 'value' => 0],
            [['type'], 'in', 'range' => array_keys(self::getTypeArray())],

            [['url'], 'unique'],
            [['info', 'image_source', 'content_source'], 'string', 'max' => 500],
            [['info', 'image_source', 'content_source'], 'safe', 'on' => self::SCENARIO_CREATE],

            [['title', 'info', 'content', 'image', 'status', '_author', 'gallery', 'info', '_categories', '_tags', 'video', 'has_russian', 'published_on', 'is_main'],
             'safe', 'on' => [self::SCENARIO_NEWS, self::SCENARIO_GALLERY, self::SCENARIO_VIDEO]],

            [['url'], 'match', 'skipOnEmpty' => true, 'pattern' => '/^[a-z0-9-]{3,255}$/', 'message' => __('Use URL friendly character')],
            [['youtube_url'], 'validateYoutube', 'message' => __('Invalid youtube url')],
            [['mover_url'], 'validateMover', 'skipOnEmpty' => true, 'message' => __('Invalid Mover url')],
            [['search', 'user', 'post_type'], 'safe', 'on' => 'search'],

            [['title', 'url', 'info', '_categories'], 'required', 'on' => [self::SCENARIO_NEWS, self::SCENARIO_GALLERY, self::SCENARIO_VIDEO], 'when' => function ($model) {
                return $model->status != self::STATUS_DRAFT;
            }],

            [['content'], 'required', 'on' => [self::SCENARIO_NEWS], 'when' => function ($model) {
                return $model->status != self::STATUS_DRAFT;
            }],

            [['gallery'], 'required', 'on' => [self::SCENARIO_GALLERY], 'when' => function ($model) {
                return $model->status != self::STATUS_DRAFT;
            }],

            [['youtube_url'], 'required', 'on' => [self::SCENARIO_VIDEO], 'when' => function ($model) {
                return empty($model->mover_url);
            }],

            [['mover_url'], 'required', 'on' => [self::SCENARIO_VIDEO], 'when' => function ($model) {
                return  empty($model->youtube_url);
            }],

            [['status'], 'in', 'range' => array_keys(self::getStatusArray()), 'on' => [self::SCENARIO_GALLERY, self::SCENARIO_VIDEO, self::SCENARIO_NEWS]],

            [['title'], 'string', 'max' => 512],
            [['url'], 'string', 'max' => 64],
            [['short_id'], 'safe', 'on' => [self::SCENARIO_NEWS, self::SCENARIO_GALLERY, self::SCENARIO_VIDEO], 'when' => function ($model) {
                return $model->status != self::STATUS_DRAFT;
            }],
        ];
    }

    public function validateYoutube($attribute, $options)
    {
        if ($value = $this->$attribute) {
            $parts = parse_url($value);
            if (isset($parts['host']) && isset($parts['path']) && isset($parts['query'])) {
                if (strpos($parts['host'], 'youtube.com') && $parts['path'] == '/watch' && strpos($parts['query'], 'v=') === 0) {
                    return true;
                }
            }
            $this->addError($attribute, __('Invalid youtube url'));
        }
    }

    public function validateMover($attribute, $options)
    {
        if ($value = $this->$attribute) {
            $parts = parse_url($value);

            if (isset($parts['host']) && isset($parts['path'])) {
                if ($parts['host'] == 'mover.uz' && strpos($parts['path'], '/watch') === 0) {
                    return true;
                }
            }
            $this->addError($attribute, __('Invalid mover url'));
        }
    }

    public static function getColorOptions()
    {
        return [
            '#000000' => __('Black'),
            '#ff0000' => __('Red'),
            '#00ff00' => __('Green'),
            '#0000ff' => __('Blue'),
        ];
    }

    public static function getStatusArray()
    {
        return [
            self::STATUS_DRAFT        => __('Draft'),
            self::STATUS_PUBLISHED    => __('Published'),
            self::STATUS_DISABLED     => __('Disabled'),
        ];
    }

    public static function getLabelArray($empty = false)
    {
        $options = [
            self::LABEL_REGULAR       => __('Regular News'),
            self::LABEL_IMPORTANT     => __('Important News'),
            self::LABEL_EDITOR_CHOICE => __('Editor\'s Choice'),
        ];


        return $empty ? array_merge(['' => ''], $options) : $options;
    }

    public static function getSocialArray()
    {
        return [
            self::SOCIAL_FACEBOOK => __('Facebook'),
            self::SOCIAL_TWITTER  => __('Twitter'),
            self::SOCIAL_TELEGRAM => __('Telegram'),
        ];
    }

    public static function getTypeArray()
    {
        return [
            self::TYPE_NEWS    => __('News'),
            self::TYPE_GALLERY => __('Gallery'),
            self::TYPE_VIDEO   => __('Video'),
        ];
    }

    public function getTypeLabel()
    {
        $status = self::getTypeArray();
        return isset($status[$this->type]) ? $status[$this->type] : $this->type;
    }

    public function getLabelLabel()
    {
        $status = self::getLabelArray();
        return isset($status[$this->label]) ? $status[$this->label] : $this->label;
    }

    public function getStatusLabel()
    {
        $status = self::getStatusArray();
        return isset($status[$this->status]) ? $status[$this->status] : $this->status;
    }

    public function search($params = [], $type = false)
    {
        $this->load($params);
        $query = self::find()
                     ->with('categories');

        $dataProvider = new ActiveDataProvider([
                                                   'query'      => $query,
                                                   'sort'       => [
                                                       'defaultOrder' => $this->status == self::STATUS_DRAFT ?
                                                           [
                                                               'created_at' => SORT_DESC,
                                                           ] : [
                                                               'published_on' => SORT_DESC,
                                                           ],
                                                   ],
                                                   'pagination' => [
                                                       'pageSize' => 30,
                                                   ],
                                               ]);


        if ($this->search) {
            $query->orFilterWhere(['_translations.title_uz' => ['$regex' => $this->search, '$options' => 'si']]);
            $query->orFilterWhere(['_translations.title_cy' => ['$regex' => $this->search, '$options' => 'si']]);
            $query->orFilterWhere(['_translations.title_ru' => ['$regex' => $this->search, '$options' => 'si']]);
            $query->orFilterWhere(['slug' => ['$regex' => $this->search, '$options' => 'si']]);
            $query->orFilterWhere(['short_id' => ['$regex' => $this->search, '$options' => 'si']]);
        }
        if ($this->status) {
            $query->andFilterWhere(['status' => $this->status]);
        }
        if ($this->post_type) {
            $query->andFilterWhere(['type' => $this->post_type]);
        }
        if ($this->label) {
            $query->andFilterWhere(['label' => $this->label]);
        }

        $query->andFilterWhere(['status' => ['$ne' => self::STATUS_IN_TRASH]]);

        return $dataProvider;
    }

    public function searchTrash($params = [])
    {
        $this->load($params);
        $query = self::find()
                     ->with('categories');

        $dataProvider = new ActiveDataProvider([
                                                   'query'      => $query,
                                                   'sort'       => [
                                                       'defaultOrder' => [
                                                           'published_on' => SORT_DESC,
                                                       ],
                                                   ],
                                                   'pagination' => [
                                                       'pageSize' => 30,
                                                   ],
                                               ]);

        if ($this->search) {
            $query->orFilterWhere(['_translations.title_uz' => ['$regex' => $this->search, '$options' => 'si']]);
            $query->orFilterWhere(['_translations.title_cy' => ['$regex' => $this->search, '$options' => 'si']]);
            $query->orFilterWhere(['_translations.title_ru' => ['$regex' => $this->search, '$options' => 'si']]);
        }
        $query->andFilterWhere(['status' => ['$eq' => self::STATUS_IN_TRASH]]);


        return $dataProvider;
    }

    public function afterDelete()
    {
        if ($image = $this->image) {
            $dir = Yii::getAlias('@static/uploads');
            if (isset($image['path']) && file_exists($dir . DS . $image['path'])) {
                unlink($dir . DS . $image['path']);
            }
        }
        parent::afterDelete();
    }

    public function updatePost($force = false)
    {

        if ($this->status == self::STATUS_PUBLISHED && !$this->published_on) {
            $this->published_on = call_user_func($this->getTimestampValue());
        }

        if (is_numeric($this->published_on)) {
            $this->published_on = new Timestamp(1, intval($this->published_on));
        }

        if ($this->has_updates) {
            $this->updated_on = call_user_func($this->getTimestampValue());
        }


        $this->has_video   = $this->type == self::TYPE_VIDEO;
        $this->has_gallery = $this->type == self::TYPE_GALLERY;

        $this->processGallery(false);
        $this->processContent($force);
        $this->_similar_indexed = false;

        return $this->save();
    }

    public function beforeSave($insert)
    {
        if (!(Yii::$app instanceof \yii\console\Application)) {
            if (empty($this->_editor) && Yii::$app->user->identity instanceof Admin) {
                $this->_editor = Yii::$app->user->identity->getId();
            }
        }

        if (!$this->short_id && $this->status == self::STATUS_PUBLISHED) {
            $attempt = 0;
            do {
                $attempt++;
                $code           = self::offerRandomSequence(3 + round($attempt / 10));
                $this->short_id = $code;
            } while (self::find()->where(['short_id' => $code])->count() > 0);
        }

        if (is_string($this->_categories))
            $this->_categories = explode(',', $this->_categories);

        if (is_string($this->_tags)) {
            $this->_tags = $this->getConvertedTagsWithCreate();

            if ($this->isAttributeChanged('_tags'))
                $this->indexSimilarPosts();
        }

        if (!$this->image && ($this->status == self::STATUS_PUBLISHED)) {
            /*$this->image = [
                'path'     => '1/logo-placeholder.jpg',
                'name'     => 'logo-placeholder.jpg',
                'base_url' => 'http://static.xabar.uz/uploads',
                'type'     => 'image/jpeg',
                'caption'  => [
                    'uz' => 'Saxovat.uz',
                    'cy' => 'Saxovat.uz',
                    'ru' => 'Saxovat.uz',
                ],
            ];*/
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

    public function getConvertedTagsWithCreate()
    {
        $tags = array_filter(explode(',', $this->_tags));

        if (count($tags) > 0)
            return array_filter(array_map(function ($id) {
                if (preg_match('/[a-z0-9]{24}/', $id)) {
                    return new ObjectID(trim($id));
                } else {
                    $id = Tag::createTag($id);
                    return $id;
                }
            }, $tags));

        return [];
    }

    public function getConvertedTags()
    {
        $tags = array_filter(explode(',', $this->_tags));

        if (count($tags) > 0)
            return array_map(function ($id) {
                return new ObjectID(trim($id));
            }, $tags);

        return [];
    }

    public function afterFind()
    {
        if (empty($this->category)) {
            $this->category = Category::findOne($this->_categories);
        }
        if (is_array($this->_categories))
            $this->_categories = implode(',', $this->_categories);

        if (is_array($this->_tags))
            $this->_tags = implode(',', $this->_tags);

        if ($this->image && is_array($this->image)) {
            $this->image_caption = $this->getImageCaption($this->image);
            $image               = $this->image;
            $image['base_url']   = Yii::getAlias('@staticUrl/uploads');
            $this->image         = $image;
        }

        if (is_numeric($this->published_on))
            $this->published_on = new Timestamp(1, $this->published_on);

        parent::afterFind();
    }

    public function getImageCaption($image)
    {
        $lang = Config::getLanguageShortName();
        if (isset($image['caption']) && isset($image['caption'][$lang])) {
            return $image['caption'][$lang];
        }
        return '';
    }

    public function processContent($force = false)
    {
        $this->title = trim($this->title);
        $this->url   = trim($this->url, ' -');
        $this->url   = str_replace('--', '-', $this->url);
        $this->url   = str_replace('--', '-', $this->url);

        if ($this->isAttributeChanged('content') || $force) {
            if ($content = $this->content) {
                $content = preg_replace_callback('#(<img\s(?>(?!src=)[^>])*?src=")data:image/(gif|png|jpeg|jpg);base64,([\w=+/]++)("[^>]*>)#', function ($matches) {
                    return $this->dataToImage($matches);
                }, $content);

                $content = preg_replace('/(<[^>]+) width=".*?"/i', '$1', $content);
                $content = preg_replace('/(<[^>]+) height=".*?"/i', '$1', $content);
                $content = preg_replace('/(<table[^>]*) style=("[^"]+"|\'[^\']+\')([^>]*>)/i', '$1$3', $content);;
                $content = preg_replace('/(<tr[^>]*) style=("[^"]+"|\'[^\']+\')([^>]*>)/i', '$1$3', $content);;
                $content = preg_replace('/(<td[^>]*) style=("[^"]+"|\'[^\']+\')([^>]*>)/i', '$1$3', $content);;

                $this->content = $content;
            }
        }

        if (Yii::$app->language == Config::LANGUAGE_UZBEK) {
            foreach (self::cyLatAttributes() as $attribute) {
                if ($this->isAttributeChanged($attribute) || $force) {
                    $this->{$attribute} = $this->convertLatinQuotas($this->{$attribute});
                }
            }

            if ($data = $this->image) {
                if (is_array($data) && is_array($data['caption'])) {
                    if (isset($data['caption']['uz'])) {
                        $data['caption']['uz'] = $this->convertLatinQuotas($data['caption']['uz']);
                    }
                }
                $this->image = $data;
            }

            if ($this->type == self::TYPE_GALLERY)
                $this->processGallery(function ($item) {
                    if (is_array($item) && isset($item['caption']) && isset($item['caption']['uz'])) {
                        $item['caption']['uz'] = $this->convertLatinQuotas($item['caption']['uz']);
                    }
                    return $item;
                });
        }
    }


    public function convertToLatin()
    {
        $translator = Translator::getInstance();

        foreach (self::cyLatAttributes() as $attribute) {
            $value = $this->getTranslation($attribute, Config::LANGUAGE_CYRILLIC);
            $this->setTranslation($attribute, $translator->translateToLatin($value), Config::LANGUAGE_UZBEK);
        }


        if ($data = $this->image) {
            if (is_array($data) && is_array($data['caption'])) {
                if (isset($data['caption']['cy'])) {
                    $data['caption']['uz'] = $translator->translateToLatin($data['caption']['cy']);
                }
            }
        }

        if ($this->type == self::TYPE_GALLERY)
            $this->processGallery(function ($item) use ($translator) {
                if (is_array($item) && isset($item['caption']['cy'])) {
                    $item['caption']['uz'] = $translator->translateToLatin($item['caption']['cy']);
                }
                return $item;
            });

        Yii::debug('Converted to Latin');

        return $this->updateAttributes(['_translations' => $this->_translations, 'image' => $data, 'gallery' => $this->gallery]);
    }

    public function convertToCyrillic()
    {
        $translator = Translator::getInstance();

        foreach (self::cyLatAttributes() as $attribute) {
            $value = $this->getTranslation($attribute, Config::LANGUAGE_UZBEK);
            $this->setTranslation($attribute, $translator->translateToCyrillic($value), Config::LANGUAGE_CYRILLIC);
        }

        if ($data = $this->image) {
            if (is_array($data) && is_array($data['caption'])) {
                if (isset($data['caption']['uz'])) {
                    $data['caption']['cy'] = $translator->translateToCyrillic($data['caption']['uz']);
                }
            }
        }

        if ($this->type == self::TYPE_GALLERY)
            $this->processGallery(function ($item) use ($translator) {
                if (is_array($item) && isset($item['caption']['uz'])) {
                    $item['caption']['cy'] = $translator->translateToCyrillic($item['caption']['uz']);
                }
                return $item;
            });

        Yii::debug('Converted to Cyrillic');

        return $this->updateAttributes(['_translations' => $this->_translations, 'image' => $data, 'gallery' => $this->gallery]);
    }

    public function getViewUrl(Category $category = null, $scheme = true)
    {
        if ($category) {
            return Url::to([$category->slug . '/' . $this->url], $scheme);
        }
        return Url::to(['yangiliklar/' . $this->url], $scheme);
    }

    public function getCroppedImage($width = 870, $height = 260, $watermark = false)
    {
        if ($this->image) {
            return parent::getCropImage($this->image, $width, $height, ManipulatorInterface::THUMBNAIL_OUTBOUND, $watermark);
        }

        return false;
    }

    public function getTagsData()
    {
        return array_map(function (Tag $tag) {
            return [
                'v' => $tag->getId(),
                't' => $tag->name,
            ];
        }, Tag::find()->where(['_id' => $this->getConvertedTags()])->all());
    }

    /**
     * @return \yii\db\ActiveQueryInterface
     */
    public function getCategories()
    {
        return $this->hasMany(Category::className(), ['_id' => '_categories']);
    }

    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['_id' => '_categories']);
    }

    /**
     * @return array|\yii\db\ActiveQueryInterface|\yii\mongodb\ActiveRecord
     */
    public function getTags()
    {
        return Tag::find()->where(['_id' => $this->getConvertedTags()])->all();
    }

    public function getTitleView()
    {
        if ($this->title) {
            return $this->getShortTitle();
        }

        return __('Draft post at {date}', ['date' => Yii::$app->formatter->asDatetime($this->created_at->getTimestamp(), 'php: l, d-F H:i')]);
    }

    public function getYoutubeEmbedUrl()
    {
        if ($url = $this->youtube_url) {
            $url = str_replace('watch?v=', 'embed/', $url);
            return $url;
        }

        return false;
    }

    public function getMoverEmbedUrl()
    {
        if ($url = $this->mover_url) {
            $url = str_replace('/watch/', '/video/embed/', $url);
            return $url;
        }

        return false;
    }

    public function toTrash()
    {
        $this->updateAttributes(['status' => self::STATUS_IN_TRASH]);
        return true;
    }

    public function restoreFromTrash()
    {
        $this->updateAttributes(['status' => self::STATUS_DRAFT]);
        return true;
    }

    public function getShortViewUrl($scheme = true)
    {
        return Url::to(['/' . ($this->short_id ? $this->short_id : $this->getId())], $scheme);
    }


    public function getShortTitle()
    {
        return StringHelper::truncateWords($this->title, 6);
    }

    public function getShortFormattedDate()
    {
        if ($this->published_on instanceof Timestamp) {
            $diff = time() - $this->published_on->getTimestamp();

            if ($diff < 300) {
                return __('Hozirgina');
            } elseif ($diff < 3600) {
                return __('{minute} minut avval', ['minute' => round($diff / 60)]);
            } elseif ($diff < 3600 * 3) {
                return __('{hour} soat avval', ['hour' => round($diff / 3600)]);
            } elseif ($diff < 86400) {
                $today = new DateTime();
                $today->setTime(0, 0, 0);

                $match_date = new DateTime();
                $match_date->setTimestamp($this->published_on->getTimestamp());
                $match_date->setTime(0, 0, 0);

                $diff     = $today->diff($match_date);
                $diffDays = (integer)$diff->format("%R%a");
                switch ($diffDays) {
                    case 0:
                        //today
                        return __('Bugun, {time}', ['time' => Yii::$app->formatter->asDate($this->published_on->getTimestamp(), 'php:H:i')]);
                        break;
                    case -1:
                        //Yesterday
                        return __('Kecha, {time}', ['time' => Yii::$app->formatter->asDate($this->published_on->getTimestamp(), 'php:H:i')]);
                        break;
                }

                return Yii::$app->formatter->asDate($this->published_on->getTimestamp(), 'php:d F, H:i');
            } elseif ($diff < 31536000) {
                return Yii::$app->formatter->asDate($this->published_on->getTimestamp(), 'php:d F, H:i');
            }
            return Yii::$app->formatter->asDate($this->published_on->getTimestamp());
        }

        return Yii::$app->formatter->asDate($this->created_at->getTimestamp());
    }

    public function getViewLabel()
    {
        return $this->views;
    }

    /**
     * @return GalleryItem[]
     */
    public function getGalleryItemsModel()
    {
        $result = [];
        if (is_array($this->gallery)) {
            foreach ($this->gallery as $item) {
                $result[] = new GalleryItem($item);
            }
        }

        return $result;
    }

    public function getPublishedOnSeconds()
    {
        return $this->published_on instanceof Timestamp ? $this->published_on->getTimestamp() : $this->published_on;
    }

    public function hasPriority()
    {
        return $this->label == self::LABEL_IMPORTANT || $this->label == self::LABEL_EDITOR_CHOICE || $this->is_main;
    }

    public function hasCommonTags(Post $post)
    {
        $thisTag = is_string($this->_tags) ? explode(',', $this->_tags) : $this->_tags;
        $postTag = is_string($post->_tags) ? explode(',', $post->_tags) : $post->_tags;

        return array_intersect($thisTag, $postTag);
    }

    private $excludedTags = [
        '5a0d7a922f19e0ba5e840976',
    ];

    public function compareStrings($s1, $s2)
    {
        $s1clean = preg_replace("/[^A-Za-z0-9]/", ' ', $s1);
        $s2clean = preg_replace("/[^A-Za-z0-9]/", ' ', $s2);

        $s1clean = strtolower(preg_replace('!\s+!', ' ', $s1clean));
        $s2clean = strtolower(preg_replace('!\s+!', ' ', $s2clean));

        //create arrays
        $ar1 = explode(" ", $s1clean);
        $ar2 = explode(" ", $s2clean);
        $l1  = count($ar1);
        $l2  = count($ar2);


        $ar1 = array_flip($ar1);
        $ar2 = array_flip($ar2);


        $maxwords = $l1 + $l2;
        $matches  = 0;

        //find matching words
        foreach ($ar1 as $word => $i) {
            if (array_key_exists($word, $ar2)) {
                $matches++;
            } else {
                if (strpos($word, $s2clean) !== false) $matches++;
            }
        }

        //find matching words
        foreach ($ar2 as $word => $i) {
            if (array_key_exists($word, $ar1)) {
                $matches++;
            } else {
                if (strpos($word, $s1clean) !== false) $matches++;
            }
        }

        return ['m' => $matches, 'p' => ($matches / $maxwords) * 100];
    }

    public function indexSimilarPosts($posts = false, $full = true)
    {
        if (!$posts) {
            $thisTags       = is_string($this->_tags) ? $this->getConvertedTags() : $this->_tags;
            $thisCategories = is_string($this->_categories) ? explode(",", $this->_categories) : $this->_categories;

            $thisTags = array_values(array_diff($thisTags, $this->excludedTags));
            $posts    = Post::find()
                            ->orFilterWhere(['_tags' => ['$in' => $thisTags]])
                            ->orFilterWhere(['_category' => ['$in' => $thisCategories]])
                            ->andFilterWhere(['status' => self::STATUS_PUBLISHED])
                            ->all();
        }


        $tagData  = [];
        $similar  = [];
        $counted  = 0;
        $withTags = 0;
        $titled   = 0;

        for ($i = 0; $i < count($posts); $i++) {
            $postB = $posts[$i];
            if (strpos($postB->title, 'va boshqa xabarlar') > 0) continue;

            if ($full || !$this->_similar_indexed || !$postB->_similar_indexed || in_array($postB->_id, $this->_similar)) {
                $common = $this->hasCommonTags($postB);
                $common = array_diff($common, $this->excludedTags);
                $count  = count($common);

                if ($postB->id != $this->id) {
                    if ($count > 0) $withTags++;
                    try {
                        $title = $this->compareStrings($this->title, $postB->title);
                    } catch (\Exception $e) {
                        echo $e->getMessage() . PHP_EOL;
                        continue;
                    }
                    $counted += $count;

                    $tagData[] = [
                        'id'      => $postB->_id,
                        'count'   => $count,
                        'updated' => $postB->published_on->getTimestamp(),
                        'post'    => $postB->title,
                        'title'   => $title,
                    ];

                    if ($title['p'] > 75) {
                        $titled++;
                    }
                }
            }
        }

        if (count($tagData)) {
            uasort($tagData, function ($b, $a) {
                if ($a['count'] == $b['count']) {
                    if ($a['title']['m'] == $b['title']['m']) {
                        return $a['title']['p'] - $b['title']['p'];
                    }
                    return $a['title']['m'] - $b['title']['m'];
                }

                return $a['count'] - $b['count'];
            });
            //print_r(array_slice($tagData, 0, 6, true));
            $similar = array_values(array_column(array_slice($tagData, 0, 6, true), 'id'));
        }

        $this->_similar = $similar;

        return $similar;
    }


    public static function reindexSimilarPostsByTag()
    {
        echo "reindexSimilarPostsByTag===================\n";
        $start = microtime(true);
        /**
         * @var $postA Post
         * @var $postB Post
         */
        $posts = Post::find()
                     ->where(['status' => Post::STATUS_PUBLISHED])
                     ->orderBy(['published_on' => SORT_DESC])
                     ->all();

        foreach ($posts as $postA) {
            echo $postA->title . PHP_EOL;
            $similar = $postA->indexSimilarPosts($posts, false);
            $postA->updateAttributes(['_similar' => $similar, '_similar_indexed' => true]);
        }
        $end = microtime(true);

        $time = round(($end - $start), 2);
        echo "Execution time: $time getTimestamp(onds\n";
    }


    public function getSimilarPosts($limit = 2)
    {
        $similar = ArrayHelper::map(self::find()
                                        ->where([
                                                    'status' => self::STATUS_PUBLISHED,
                                                    '_id'    => ['$in' => $this->_similar],
                                                ])
                                        ->all(), 'id', function ($item) {
            return $item;
        });

        $result = [];
        foreach ($this->_similar as $id) {
            $result[] = $similar[(string)$id];
        }

        return array_slice($result, 0, $limit);
    }
}
