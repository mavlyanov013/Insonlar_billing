<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace console\controllers;

use common\components\Config;
use common\models\Category;
use common\models\Post;
use common\models\Tag;
use Faker\Generator;
use Yii;
use yii\console\Controller;

class FakerController extends Controller
{
    public $count    = 0;
    public $language = 'en-US';
    /**
     * @var Generator
     */
    private $_generator;

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'count',
        ]);
    }

    public function actionPost()
    {
        for ($i = 0; $i <= $this->count; $i++) {
            $post = new Post([
                                 'title'       => $this->getGenerator()->text(32),
                                 'url'         => $this->getGenerator()->slug,
                                 '_categories' => $this->getGenerator()->randomElements(array_map(function (Category $cat) {
                                     return $cat->getId();
                                 }, Category::findAll(['parent' => Config::get(Config::CONFIG_CATALOG_MENU_ROOT)])), 1),
                                 '_tags'       => $this->getGenerator()->randomElements(array_map(function (Tag $tag) {
                                     return $tag->getId();
                                 }, Tag::find()->all()), 1),
                                 'type'        => $this->getGenerator()->randomElement(['news', 'video', 'gallery']),  // generate a sentence with 7 words
                                 'info'        => $this->getGenerator()->sentence(15, true),  // generate a sentence with 7 words
                                 'status'      => Post::STATUS_PUBLISHED,
                                 'content'     => $this->getGenerator()->randomHtml(),
                             ]);
            $post->save(false);
            echo $post->title . " saved\n";
        }
    }

    /**
     * @return Generator
     */
    public function getGenerator()
    {
        if ($this->_generator === null) {
            $language         = $this->language === null ? Yii::$app->language : $this->language;
            $this->_generator = \Faker\Factory::create(str_replace('-', '_', $language));
        }
        return $this->_generator;
    }

}