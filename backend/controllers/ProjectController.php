<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace backend\controllers;

use common\models\Project;
use Yii;
use yii\base\Exception;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class ProjectController extends BackendController
{
    public $activeMenu = 'store';

    /**
     * @resource Web-site | View Project List | course/index
     */
    public function actionIndex()
    {
        $searchModel = new Project(['scenario' => 'search']);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $searchModel->search($this->get()),
        ]);
    }

    /**
     * @param $id bool|string
     * @return array|string
     * @resource Web-site | Manage Projects | course/edit,course/view
     * @throws NotFoundHttpException
     */
    public function actionEdit($id = false)
    {
        $model = $id ? $this->findModel($id) : new Project();

        if (Yii::$app->request->isAjax && $model->load($this->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($model);
        }

        if ($model->load($this->post()) && $model->save()) {
            if ($id) {
                $this->addSuccess(__('Project {title} updated successfully', ['title' => $model->name]));
            } else {
                $this->addSuccess(__('Project {title} created successfully', ['title' => $model->name]));
            }

            return $this->redirect(['edit', 'id' => $model->getId()]);
        }
        return $this->render('edit', [
            'model'        => $model,
        ]);
    }

    /**
     * @param $id
     * @return Project
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $model = Project::findOne($id);
        if (!$model)
            throw new NotFoundHttpException(__("Project not found"));

        return $model;
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Exception
     * @resource Web-site | Delete Projects | course/delete
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        try {
            if ($model->delete()) {

                $this->addSuccess(__('Project {title} deleted successfully', ['title' => $model->name]));
            }
        } catch (Exception $e) {
            $this->addError($e->getMessage());

            return $this->redirect(['edit', 'id' => $model->getId()]);
        }

        return $this->redirect(['index']);
    }
}