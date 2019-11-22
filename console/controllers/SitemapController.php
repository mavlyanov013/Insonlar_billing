<?php
/**
 * Created by PhpStorm.
 * User: shavkat
 * Date: 2/1/17
 * Time: 4:54 PM
 */

namespace console\controllers;


use common\components\Config;
use common\components\LocaleSitemap;
use common\models\Blogger;
use common\models\Category;
use common\models\Post;
use common\models\Tag;
use Yii;
use yii\console\Controller;

class SitemapController extends Controller
{
    public function actionIndex()
    {
        self::generate();
    }

    public static function generate()
    {
        /**
         * @var $post     Post
         * @var $blogger  Blogger
         * @var $category Category
         * @var $tag      Tag
         */
        $host    = Yii::getAlias('@frontendUrl') . '/';
        $sitemap = new LocaleSitemap($host);

        $sitemap->setPath(Yii::getAlias('@frontend/web') . DIRECTORY_SEPARATOR);

        $sitemap->addItem('', 1, 'hourly');


        foreach (Post::find()
                     ->where(['status' => Post::STATUS_PUBLISHED])
                     ->orderBy(['published_on' => SORT_DESC])
                     ->all() as $post) {

            $sitemap->addItem(
                $post->getViewUrl($post->category, false),
                $post->hasPriority() ? 1 : 0.9,
                'daily',
                $post->updated_at->sec
            );
        }

        foreach (Tag::getMostUsed(50) as $tag) {
            $sitemap->addItem($tag->getViewUrl(true), 0.5, 'daily');
        }

        $sitemap->createSitemapIndex($host, 'Today');
    }
}