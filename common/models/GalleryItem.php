<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

/**
 * Created by PhpStorm.
 * Date: 12/15/17
 * Time: 3:34 AM
 */

namespace common\models;


use common\components\Config;
use Imagine\Image\ManipulatorInterface;

class GalleryItem
{
    public $caption;
    public $path;
    public $image;

    /**
     * GalleryItem constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $lang          = Config::getLanguageShortName();
        $this->caption = isset($data['caption'][$lang]) ? $data['caption'][$lang] : '';
        $this->image   = $data;
    }

    public function getImageCropped($width = 642, $height = 340)
    {
        return MongoModel::getCropImage($this->image, $width, $height, ManipulatorInterface::THUMBNAIL_OUTBOUND);
    }
}