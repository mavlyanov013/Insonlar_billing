<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace frontend\controllers;

use common\models\Appeal;
use common\models\Expense;
use common\models\Page;
use common\models\payment\methods\agr\AgrWebForm;
use common\models\payment\methods\paycom\PaycomWebForm;
use common\models\payment\methods\paymo\PaymoWebForm;
use frontend\models\AppealForm;
use frontend\models\ContactForm;
use frontend\models\Payments;
use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\Cookie;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends BaseController
{
    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $paycomPayment = new PaycomWebForm();
        $agrPayment    = new AgrWebForm();
        $result        = false;

        if ($this->get('success') == md5(Yii::$app->session->id)) {
            $this->addSuccess(__('To\'lov muvaffaqqiyatli amalga oshdi'));
            return $this->redirect(['/']);
        }

        $name = mb_substr(trim(strip_tags($this->get('name'))), 0, 120);

        if ($amount = intval($this->get('amount'))) {
            if ($this->get('method') == 'paycom') {
                if ($paycomPayment->isActive()) {
                    if ($result = $paycomPayment->prepareFormWithParams($amount, $name)) {
                        $this->addSuccess(__('Siz PayMe to\'lov tizimiga o\'tkazilasiz'));
                    } else {
                        $errors = $paycomPayment->getErrors();
                        $errors = array_pop($errors);
                        $this->addError($errors[0]);
                    }
                }
            } else {
                if ($agrPayment->isActive()) {
                    if ($result = $agrPayment->prepareFormWithParams($amount, $name)) {
                        $this->addSuccess(__('Siz PaSys to\'lov tizimiga o\'tkazilasiz'));
                    } else {
                        $errors = $agrPayment->getErrors();
                        $errors = array_pop($errors);
                        $this->addError($errors[0]);
                    }
                }
            }
        }


        return $this->render('index', [
            'result' => $result,
            'name'   => $name,
            'amount' => $amount,
        ]);
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionPaymo()
    {
        $paymoPayment = new PaymoWebForm();
        $paymoResult  = false;

        if ($paymoPayment->isActive()) {
            $paymoPayment->amount  = intval($this->get('amount'));
            $paymoPayment->account = substr(trim(strip_tags($this->get('name'))), 0, 120);
            if ($paymoPayment->validate()) {
                if ($paymoResult = $paymoPayment->prepareForm()) {
                    // $this->addSuccess(__('Siz Paymo to\'lov tizimiga o\'tqazilasiz'));
                } else {
                    $errors = $paymoPayment->getErrors();
                    $errors = array_pop($errors);
                    $this->addError($errors[0]);
                }
            }
        }

        return $this->render('paymo', [
            'paymoResult'  => $paymoResult,
            'paymoPayment' => $paymoPayment,
        ]);
    }


    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                $this->addSuccess(__('Thank you for contacting us. We will respond to you as soon as possible.'));
            } else {
                $this->addError(__('There was an error sending your message.'));
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Displays contact page.
     *
     * @param string $type
     * @return mixed
     */
    public function actionReport($type = 'payments')
    {
        if ($type == 'payments') {
            $model = new Payments(['scenario' => 'search']);
        } else {
            $model = new Expense(['scenario' => 'search']);
        }

        return $this->render('report', [
            'model'    => $model,
            'provider' => $model->search($this->get()),
            'type'     => $type,
        ]);
    }

    /**
     * Displays appeal page.
     *
     * @return mixed
     */
    public function actionAppealView($number, $t = '')
    {
        if ($model = Appeal::findOne(['number' => $number])) {
            if ($t == $model->getNumberToken()) {
                return $this->render('appeal-view', ['model' => $model]);
            }
        }

        return $this->render('appeal-view-no');
    }

    public function actionAppeal()
    {
        $model = new Appeal(['scenario' => 'insert']);

        if (Yii::$app->request->isAjax && $model->load($this->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            Yii::$app->response->cookies->add(new Cookie([
                'name'   => Appeal::COOKIE_NAME,
                'value'  => base64_encode(Json::encode($model->getAttributes(), true)),
                'expire' => time() + 86400,
            ]));
            $labels = [];
            foreach ($model->activeAttributes() as $attribute) {
                $labels[Html::getInputId($model, $attribute)] = $model->getAttributeLabel($attribute);
            }
            return $labels;
        }

        if (Yii::$app->request->cookies->has(Appeal::COOKIE_NAME)) {
            $data = base64_decode(Yii::$app->request->cookies->get(Appeal::COOKIE_NAME));
            $model->load(Json::decode($data), '');
        }


        if (Yii::$app->request->getIsPost()
            && $model->load($this->post())
            && $model->validate()) {

            if ($model->save()) {
                $this->addSuccess(__('Sizning {b}{number}{bc} raqamli arizangiz ro\'yxatga olindi. Tez orada murojaatni ko\'rib chiqamiz', ['number' => $model->number]));
                $model->sendEmail();
                Yii::$app->response->cookies->remove(Appeal::COOKIE_NAME);
            } else {
                $this->addError(__('There was an error sending your message.'));
            }

            return $this->refresh();
        } else {


            if (!YII_DEBUG && Appeal::checkLimitLastTime()
                && !Yii::$app->request->cookies->getValue(Appeal::CAPTCHA_COOKIE_NAME, false)) {
                return $this->redirect(['/captcha']);
            }


            return $this->render('appeal', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Displays appeal page.
     *
     * @return mixed
     */
    public function actionCaptcha()
    {
        $model = new AppealForm();

        if (!Yii::$app->request->isAjax && $model->load($this->post()) && $model->validate()) {
            Yii::$app->response->cookies->add(new Cookie([
                'name'   => Appeal::CAPTCHA_COOKIE_NAME,
                'value'  => true,
                'expire' => time() + 3600,
            ]));
            return $this->redirect(['/add-case']);
        }

        return $this->render('captcha', [
            'model' => $model,
        ]);
    }


    /**
     * Displays page.
     *
     * @param $slug string
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionPage($slug)
    {
        if ($page = Page::findOne(['status' => Page::STATUS_PUBLISHED, 'url' => $slug])) {

            return $this->render('page', [
                'model' => $page,
            ]);
        }
        throw new NotFoundHttpException('Page not found');
    }


    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Displays partner page.
     *
     * @return mixed
     */
    public function actionPartner()
    {
        return $this->render('partner');
    }

    /**
     * Displays partner page.
     *
     * @return mixed
     */
    public function actionManagement()
    {
        return $this->render('management');
    }

    /**
     * Displays payment page.
     *
     * @return mixed
     */
    public function actionPayment()
    {
        return $this->render('payment');
    }

    public function actionT()
    {
        $date    = date("d-m-Y H:i:s");
        $time    = time();
        $content = $this->renderPartial('@frontend/views/payments-pdf', ['date' => $date]);
        $pdf     = new \kartik\mpdf\Pdf([
            'mode'        => \kartik\mpdf\Pdf::MODE_UTF8,
            'content'     => $content,
            'filename'    => "Payments-$time.pdf",
            'destination' => \kartik\mpdf\Pdf::DEST_BROWSER,
            'cssFile'     => '@frontend/assets/app/css/pdf.css',
            'cssInline'   => '',
            'options'     => [
                'title'   => 'To‘lov tarixi',
                'subject' => "Mehrli qo‘llar uchun $date ga qadar kunlik o‘tkazilgan summalar",
            ],
            'methods'     => [
                'SetHeader' => ['<a href="http://www.mehrli.uz">Mehrli qo‘llar</a> ||Sana: ' . $date],
                'SetFooter' => ['|{PAGENO}-sahifa|'],
            ],
        ]);

        $pdf->getApi()->SetProtection(array(), '', 'saxovatP@r0l2018' . date('d'), 128);

        return $pdf->render();
    }

}
