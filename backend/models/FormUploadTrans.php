<?php

namespace backend\models;

use common\components\Config;
use common\models\SystemMessage;
use ErrorException;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class FormUploadTrans extends Model
{
    const ONE_MB = 1048576;
    /**
     * @var UploadedFile
     */
    public $file;


    public function rules()
    {
        return [
            [['file'], 'file',
             'extensions'               => ['csv'],
             'checkExtensionByMimeType' => false,
             'maxSize'                  => 50 * self::ONE_MB,
             'tooBig'                   => __('The file {file} is too big. Its size cannot exceed 50 Mb.'),
            ],
        ];
    }

    public function uploadData()
    {
        /**
         * @var $mongo \yii\mongodb\Connection
         */

        $this->file = UploadedFile::getInstance($this, 'file');

        if ($this->validate()) {
            try {
                $cols   = false;
                $handle = fopen($this->file->tempName, 'r');
                $i      = 0;
                $data   = [];
                while ($row = fgetcsv($handle)) {
                    $i++;
                    if ($i == 1) {
                        foreach ($row as $r => $id) {
                            if (empty($id)) {
                                unset($row[$r]);
                            }
                        }
                        $cols = array_flip($row);

                        continue;
                    }
                    $attributes = [];

                    foreach ($cols as $name => $index) {
                        $attributes[$name] = trim($row[$index]);
                    }

                    if (!isset($attributes['category']) || !$attributes['category']) {
                        $attributes['category'] = 'app.ui';
                    }

                    if (isset($data[$attributes['message']])) {
                        if (!isset($data[$attributes['message']][Config::LANGUAGE_DEFAULT]) || empty($data[$attributes['message']][Config::LANGUAGE_DEFAULT])) {
                            $data[$attributes['message']] = $attributes;
                        }
                    } else {
                        $data[$attributes['message']] = $attributes;
                    }
                }

                $mongo = Yii::$app->mongodb;
                $mongo->getCollection(SystemMessage::collectionName())->remove();

                if ($inserted = $mongo->getCollection(SystemMessage::collectionName())->batchInsert(array_values($data))) {
                    return $inserted;
                }
            } catch (ErrorException $e) {
                $this->addError('file', $e->getMessage());
            }
        }

        return false;
    }

}