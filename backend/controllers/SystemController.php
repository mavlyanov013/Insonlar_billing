<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace backend\controllers;

use backend\models\FormUploadTrans;
use common\components\Config;
use common\models\Log;
use common\models\Login;
use common\models\SystemMessage;
use ErrorException;
use Yii;
use yii\data\ArrayDataProvider;
use yii\helpers\BaseFileHelper;

class SystemController extends BackendController
{

    public $activeMenu = 'system';

    /**
     * @resource System | System Backups | system/backup
     */
    public function actionBackup()
    {
        $dir = Yii::getAlias('@backups') . DS;
        if ($name = $this->get('id')) {
            if (file_exists($dir . $name)) {
                return Yii::$app->response->sendFile($dir . $name);
            }
        }
        if ($name = $this->get('rem')) {
            if (file_exists($dir . $name)) {
                $time = time() - intval(filemtime($dir . $name));
                if ($time < 3600 * 24 * 7) {
                    if (unlink($dir . $name)) {
                        $this->addSuccess(__('File {file} has removed', ['file' => $name]));
                    }
                } else {
                    $this->addError(__('You cannot delete backups after a week'));
                }
                return $this->redirect(['/backend/system/backup']);
            }
        }
        $data = [];
        foreach (glob($dir . '*.bak.*') as $file) {
            $data[] = [
                'name' => basename($file),
                'size' => Yii::$app->formatter->asSize(filesize($file)),
                'time' => Yii::$app->formatter->asDatetime(filemtime($file)),
                'date' => intval(filemtime($file)),
            ];
        }

        $provider = new ArrayDataProvider([
                                              'allModels'  => $data,
                                              'sort'       => [
                                                  'attributes'   => ['name', 'size', 'time', 'date'],
                                                  'defaultOrder' => ['date' => SORT_DESC],
                                              ],
                                              'pagination' => [
                                                  'pageSize' => 20,
                                              ],
                                          ]);
        return $this->render('backup', ['dataProvider' => $provider]);
    }


    /**
     * @return string
     * @resource System | Manage Translations | system/deltrans
     */
    public function actionDelete($id)
    {
        if ($message = SystemMessage::findOne(['_id' => $id])) {
            if ($message->delete()) {
                $this->addSuccess(__('Message "{message}" deleted successfully', ['message' => $message->message]));
            }
        }
    }

    /**
     * @return string
     * @resource System | Manage Translations | system/translate
     */
    public function actionTranslate($id)
    {
        /**
         * @var $message SystemMessage;
         */
        $message = SystemMessage::findOne(['_id' => $id]);

        if ($message->load(Yii::$app->request->post()) && $message->save()) {
            $this->addSuccess(__('Message "{message}" updated successfully', ['message' => $message->message]));
            return null;
        }

        return $this->renderPartial('translate', ['model' => $message]);
    }

    /**
     * @param bool $convert
     * @return string
     * @resource System | Manage Translations | system/translation
     * @resource System | Upload Translations | system/upload-trans
     */
    public function actionTranslation($convert = false)
    {
        $searchModel = new SystemMessage(['scenario' => 'search']);

        $model = new FormUploadTrans();
        if ($this->_user()->canAccessToResource('system/upload-trans')) {
            if ($model->load(Yii::$app->request->post())) {
                if ($data = $model->uploadData()) {
                    $this->addSuccess(__('{count} message updated successfully', ['count' => count($data)]));
                } else {
                    $errors = $model->getFirstErrors();
                    $this->addError(array_pop($errors));
                }

                return $this->refresh();
            }
        }
        /**
         * @var SystemMessage $message
         */
        if ($convert && Config::isLatinCyrill() && !Yii::$app->request->isAjax) {
            $count = 0;
            foreach (SystemMessage::find()->all() as $message) {
                $count += $message->transliterateUzbek();
            }

            if ($count) {
                $this->addSuccess(__('{count} messages transliterated successfully', ['count' => $count]));
            }
            return $this->redirect(['/backend/system/translation']);
        }

        return $this->render('translation', [
            'dataProvider' => $searchModel->search(Yii::$app->request->get()),
            'searchModel'  => $searchModel,
            'model'        => $model,
        ]);
    }

    /**
     * @return string
     * @resource System | Download Translations | system/download
     */
    public function actionDownload()
    {
        /**
         * @var $message SystemMessage
         */
        $languages = Config::getLanguageOptions();
        $result    = [
            array_merge(['category', 'message'], array_keys($languages)),
        ];

        foreach (SystemMessage::find()->orderBy(['_id' => SORT_ASC])->all() as $message) {
            $data = [
                'category' => $message->category,
                'message'  => $message->message,
            ];

            foreach ($languages as $lang => $label) {
                $data[$lang] = $message->hasAttribute($lang) ? $message->getAttribute($lang) : "";
            }

            $result[] = $data;
        }

        $fileName = Yii::getAlias('@runtime') . DS . 'trans_' . time() . '.csv';
        if ($handle = fopen($fileName, 'w+')) {
            foreach ($result as $row)
                fputcsv($handle, $row, ",", '"');
            fclose($handle);

            Yii::$app->response->sendFile($fileName);
        }
    }

    /**
     * @return string|\yii\web\Response
     * @resource System | Change Configuration | system/configuration
     */
    public function actionConfiguration()
    {
        if (Yii::$app->request->getIsPost() && Config::batchUpdate($this->getPost('config'))) {
            $this->addSuccess(__('Configuration updated successfully'));

            return $this->refresh();
        }

        return $this->render('configuration', []);
    }

    /**
     * @return string|\yii\web\Response
     * @resource System | Create DB Snapshot | system/snapshot
     */
    public function actionSnapshot()
    {
        $old_path = getcwd();
        chdir(Yii::getAlias('@backups'));

        putenv("PATH=/home/user/bin/:" . $_SERVER["PATH"] . "");
        $output = shell_exec('./backup.sh no');

        chdir($old_path);


        return $this->redirect(['/backend/system/backup']);
    }

    /**
     * @return \yii\web\Response
     * @resource System | Clear Cache | system/cache
     */
    public function actionCache()
    {
        $dirs = [
            '@frontend/runtime/cache',
            '@api/runtime/cache',
            '@backend/runtime/cache',
        ];
        foreach ($dirs as $dir) {
            $dir = Yii::getAlias($dir);

            if (is_dir($dir)) {
                try {
                    BaseFileHelper::removeDirectory($dir);
                } catch (\Exception $e) {
                }
            }

        }

        $this->addSuccess(__('System cache cleared successfully'));
        return $this->redirect(Yii::$app->request->getReferrer() ?: ['/backend/dashboard/index']);
    }


    private static function removeDirContent($dir, $child = false)
    {
        if (!($handle = opendir($dir))) {
            return;
        }
        if (!is_link($dir)) {

            while (($file = readdir($handle)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $path = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($path)) {
                    static::removeDirContent($path, true);
                } else {
                    try {
                        unlink($path);
                    } catch (ErrorException $e) {
                        if (DIRECTORY_SEPARATOR === '\\') {
                            // last resort measure for Windows
                            $lines = [];
                            exec("DEL /F/Q \"$path\"", $lines, $deleteError);
                        } else {
                            throw $e;
                        }
                    }
                }
            }
            closedir($handle);
        }
        if ($child) {
            if (is_link($dir)) {
                unlink($dir);
            } else {
                rmdir($dir);
            }
        }
    }


    /**
     * @return string
     * @resource System | Login History | system/logins
     */
    public function actionLogins()
    {
        $searchModel = new Login(['scenario' => 'search']);

        return $this->render('logins', [
            'dataProvider' => $searchModel->search(Yii::$app->request->get()),
            'searchModel'  => $searchModel,
        ]);
    }

    /**
     * @return string
     * @resource System | Login History | system/del-login
     */
    public function actionDelLogin($id)
    {
        if ($message = Login::findOne($id)) {
            if ($message->delete()) {
                $this->addSuccess(__('Record deleted successfully'));
            }
        }

        return $this->actionLogins();
    }

    /**
     * @return string
     * @resource System | User Logs | system/user-logs
     */
    public function actionUserLogs()
    {
        $searchModel = new Log();

        return $this->render('user-logs', [
            'dataProvider' => $searchModel->search(Yii::$app->request->get()),
            'searchModel'  => $searchModel,
        ]);
    }


    /**
     * @return string
     * @resource System | User Logs | system/user-logs-view
     */
    public function actionUserLogsView($id)
    {
        $model = Log::findOne($id);

        return $this->render('user-logs-view', [
            'model' => $model,
        ]);
    }

}
