<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace backend\controllers;

use trntv\filekit\Storage;
use yii\di\Instance;
use yii\helpers\Url;
use yii\web\HttpException;

/**
 * FileStorageController implements the CRUD actions for files.
 */
class FileStorageController extends BackendController
{

    public function actions()
    {
        return [
            'upload' => [
                'class'           => 'common\components\CropUpload',
                'deleteRoute'  => 'delete',
                'fileparam'    => 'files',
                'on afterSave' => function ($event) {
                    /* @var $file \League\Flysystem\File */
                    $file = $event->file;
                    // do something (resize, add watermark etc)
                },
            ],
            'delete' => [
                'class' => 'common\components\CropUploadDelete',
            ],
        ];
    }

    /**
     * @resource Web-site | Upload Files | file-storage/delete,file-storage/upload
     */
    public function actionUploadDelete()
    {
        $path        = \Yii::$app->request->post('file');
        $uploads_url = Url::to('@staticUrl/uploads/');
        $path        = str_replace($uploads_url, '', $path);
        $success     = $this->getFileStorage()->delete($path);
        if (!$success) {
            throw new HttpException(400);
        }
        return $success;

    }

    protected function getFileStorage()
    {
        $fileStorage = Instance::ensure('fileStorage', Storage::className());
        return $fileStorage;
    }
}
