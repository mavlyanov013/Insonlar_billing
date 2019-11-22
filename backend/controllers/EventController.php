<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace backend\controllers;

use common\components\Config;
use common\models\Event;
use Yii;
use yii\base\Exception;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class EventController extends BackendController
{
    public $activeMenu = 'store';

    /**
     * @return string
     * @resource Web-site | Manage Events | event/index
     */
    public function actionIndex()
    {
        $searchModel = new Event(['scenario' => 'search']);

        return $this->render('index', [
            'dataProvider' => $searchModel->search(Yii::$app->request->get()),
            'searchModel'  => $searchModel,
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @resource Web-site | Manage Events | event/view
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * @param $id
     * @return array|string|Response
     * @resource Web-site | Manage Events | event/edit
     */
    public function actionEdit($id = false)
    {
        if ($id) {
            $model = $this->findModel($id);
        } else {
            $model = new Event();
        }

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($model);
        }


        if ($this->get('convert')) {
            $changed = false;
            $lang    = Yii::$app->language;

            if ($lang == Config::LANGUAGE_CYRILLIC) {
                $lang    = Config::LANGUAGE_UZBEK;
                $changed = $model->syncLatinCyrill($lang, true);

            } else if ($lang == Config::LANGUAGE_UZBEK) {
                $lang    = Config::LANGUAGE_CYRILLIC;
                $changed = $model->syncLatinCyrill($lang, true);
            }

            if ($changed) {
                $this->addSuccess(__('Event converted to {language} successfully', ['language' => Config::getLanguageLabel($lang)]));
            }

            return $this->redirect(['edit', 'id' => $model->getId(), 'language' => $lang]);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($id) {
                $this->addSuccess(__('Event {title} updated successfully', ['title' => $model->name]));
            } else {
                $this->addSuccess(__('Event {title} created successfully', ['title' => $model->name]));
            }

            return $this->redirect(['edit', 'id' => $model->getId()]);
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Exception
     * @resource Web-site | Delete Events | event/delete
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        try {
            if ($model->delete()) {

                $this->addSuccess(__('Event {title} deleted successfully', ['title' => $model->name]));
            }
        } catch (Exception $e) {
            $this->addError($e->getMessage());

            return $this->redirect(['edit', 'id' => $model->getId()]);
        }


        return $this->redirect(['index']);
    }


    /**
     * @param $id
     * @return null|Event
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Event::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested event does not exist.');
        }
    }

}
