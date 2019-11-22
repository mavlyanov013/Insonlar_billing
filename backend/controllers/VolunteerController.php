<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace backend\controllers;

use common\components\Config;
use common\models\Volunteer;
use Yii;
use yii\base\Exception;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class VolunteerController extends BackendController
{
    public $activeMenu = 'users';

    /**
     * @return string
     * @resource Users | Manage Volunteers | volunteer/index, volunteer/view
     */
    public function actionIndex()
    {
        $searchModel = new Volunteer(['scenario' => 'search']);

        return $this->render('index', [
            'dataProvider' => $searchModel->search(Yii::$app->request->get()),
            'searchModel'  => $searchModel,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * @return mixed
     * @resource Users | Manage Volunteers | volunteer/create
     */
    public function actionCreate()
    {
        $model = new Volunteer();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->addSuccess(__('Volunteer {name} created successfully', ['name' => $model->fullname]));

            return $this->redirect(['update', 'id' => $model->id]);
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }


    /**
     * @param integer $id
     * @return mixed
     * @resource Users | Manage Volunteers | volunteer/update
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

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
                $this->addSuccess(__('Volunteer converted to {language} successfully', ['language' => Config::getLanguageLabel($lang)]));
            }

            return $this->redirect(['update', 'id' => $model->getId(), 'language' => $lang]);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->addSuccess(__('Volunteer {name} updated successfully', ['name' => $model->fullname]));

            return $this->redirect(['update', 'id' => $model->id]);
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * @param integer $id
     * @return mixed
     * @resource Users | Delete Volunteer | volunteer/delete
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        try {
            if ($model->delete()) {

                $this->addSuccess(__('Volunteer {name} deleted successfully', ['name' => $model->fullname]));
            }
        } catch (Exception $e) {
            $this->addError($e->getMessage());

            return $this->redirect(['update', 'id' => $model->id]);
        }


        return $this->redirect(['index']);
    }

    /**
     * Finds the Admin model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Volunteer the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Volunteer::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
