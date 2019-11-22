<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace backend\controllers;

use common\models\Ad;
use Yii;
use common\models\Place;
use yii\web\Response;
use yii\base\Exception;
use yii\widgets\ActiveForm;
use yii\web\NotFoundHttpException;

class PlaceController extends BackendController
{
    public $activeMenu = 'adv';

    /**
     * @return string
     * @resource Advertising | Manage Places | place/index
     */
    public function actionIndex()
    {
        $model = new Place();

        return $this->render('index', [
            'dataProvider' => $model->search(Yii::$app->request->get()),
            'model'        => $model,
        ]);
    }

    /**
     * @param bool $id
     *
     * @return array|string|Response
     * @resource Advertising | Edit Places | place/edit
     */
    public function actionEdit($id = false)
    {
        if ($id) {
            $model = $this->findModel($id);
            $model->setScenario('update');
        } else {
            $model = new Place(['scenario' => 'insert']);
        }

        if ($this->isAjax()) {
            if ($model->load($this->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }

            if ($this->get('add')) {
                if ($data = @json_decode($this->post('data'), true)) {
                    return $model->addAds($data);
                }
            }

            if ($this->get('remove')) {
                if ($data = @json_decode($this->post('data'), true)) {
                    return $model->removeAds($data);
                }
            }

            if (($p = intval($this->get('percent'))) != 0) {
                if ($ad = $this->findAdModel($this->get('ad'))) {

                    $model->changeAdPercent($ad, $p);

                    return $this->render('edit', [
                        'model'        => $model,
                        'dataProvider' => $model->getAdsProvider(),
                    ]);
                }
            }

            if ($this->get('list')) {
                return $this->renderAjax('_ad', [
                    'dataProvider' => $model->getAdsNinProvider(),
                ]);
            }
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($id) {
                $this->addSuccess(__('Place {title} updated successfully', ['title' => $model->title]));
            } else {
                $this->addSuccess(__('Place {title} created successfully', ['title' => $model->title]));
            }

            return $this->redirect(['edit', 'id' => $model->getId()]);
        }

        return $this->render('edit', [
            'model'        => $model,
            'dataProvider' => $model->getAdsProvider(),
        ]);
    }

    /**
     * @param $id
     *
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Exception
     * @resource Advertising | Delete Places | place/delete
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        try {
            if ($model->delete()) {

                $this->addSuccess(__('Place {title} deleted successfully', ['title' => $model->title]));
            }
        } catch (Exception $e) {
            $this->addError($e->getMessage());

            return $this->redirect(['edit', 'id' => $model->getId()]);
        }

        return $this->redirect(['index']);
    }

    /**
     * @param $id
     *
     * @return null|Place
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Place::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested Place does not exist.');
        }
    }

    /**
     * @param $id
     *
     * @return null|Ad
     * @throws NotFoundHttpException
     */
    protected function findAdModel($id)
    {
        if (($model = Ad::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested Ad does not exist.');
        }
    }
}