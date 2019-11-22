<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace backend\controllers;

use common\components\Config;
use common\models\Page;
use Yii;
use yii\base\Exception;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class PageController extends BackendController
{
    public $activeMenu = 'system';

    /**
     * @return string
     * @resource System| Manage Pages | page/index
     */
    public function actionIndex()
    {
        $searchModel = new Page(['scenario' => 'search']);

        return $this->render('index', [
            'dataProvider' => $searchModel->search(Yii::$app->request->get()),
            'searchModel'  => $searchModel,
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @resource System | Manage Pages | page/view
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
     * @resource System | Manage Pages | page/edit
     */
    public function actionEdit($id = false)
    {
        if ($id) {
            $model = $this->findModel($id);
        } else {
            $model = new Page();
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
                $this->addSuccess(__('Page converted to {language} successfully', ['language' => Config::getLanguageLabel($lang)]));
            }

            return $this->redirect(['edit', 'id' => $model->getId(), 'language' => $lang]);
        }

        if ($model->load(Yii::$app->request->post()) && $model->updatePage()) {
            if ($id) {
                $this->addSuccess(__('Page {title} updated successfully', ['title' => $model->title]));
            } else {
                $this->addSuccess(__('Page {title} created successfully', ['title' => $model->title]));
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
     * @resource System | Delete Pages | page/delete
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        try {
            if ($model->delete()) {

                $this->addSuccess(__('Page {title} deleted successfully', ['title' => $model->title]));
            }
        } catch (Exception $e) {
            $this->addError($e->getMessage());

            return $this->redirect(['edit', 'id' => $model->getId()]);
        }


        return $this->redirect(['index']);
    }


    /**
     * @param $id
     * @return null|Page
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Page::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
