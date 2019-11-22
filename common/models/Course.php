<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

/**
 * Created by PhpStorm.
 * User: rustam
 * Date: 3/18/17
 * Time: 11:38 AM
 */

namespace common\models;


use Imagine\Image\ManipulatorInterface;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * @property string name
 * @property string status
 * @property string description
 * @property string contact
 * @property string content
 * @property string address
 * @property array image
 * @property array files
 * @property mixed _search
 * @property mixed _category
 *
 * @property Category category
 */
class Course extends MongoModel
{
    protected $_translatedAttributes = ['name', 'description', 'content', 'address', 'contact'];
    protected $_idAttributes         = ['_category'];

    const STATUS_ENABLE  = 'enable';
    const STATUS_DISABLE = 'disable';

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

    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'course';
    }

    public static function getAll()
    {
        $all = self::find()->all();
        return ArrayHelper::map($all, 'id', 'name');
    }

    public static function getCoursesAsOption()
    {
        $result = ArrayHelper::map(self::find()->select(['_id', 'name'])->orderBy('name')->all(), function ($model) {
            return $model->getId();
        }, 'name');
        return $result;
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'name',
            'status',
            'start_time',
            'content',
            'description',
            'address',
            '_category',
            'image',
            'contact',
            'files',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 128],
            [['contact', 'address', 'content', 'image', '_category', 'description', 'start_time', 'status', 'files'], 'safe'],
            [['search'], 'safe', 'on' => 'search'],
        ];
    }

    /**
     * @return \yii\db\ActiveQueryInterface
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['_id' => '_category']);
    }

    public function search($params = [])
    {
        $query = self::find();
        $this->load($params);

        $dataProvider = new ActiveDataProvider([
                                                   'query'      => $query,
                                                   'sort'       => [
                                                       'defaultOrder' => [
                                                           'created_at' => SORT_DESC,
                                                       ],
                                                       'attributes'   => [
                                                           'name',
                                                           'updated_at',
                                                           'start_time',
                                                           'created_at',
                                                       ],
                                                   ],
                                                   'pagination' => [
                                                       'pageSize' => 50,
                                                   ],
                                               ]);

        if ($this->search) {
            $query->andFilterWhere(['like', 'name', $this->search]);
        }

        return $dataProvider;
    }


    public function getViewUrl($scheme = false)
    {
        return Url::to(['course/view', 'id' => $this->id], $scheme);
    }

    public function getCroppedImage($width = 870, $height = 260, $manipulation = ManipulatorInterface::THUMBNAIL_OUTBOUND, $watermark = false)
    {
        if ($this->image) {
            return parent::getCropImage($this->image, $width, $height, $manipulation, $watermark);
        }

        return false;
    }


    public function getFileDownloadLink($i)
    {
        $files = $this->files;
        if (isset($files[$i]) && is_array($files[$i]) && isset($files[$i]['path'])) {
            return linkTo(['/education/' . $this->id . '?file=' . $i]);
        }
        return false;
    }


    public function getFileDownloadPath($i)
    {
        $files = $this->files;

        if (isset($files[$i]) && is_array($files[$i]) && isset($files[$i]['path'])) {
            return Yii::getAlias("@static/uploads/") . $files[$i]['path'];
        }
        return false;
    }

}