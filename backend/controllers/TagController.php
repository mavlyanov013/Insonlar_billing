<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace backend\controllers;

use common\models\Tag;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class TagController extends BackendController
{
    public $activeMenu = 'store';

    /**
     * @param bool|string $id
     * @return Tag|Response|array|string
     * @resource News | Manage Tags | tag/index
     */
    public function actionIndex($id = false)
    {
        if ($id) {
            $model = $this->findModel($id);
            $model->setScenario('update');
        } else {
            $model = new Tag(['scenario' => 'insert']);
        }
        $searchModel = new Tag(['scenario' => 'search']);

        if ($this->get('save')) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                if ($id) {
                    $this->addSuccess(__('Tag {name} updated successfully', ['name' => $model->name_uz]));
                } else {
                    $this->addSuccess(__('Tag {name} created successfully', ['name' => $model->name_uz]));
                }

                if (!$this->isAjax())
                    return $this->redirect(['index', 'id' => $model->id]);
            }
        }
        return $this->render('index', [
            'model'        => $model,
            'searchModel'  => $searchModel,
            'dataProvider' => $searchModel->search(Yii::$app->request->get()),
        ]);
    }

    /**
     * @resource News | Manage Tags | tag/add
     * @return array|mixed
     */
    public function actionAdd()
    {
        $model                      = new Tag(['scenario' => Tag::SCENARIO_INSERT]);
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data                       = [
            $model->formName() => $this->post('data'),
        ];
        if ($model->load($data) && $model->save()) {
            return [
                'value' => $model->getId(),
                'text'  => $model->name,
            ];
        }
        return $this->post();
    }

    /**
     * @param $query
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionFetch($query)
    {
        $tags = Tag::find()
                   ->andFilterWhere(['like', 'name', $query])
                   ->all();

        $result                     = array_map(function (Tag $tag) {
            return [
                'value' => $tag->getId(),
                'text'  => $tag->name,
            ];
        }, $tags);
        Yii::$app->response->format = Response::FORMAT_JSON;

        return $result;
    }

    /**
     * @param $id
     * @param $attribute
     * @resource News | Manage Tags | tag/change
     * @return bool
     */
    public function actionChange($id, $attribute)
    {
        $tag = $this->findModel($id);
        if ($tag->hasAttribute($attribute)) {
            if ($tag->$attribute) {
                $tag->$attribute = false;
                $tag->save(false);
            } else {
                $tag->$attribute = true;
                $tag->save(false);
            }
            return !$tag->hasErrors();
        }
        return false;
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Exception
     * @resource News | Manage Tags | tag/delete
     */
    public function actionDelete($id)
    {
        /**
         * @var Tag $model
         */
        $model = $this->findModel($id);
        if ($model->delete()) {
            $this->addSuccess(__("Tag {name} has been deleted", ['name' => $model->name_uz]));
            return $this->redirect(['index']);
        }
    }


    /**
     * @param $id
     * @return null|Tag
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Tag::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested post does not exist.');
        }
    }

}
