<?php

namespace common\models;

use frontend\models\Products;
use Imagine\Image\ManipulatorInterface;
use Timestamp;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * This is the model class for table "category".
 * @property string  $name
 * @property string  $slug
 * @property string  $position
 * @property string  $_products
 * @property integer $count_products
 * @property string  $_filter
 * @property array   $_sort
 * @property string  $_type
 * @property string  $parent
 * @property mixed   $created_at
 * @property mixed   $updated_at
 * @property array   image
 * @property Post[]  posts
 */
class Category extends MongoModel
{
    protected $_translatedAttributes = ['name', 'description', 'meta_description', 'meta_keywords', 'page_title'];
    protected $_booleanAttributes    = [];

    /** @var self[] */
    public $child  = [];

    public function init()
    {

        parent::init();
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), [
            '_filter',
            '_type',
            '_sort',
            '_children',
            'position',
            'parent',
            'color',
            'name',
            'slug',
            'image',
            'description',
            'meta_description',
            'meta_keywords',
            'page_title',
            'count_posts',
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['image', 'color'], 'safe'],
            [['page_title', 'meta_keywords', 'meta_description'], 'string', 'max' => 1024],

            [['slug', 'name'], 'required', 'on' => ['insert', 'update']],
            [['slug'], 'match', 'pattern' => '/^[\/a-z0-9-]{1,128}$/', 'message' => __('Use only english alpha and numeric characters')],

            [['slug', 'description'], 'unique', 'on' => ['insert', 'update']],
            [['search'], 'safe'],
            [['parent'], 'default', 'value' => false],
        ];
    }

    public function getBooleanAttributes()
    {
        return $this->_booleanAttributes;
    }

    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->position = self::find()->count() + 1;
        }

        return parent::beforeSave($insert);
    }

    public static function getRootCategories()
    {
        return self::find()->where(['parent' => ['$eq' => false]])->orderBy('position')->all();
    }

    public static function getParentCategory($parent)
    {
        return self::findOne(['_id' => $parent]);
    }

    public static function getChildCategories($category)
    {
        return self::findAll(['parent' => $category]);
    }

    public static function getRootCategoriesAsOption()
    {
        return ArrayHelper::map(self::getRootCategories(), function (self $model) {
            return $model->getId();
        }, 'name');
    }

    public static function getCategoryTreeAsArray($selected = [], $root = false)
    {
        return self::_tree(self::getCategoryTree([], $root), $selected);
    }

    protected static function _tree($categories, $selected)
    {
        $result = [];
        foreach ($categories as $category) {
            $result[] = [
                'title'    => $category->name,
                'selected' => in_array($category->getId(), $selected),
                'key'      => $category->getId(),
                'expanded' => false,
                'folder'   => count($category->child) > 0,
                'children' => count($category->child) ? self::_tree($category->child, $selected) : null,
            ];
        }
        return $result;
    }

    /**
     * @param array $where
     * @param bool  $root
     * @param bool  $self
     * @return Category[]|array|mixed
     */
    public static function getCategoryTree($where = [], $root = false, $self = false)
    {
        /**
         * @var $category Category
         */

        $all        = self::find()->where($where)->orderBy('position')->all();
        $categories = [];
        $moved      = [];

        foreach ($all as $category) {
            $categories[$category->getId()] = $category;
        }
        foreach ($all as $category) {
            if ($parent = $category->parent) {
                if (isset($categories[$parent])) {
                    $categories[$parent]->child[] = $category;
                    $moved[]                      = $category->id;
                }
            }
        }

        foreach ($moved as $id) {
            unset($categories[$id]);
        }

        if ($root && isset($categories[$root])) {
            return $self ? $categories[$root] : $categories[$root]->child;
        }

        return $categories;
    }

    public static function sortTree($data, $parent = false)
    {
        $pos = 0;
        foreach ($data as $item) {
            if ($category = self::findOne($item['id'])) {
                $category->parent   = $parent;
                $category->position = $pos++;
                $category->save();
                if (isset($item['children']))
                    self::sortTree($item['children'], $category->getId());
            }
        }
    }

    public function getCroppedImage($width = 870, $height = 260)
    {
        if ($this->image) {
            return parent::getCropImage($this->image, $width, $height, ManipulatorInterface::THUMBNAIL_OUTBOUND);
        }

        return false;
    }

    public function searchByParent($parent)
    {
        $query = self::find()->where(['parent' => $parent]);

        $dataProvider = new ActiveDataProvider([
                                                   'query'      => $query,
                                                   'pagination' => [
                                                       'pageSize' => 5,
                                                   ],
                                               ]);

        return $dataProvider;
    }

    public function getViewUrl($params = [])
    {
        return Url::to(array_merge(['/' . $this->slug], $params), true);
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

    public function afterFind()
    {
        parent::afterFind();
    }


}
