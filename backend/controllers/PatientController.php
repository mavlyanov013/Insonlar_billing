<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace backend\controllers;

use common\models\Patient;
use Yii;
use yii\base\Exception;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class PatientController extends BackendController
{
    public $activeMenu = 'store';

    /**
     * @resource Web-site | View Patient List | patient/index
     */
    public function actionIndex()
    {
        $searchModel = new Patient(['scenario' => 'search']);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $searchModel->search($this->get()),
        ]);
    }

    /**
     * @param $id bool|string
     * @return array|string
     * @resource Web-site | Manage Patients | patient/edit,patient/view
     * @throws NotFoundHttpException
     */
    public function actionEdit($id = false)
    {
        $model = $id ? $this->findModel($id) : new Patient();

        if (Yii::$app->request->isAjax && $model->load($this->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($model);
        }

        if ($model->load($this->post()) && $model->save()) {
            if ($id) {
                $this->addSuccess(__('Patient {title} updated successfully', ['title' => $model->fullname]));
            } else {
                $this->addSuccess(__('Patient {title} created successfully', ['title' => $model->fullname]));
            }

            return $this->redirect(['edit', 'id' => $model->getId()]);
        }
        return $this->render('edit', [
            'model'        => $model,
        ]);
    }

    /**
     * @param $id
     * @return Patient
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $model = Patient::findOne($id);
        if (!$model)
            throw new NotFoundHttpException(__("Patient not found"));

        return $model;
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Exception
     * @resource Web-site | Delete Patients | patient/delete
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        try {
            if ($model->delete()) {

                $this->addSuccess(__('Patient {title} deleted successfully', ['title' => $model->fullname]));
            }
        } catch (Exception $e) {
            $this->addError($e->getMessage());

            return $this->redirect(['edit', 'id' => $model->getId()]);
        }

        return $this->redirect(['index']);
    }
}