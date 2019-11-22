<?php

namespace backend\controllers;

use common\models\payment\methods\Cash;
use common\models\payment\Payment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use yii\base\Exception;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * PaymentController implements the CRUD actions for Payment model.
 */
class PaymentController extends BackendController
{
    public $activeMenu = 'finance';

    /**
     * Lists all Payment models.
     * @return mixed
     * @resource Finance | Export Payments | payment/download
     */
    public function actionDownload()
    {
        /**
         * @var $model Payment
         */
        $searchModel = new Payment(['scenario' => 'search']);

        $models    = $searchModel->search($this->get(), false);
        $result    = [];
        $resultDay = [];

        $resultDay[] = [
            'date'   => $searchModel->getAttributeLabel('time'),
            'amount' => $searchModel->getAttributeLabel('amount'),
            'count'  => __('To\'lovlar'),
        ];

        $result[] = [
            'date'      => $searchModel->getAttributeLabel('time'),
            'amount'    => $searchModel->getAttributeLabel('amount'),
            'method'    => $searchModel->getAttributeLabel('method'),
            'user_data' => $searchModel->getAttributeLabel('user_data'),
        ];

        $total = 0;
        foreach ($models as $model) {
            $day = $model->getPaymentDateFormattedAsDay();
            if (!isset($resultDay[$day])) $resultDay[$day] = ['amount' => 0, 'count' => 0,];
            $resultDay[$day]['amount'] += $model->amount;
            $resultDay[$day]['count']++;

            $result[] = [
                'date'      => $model->getPaymentDateFormatted(),
                'amount'    => $model->amount,
                'method'    => $model->getMethodLabel(),
                'user_data' => $model->user_data,
            ];
            $total    += $model->amount;
        }

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle(__('To\'lovlar'));

        foreach ($result as $i => $row) {
            $sheet->setCellValueExplicitByColumnAndRow(1, $i + 1, $row['date'], DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow(2, $i + 1, $row['amount'], $i == 0 ? DataType::TYPE_STRING : DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicitByColumnAndRow(3, $i + 1, $row['method'], DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow(4, $i + 1, $row['user_data'], DataType::TYPE_STRING);
        }

        $rows = count($result);
        $sheet->setCellValueByColumnAndRow(2, $rows + 1, "=SUM(B2:B$rows)");


        $sheet = $spreadsheet->addSheet(new Worksheet(null, __('Kunlik')));

        $i = 0;
        foreach ($resultDay as $date => $row) {
            $sheet->setCellValueExplicitByColumnAndRow(1, $i + 1, $i == 0 ? '' : $date, DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow(2, $i + 1, $row['amount'], $i == 0 ? DataType::TYPE_STRING : DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicitByColumnAndRow(3, $i + 1, $row['count'], $i == 0 ? DataType::TYPE_STRING : DataType::TYPE_NUMERIC);
            $i++;
        }

        $writer   = new Xlsx($spreadsheet);
        $fileName = Yii::getAlias('@runtime') . DS . 'Payment-' . Yii::$app->formatter->asDatetime(time(), 'php:d-m-Y') . '.xlsx';
        $writer->save($fileName);

        Yii::$app->response->sendFile($fileName);
        unlink($fileName);
        return;

    }

    /**
     * Lists all Payment models.
     * @return mixed
     * @resource Finance | Manage Payments | payment/index
     */
    public function actionIndex()
    {
        /**
         * @var $model Payment
         */
        $searchModel = new Payment(['scenario' => 'search']);


        return $this->render('index', [
            'dataProvider' => $searchModel->search($this->get()),
            'searchModel'  => $searchModel,
        ]);
    }


    /**
     * Updates an existing Payment model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @resource Finance | Create Cash Payment | payment/cash
     */
    public function actionCash($id = false)
    {
        $model = false;
        if ($id) {
            $model = Payment::findOne(['_id' => $id, 'method' => Cash::METHOD_CODE]);
        }

        if ($model == null) {
            $method = Payment::getMethodInstance(Cash::METHOD_CODE);

            $model         = new Payment();
            $model->method = $method->getCode();
        }

        $model->setScenario('cash');

        if (Yii::$app->request->isAjax && $model->load($this->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($model);
        }

        if ($model->load($this->post()) && $model->save()) {
            $this->addSuccess(__('Cash Payment #{number} updated successfully', ['number' => $model->transaction_id]));

            return $this->redirect(['cash', 'id' => $model->id]);
        }

        return $this->render('cash', [
            'model' => $model,
        ]);
    }


    /**
     * @resource Finance | Manage Payments | payment/update
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->setScenario('update');

        if (Yii::$app->request->isAjax && $model->load($this->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($model);
        }

        if ($model->load($this->post()) && $model->save()) {
            $this->addSuccess(__('Payment #{number} updated successfully', ['number' => $model->transaction_id]));

            return $this->redirect(['update', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }


    /**
     * @resource Finance | Delete Payments | payment/delete
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        try {
            if ($model->delete()) {

                $this->addSuccess(__('Payment #{number} deleted successfully', ['number' => $model->transaction_id]));
            }
        } catch (Exception $e) {
            $this->addError($e->getMessage());

            return $this->redirect(['update', 'id' => $model->getId()]);
        }


        return $this->redirect(['index']);
    }

    /**
     * @resource Finance | Cancel Payments | payment/cancel
     */
    public function actionCancel($id)
    {
        $model = $this->findModel($id);

        $model->status = Payment::STATUS_CANCELLED;
        $model->addAllInformation(['cancel_note' => __('Cancelled by {login}', ['login' => $this->_user()->login])]);

        if ($model->save()) {
            $this->addSuccess(__('Payment #{number} cancelled successfully', ['number' => $model->transaction_id]));
        }


        return $this->redirect(['payment/update', 'id' => $model->id]);
    }

    /**
     * Finds the Payment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Payment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Payment::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
