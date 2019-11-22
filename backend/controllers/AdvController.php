<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace backend\controllers;

use common\components\Config;
use common\models\Ad;
use Yii;
use yii\base\Exception;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class AdvController extends BackendController
{
    public $activeMenu = 'adv';

    /**
     * @return string
     * @resource Advertising | Manage Ads | adv/index
     */
    public function actionIndex()
    {
        $searchModel = new Ad();

        return $this->render('index', [
            'dataProvider' => $searchModel->search(Yii::$app->request->get()),
            'searchModel'  => $searchModel,
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @resource Advertising | Manage Ads | adv/view
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
     * @resource Advertising | Edit Ads | adv/edit
     */
    public function actionEdit($id = false)
    {
        if ($id) {
            $model           = $this->findModel($id);
            $model->scenario = Ad::SCENARIO_UPDATE;
        } else {
            $model = new Ad(['scenario' => 'insert']);
        }

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $model->checkStatus();
            $model->checkLimit();

            if ($id) {
                $this->addSuccess(__('Advertising {title} updated successfully', ['title' => $model->title]));
            } else {
                $this->addSuccess(__('Advertising {title} created successfully', ['title' => $model->title]));
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
     * @resource Advertising | Delete Ads | adv/delete
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        try {
            if ($model->delete()) {

                $this->addSuccess(__('Advertising {title} deleted successfully', ['title' => $model->title]));
            }
        } catch (Exception $e) {
            $this->addError($e->getMessage());

            return $this->redirect(['edit', 'id' => $model->getId()]);
        }


        return $this->redirect(['index']);
    }


    /**
     * @param $id
     * @return null|Ad
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Ad::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
