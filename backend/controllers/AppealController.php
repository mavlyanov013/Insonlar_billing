<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace backend\controllers;

use common\models\Ad;
use common\models\Appeal;
use common\models\AppealHistory;
use Yii;
use yii\base\Exception;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class AppealController extends BackendController
{
    public $activeMenu = 'users';

    /**
     * @return string
     * @resource Appeal | Manage Appeals | appeal/index
     */
    public function actionIndex()
    {
        $searchModel = new Appeal(['scenario' => 'search']);

        return $this->render('index', [
            'dataProvider' => $searchModel->search(Yii::$app->request->get()),
            'searchModel'  => $searchModel,
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @resource Appeal | Manage Appeals | appeal/view
     * @resource Appeal | Change Appeal Status | appeal/status
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $comment = new AppealHistory();

        if ($this->_user()->canAccessToResource('appeal/status')) {
            if ($comment->load($this->post())) {
                if ($comment->updateAppeal($model)) {
                    $this->addSuccess(__('Murojaat {b}{number}{bc} yangilandi', ['number' => $model->number]));
                    $comment = new AppealHistory();
                } else {
                    $oldComment            = $comment;
                    $comment               = new AppealHistory();
                    $comment->status_after = $oldComment->status_after;
                    $comment->comment      = $oldComment->comment;
                }
            }
        }
        if ($this->get('print')) {
            $this->layout = 'main';
            return $this->render('print', [
                'model'   => $model,
                'comment' => $comment,
            ]);
        }
        return $this->render('view', [
            'model'   => $model,
            'comment' => $comment,
        ]);
    }

    /**
     * @return array|string|Response
     * @resource Appeal | Edit Appeals | appeal/edit
     */
    public function actionEdit($id)
    {
        $model           = $this->findModel($id);
        $model->scenario = Ad::SCENARIO_UPDATE;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($id) {
                $this->addSuccess(__('Appeal {number} updated successfully', ['number' => $model->number]));
            } else {
                $this->addSuccess(__('Appeal {number} created successfully', ['number' => $model->number]));
            }

            return $this->redirect(['edit', 'id' => $model->getId()]);
        }

        if ($file = $this->get('attachment', false)) {
            $attachment = array_filter($model->attachments, function ($item) use ($file) {
                return $item['name'] == $file;
            });
            return Yii::$app->response->sendFile(Yii::getAlias("@static/uploads/{$attachment[0]['path']}"), $file);
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
     * @resource Appeal | Delete Appeals | appeal/delete
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        try {
            if ($model->delete()) {

                $this->addSuccess(__('Appeal {b}{number}{bc} deleted successfully', ['number' => $model->number]));
            }
        } catch (Exception $e) {
            $this->addError($e->getMessage());

            return $this->redirect(['edit', 'id' => $model->getId()]);
        }


        return $this->redirect(['index']);
    }


    /**
     * @param $id
     * @return null|Appeal
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Appeal::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
