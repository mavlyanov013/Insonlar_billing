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

    public function actionTelegram($test = false)
    {
        Yii::$app->language = 'uz-UZ';
        $summ               = Payment::getTodayPaymentAmount();
        $amount             = Yii::$app->formatter->asCurrency($summ);
        $day                = date('d');
        $last               = intval(Config::get("last_payment_$day"));
        print_r(['day' => $day, 'lst' => $last, 'now' => $summ]);

        if ($summ  > 0 || $test) {
            $count = Payment::getTodayPaymentCount();

            $hour  = date('H:i');
            $month = Yii::$app->formatter->asDate(time(), 'php:mm');

            $image   = imagecreatefromjpeg(Yii::getAlias('@frontend/assets/app/images/donate_stat.jpg'));
            $width   = imagesx($image);
            $height  = imagesy($image);
            $centerX = $width / 2;
            $centerY = $height / 2;
            list($left, $bottom, $right, , , $top) = $ts = imageftbbox(30, 0, Yii::getAlias('@frontend/assets/vendor/fonts/Poppins-Bold.ttf'), $amount);
            $left_offset = ($right - $left) / 2;
            $top_offset  = ($bottom - $top) / 2;
            $x           = abs($ts[4] - $ts[0]) + 10;
            $y           = $centerY - $top_offset;
            imagedestroy($image);
            // write today amount
            $image = Image::text(
                Yii::getAlias('@frontend/assets/app/images/donate_stat.jpg'),
                preg_replace("/[a-zA-Zа-я‘]+/", '', $amount), '@frontend/assets/vendor/fonts/Poppins-Bold.ttf',
                [500 - ($left_offset + (strlen($amount) * 2)), 215],
                ['color' => '000000', 'size' => 50]
            );

            $poppins = Image::getImagine()->font(
                Yii::getAlias('@frontend/assets/vendor/fonts/Poppins-Bold.ttf'),
                47,
                new Color('000000'));

            $quicksand = Image::getImagine()->font(
                Yii::getAlias('@frontend/assets/vendor/fonts/Quicksand-Bold.otf'),
                47,
                new Color('000000'));

            $quicksand_s = Image::getImagine()->font(
                Yii::getAlias('@frontend/assets/vendor/fonts/Quicksand-Bold.otf'),
                20,
                new Color('000000'));
            $font_h      = Image::getImagine()->font(
                Yii::getAlias('@frontend/assets/vendor/fonts/Quicksand-Bold.otf'),
                14,
                new Color('000000'));

            $left = strlen(Payment::getTodayPaymentCount()) * 10;
            // write count members
            $image->draw()->text($count, $poppins, new Point(500 - ((strlen($count) / 2) * 20), 25));
            // write day
            $image->draw()->text($day, $quicksand, new Point(75, 70));
            // write month
            $image->draw()->text($month, $quicksand_s, new Point(75, 140));
            $image->draw()->text($hour, $font_h, new Point(80, 170));
            // $image->draw()->polygon([new Point(40, 200), new Point(182, 200), new Point(182, 230), new Point(40, 230)], new Color('ffff00'), true);

            $dir  = Yii::getAlias('@static/uploads');
            $time = time();

            $imageFile = $dir . "/donate_{$time}.jpg";
            $image->save($imageFile);


            $bot = new BotApi(getenv('BOT_TOKEN'));

           /* $bot->setCurlOption(CURLOPT_PROXY, getenv('CURLOPT_PROXY'));
            $bot->setCurlOption(CURLOPT_PROXYUSERPWD, getenv('CURLOPT_PROXYUSERPWD'));
            $bot->setCurlOption(CURLOPT_FOLLOWLOCATION, 1);
            $bot->setCurlOption(CURLOPT_RETURNTRANSFER, 1);
            $bot->setCurlOption(CURLOPT_HEADER, 1);*/

            $chats = Config::getAsArray(Config::TELEGRAM_CHATS, []);
            $file  = false;
            try {
                $file = $this->actionGenerateFile();
            } catch (\Exception $e) {
                echo $e->getMessage();
                echo "error at: " . $e->getTraceAsString();
            }
            foreach ($chats as $item) {
                try {
                    $bot->sendPhoto($item, new \CURLFile($imageFile));
                } catch (Exception $exception) {
                    echo $exception->getMessage() . ' ' . $item . ' code ' . $exception->getCode() . PHP_EOL;
                }

                try {
                    if ($file) {
                        $bot->sendDocument($item, new \CURLFile($file), 'To‘lovlar');
                    }
                } catch (\Exception $e) {
                    echo $exception->getMessage() . ' ' . $item . ' code ' . $exception->getCode() . PHP_EOL;
                }
            }
        }

        Config::set("last_payment_$day", $summ);
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
                'subject' => "Saxovat Qo‘qon uchun $date ga qadar kunlik o‘tkazilgan summalar",
            ],
            'methods'     => [
                'SetHeader' => ['"Saxovat Qo‘qon" Jamoat Xayriya Fondi ||Sana: ' . $date],
                'SetFooter' => ['|{PAGENO}-sahifa|'],
            ],
        ]);

        $pdf->getApi()->SetProtection(array(), '', 'saxovatquqonP@r0l2018' . date('d'), 128);

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
