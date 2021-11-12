<?php

namespace console\controllers;

use common\components\Config;
use common\models\Ad;
use common\models\Admin;
use common\models\Log;
use common\models\payment\Payment;
use common\models\Place;
use common\models\Post;
use common\models\Stat;
use common\models\Volunteer;
use Imagine\Image\Color;
use Imagine\Image\Point;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use Yii;
use yii\console\Controller;
use yii\imagine\Image;

class IndexerController extends Controller
{

    public function actionTelegram($test = 0)
    {

        Yii::$app->language = 'uz-UZ';
        $summ               = Payment::getTodayPaymentAmount();
        if ($summ || $test) {
            $count = Payment::getTodayPaymentCount();

            $quicksand     = Yii::getAlias('@frontend/assets/vendor/fonts/Quicksand-Regular.otf');
            $quicksandBold = Yii::getAlias('@frontend/assets/vendor/fonts/Quicksand-Bold.otf');
            $poppins       = Yii::getAlias('@frontend/assets/vendor/fonts/Poppins-Bold.ttf');
            $imageFile     = Yii::getAlias('@frontend/assets/app/images/stat_1.jpg');

            $image   = imagecreatefromjpeg($imageFile);
            $width   = imagesx($image);
            $height  = imagesy($image);
            $centerX = $width / 2;

            $image = Image::getImagine()->open($imageFile);

            $yOffset = -150;
            $texts   = [
                [
                    __('Bugun'),
                    36,
                    530,
                    '555555',
                    $quicksandBold
                ],
                [
                    Yii::$app->formatter->asInteger($summ),
                    62,
                    580,
                    '187DB1',
                    $poppins
                ],
                [
                    __('so\'m xayriya qilindi'),
                    36,
                    665,
                    '555555',
                    $quicksandBold
                ],
                [
                    __('{date},  {count} ta ishtirokchi', [
                        'count' => $count,
                        'date'  => mb_strtolower(Yii::$app->formatter->asDate(time(), 'php:j-F')),
                    ]),
                    16,
                    920,
                    '555555',
                    $quicksandBold
                ]
            ];
            foreach ($texts as $item) {
                list($left, $bottom, $right, , , $top) = imageftbbox($item[1], 0, $quicksand, $item[0]);
                $font = Image::getImagine()->font($item[4], $item[1], new Color($item[3]));
                $x    = $centerX - ($right - $left) / 2;
                $image->draw()->text($item[0], $font, new Point($x, $yOffset + $item[2]));
            }


            $imgFile  = Yii::getAlias('@static/uploads/donate.jpg');
            $time = time();

            $image->save($imgFile);

            $bot   = new BotApi(getenv('BOT_TOKEN'));
            $chats = Config::getAsArray(Config::TELEGRAM_CHATS, []);
            $file  = false;

            try {
                $file = $this->actionGenerateFile();
            } catch (\Exception $e) {
                echo $e->getMessage();
            }

            foreach ($chats as $item) {
                try {
                    $bot->sendPhoto($item, new \CURLFile($imgFile));

                    if ($file) {
                        $bot->sendDocument($item, new \CURLFile($file), "To‘lovlar");
                    }
                } catch (Exception $exception) {
                    echo $exception->getMessage() . ' ' . $item . ' code ' . $exception->getCode() . PHP_EOL;
                }
            }
        }
    }


    public function actionGenerateFile()
    {
        Yii::$app->language = 'uz-UZ';
        $date               = date("d-m-Y H:i:s");
        $time               = time();
        $file               = Yii::getAlias("@static/pdf/Payments-$time.pdf");
        $content            = $this->renderPartial('@frontend/views/payments-pdf', ['date' => $date]);

        $pdf = new \kartik\mpdf\Pdf([
                                        'mode'        => \kartik\mpdf\Pdf::MODE_UTF8,
                                        'content'     => $content,
                                        'filename'    => $file,
                                        'destination' => \kartik\mpdf\Pdf::DEST_FILE,
                                        'cssFile'     => '@frontend/assets/app/css/pdf.css',
                                        'options'     => [
                                            'title'   => 'To‘lov tarixi',
                                            'subject' => "Mehrli insonlar fondi uchun $date ga qadar kunlik o‘tkazilgan summalar",
                                        ],
                                        'methods'     => [
                                            'SetHeader' => ['Mehrli insonlar ||Sana: ' . $date],
                                            'SetFooter' => ['|{PAGENO}-sahifa|'],
                                        ],
                                    ]);

        $pdf->getApi()->SetProtection(array(), '', 'saxovatP@r0l2018' . date('d'), 128);

        $pdf->render();

        return $file;
    }


    /**
     * every 1 minutes
     */
    public function actionVeryFast()
    {

    }

    /**
     * every 5 minutes
     */
    public function actionFast()
    {
        /**
         * @var Post  $post
         * @var Admin $admin
         */
        Stat::indexPostViewsReset();
        Stat::indexPostViewsAll();

        Ad::reindexStatuses();
        Place::reindexStatuses();

        Stat::indexAdClicksAll();
        Stat::indexAdViewsAll();
    }

    /**
     * every half hour
     */
    public function actionNormal()
    {
        SitemapController::generate();
    }

    /**
     * every 1 hour
     */
    public function actionSlow()
    {

    }

    /**
     * every 24 hour
     */
    public function actionDaily()
    {
        echo Log::cleanLogs() . PHP_EOL;
    }

    public function actionCreateIndex()
    {

    }

    /**
     * @return \yii\mongodb\Connection
     */
    private static function getConnection()
    {
        return Yii::$app->mongodb;
    }

    public function actionVol()
    {
        Volunteer::updateAll(['type' => Volunteer::TYPE_VOLUNTEER]);
    }

}
