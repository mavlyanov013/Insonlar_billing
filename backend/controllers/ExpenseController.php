<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace backend\controllers;

use common\models\Expense;
use Yii;
use yii\base\Exception;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class ExpenseController extends BackendController
{
    public $activeMenu = 'finance';

    /**
     * @resource Finance | Manage Expenses | expense/index
     */
    public function actionIndex()
    {
        $searchModel = new Expense(['scenario' => 'search']);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $searchModel->search($this->get()),
        ]);
    }

    /**
     * @param $id bool|string
     * @return array|string
     * @resource Finance | Manage Expenses | expense/edit,expense/view
     * @throws NotFoundHttpException
     */
    public function actionEdit($id = false)
    {
        $model = $id ? $this->findModel($id) : new Expense();

        if (Yii::$app->request->isAjax && $model->load($this->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($model);
        }

        if ($model->load($this->post()) && $model->save()) {
            if ($id) {
                $this->addSuccess(__('Expense {title} updated successfully', ['title' => $model->name]));
            } else {
                $this->addSuccess(__('Expense {title} created successfully', ['title' => $model->name]));
            }

            return $this->redirect(['edit', 'id' => $model->getId()]);
        }
        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * @param $id
     * @return Expense
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $model = Expense::findOne($id);
        if (!$model)
            throw new NotFoundHttpException(__("Expense not found"));

        return $model;
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Exception
     * @resource Finance | Delete Expenses | expense/delete
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        try {
            if ($model->delete()) {

                $this->addSuccess(__('Expense {title} deleted successfully', ['title' => $model->name]));
            }
        } catch (Exception $e) {
            $this->addError($e->getMessage());

            return $this->redirect(['edit', 'id' => $model->getId()]);
        }

        return $this->redirect(['index']);
    }
}