<?php

namespace common\components;

use common\models\payment\Payment;
use Mpdf\Mpdf;
use Yii;

class TelegramService
{
    public $botToken;
    public $chatId;

    protected function callTelegram($method, $postFields = [])
    {
        if (!$this->botToken) {
            return false;
        }

        $url = "https://api.telegram.org/bot{$this->botToken}/{$method}";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $result = curl_exec($ch);

        if ($result === false) {
            Yii::error('Telegram CURL error: ' . curl_error($ch), 'telegram');
        }

        curl_close($ch);

        return $result;
    }

    public function sendMessage($text)
    {
        if (!$this->chatId || !$text) {
            return false;
        }

        return $this->callTelegram('sendMessage', [
            'chat_id' => $this->chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);
    }

    public function sendPhoto($photoPath, $caption = null)
    {
        if (!$this->chatId || !$photoPath || !file_exists($photoPath)) {
            return false;
        }

        $postFields = [
            'chat_id' => $this->chatId,
            'photo' => new \CURLFile($photoPath),
            'parse_mode' => 'HTML',
        ];

        if ($caption) {
            $postFields['caption'] = $caption;
        }

        return $this->callTelegram('sendPhoto', $postFields);
    }

    public function sendDocument($documentPath, $caption = null)
    {
        if (!$this->chatId || !$documentPath || !file_exists($documentPath)) {
            return false;
        }

        $postFields = [
            'chat_id' => $this->chatId,
            'document' => new \CURLFile($documentPath),
            'parse_mode' => 'HTML',
        ];

        if ($caption) {
            $postFields['caption'] = $caption;
        }

        return $this->callTelegram('sendDocument', $postFields);
    }

    public function sendPaymentNotification(Payment $payment)
    {
        Yii::error('telegram: sendPaymentNotification started', 'telegram');

        $amount = number_format((float)$payment->amount, 0, '.', ' ');
        $date = date('d-m-Y H:i:s', (int)($payment->time / 1000));
        $transactionId = (string)$payment->transaction_id;
        $method = strtoupper((string)$payment->method);
        $localId = (string)$payment->local_id;
        $category = (string)$payment->category;
        $userData = trim((string)$payment->user_data);

        $baseDir = Yii::getAlias('@common/../runtime/telegram-payments');
        Yii::error('telegram: baseDir=' . $baseDir, 'telegram');

        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
        }

        $imagePath = $baseDir . '/payment-' . $transactionId . '.jpg';
        $pdfPath   = $baseDir . '/payment-' . $transactionId . '.pdf';

        Yii::error('telegram: imagePath=' . $imagePath, 'telegram');
        Yii::error('telegram: pdfPath=' . $pdfPath, 'telegram');

        $photoCaption = "💚 <b>Yangi xayriya tushdi</b>\n"
            . "💰 <b>Summa:</b> {$amount} so'm\n"
            . "💳 <b>To'lov turi:</b> {$method}\n"
            . "🕒 <b>Vaqt:</b> {$date}";

        $docCaption = "Mexrli insonlar safida bo'ling:\n👉 PAYME | CLICK | APELSIN";

        $imageGenerated = $this->generatePaymentImage($imagePath, [
            'amount' => $amount,
            'date' => $date,
        ]);

        Yii::error('telegram: imageGenerated=' . var_export($imageGenerated, true), 'telegram');
        Yii::error('telegram: imageExists=' . (file_exists($imagePath) ? 'yes' : 'no'), 'telegram');

        if ($imageGenerated && file_exists($imagePath)) {
            $photoResult = $this->sendPhoto($imagePath, $photoCaption);
            Yii::error('telegram: sendPhoto result=' . $photoResult, 'telegram');
        } else {
            $textResult = $this->sendMessage($photoCaption);
            Yii::error('telegram: fallback sendMessage result=' . $textResult, 'telegram');
        }

        $this->generatePaymentPdf($pdfPath, [
            'amount' => $amount,
            'date' => $date,
            'transactionId' => $transactionId,
            'method' => $method,
            'localId' => $localId,
            'category' => $category,
            'userData' => $userData,
        ]);

        Yii::error('telegram: pdfExists=' . (file_exists($pdfPath) ? 'yes' : 'no'), 'telegram');

        if (file_exists($pdfPath)) {
            $docResult = $this->sendDocument($pdfPath, $docCaption);
            Yii::error('telegram: sendDocument result=' . $docResult, 'telegram');
        }

        Yii::error('telegram: sendPaymentNotification finished', 'telegram');

        return true;
    }

    protected function generatePaymentImage($outputPath, array $data)
    {
        $templatePath = \Yii::getAlias('@common/assets/payment-template.jpg');
        Yii::error('telegram: templatePath=' . $templatePath, 'telegram');

        if (!file_exists($templatePath)) {
            Yii::error('telegram: template not found', 'telegram');
            return false;
        }

        $fontBold = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
        $font = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';

        if (!file_exists($fontBold) || !file_exists($font)) {
            Yii::error('telegram: font not found', 'telegram');
            return false;
        }

        $image = imagecreatefromjpeg($templatePath);

        if (!$image) {
            Yii::error('telegram: imagecreatefromjpeg failed', 'telegram');
            return false;
        }

        $blue  = imagecolorallocate($image, 0, 115, 201);
        $black = imagecolorallocate($image, 40, 40, 40);

        imagettftext($image, 48, 0, 250, 600, $blue, $fontBold, $data['amount']);
        imagettftext($image, 28, 0, 220, 700, $black, $font, "so'm xayriya qilindi");
        imagettftext($image, 20, 0, 300, 900, $black, $font, $data['date']);

        imagejpeg($image, $outputPath, 95);
        imagedestroy($image);

        Yii::error('telegram: output image created=' . (file_exists($outputPath) ? 'yes' : 'no'), 'telegram');

        return file_exists($outputPath);
    }

    protected function generatePaymentPdf($outputPath, array $data)
    {
        $html = '
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                body {
                    font-family: sans-serif;
                    color: #222;
                    font-size: 14px;
                }
                .wrapper {
                    border: 1px solid #d9d9d9;
                    padding: 24px;
                }
                .title {
                    font-size: 22px;
                    font-weight: bold;
                    color: #0b74c9;
                    margin-bottom: 10px;
                }
                .subtitle {
                    font-size: 14px;
                    color: #666;
                    margin-bottom: 25px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 10px;
                }
                td {
                    padding: 10px 8px;
                    border-bottom: 1px solid #e5e5e5;
                    vertical-align: top;
                }
                .label {
                    width: 220px;
                    font-weight: bold;
                    background: #f8f8f8;
                }
                .footer {
                    margin-top: 30px;
                    font-size: 13px;
                    color: #555;
                }
                .links {
                    margin-top: 8px;
                    font-weight: bold;
                    color: #0b74c9;
                }
            </style>
        </head>
        <body>
            <div class="wrapper">
                <div class="title">Mehrli insonlar - To\'lov ma\'lumoti</div>
                <div class="subtitle">Xayriya to\'lovi muvaffaqiyatli qabul qilindi</div>

                <table>
                    <tr>
                        <td class="label">Summa</td>
                        <td>' . htmlspecialchars($data['amount']) . ' so\'m</td>
                    </tr>
                    <tr>
                        <td class="label">To\'lov turi</td>
                        <td>' . htmlspecialchars($data['method']) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Transaction ID</td>
                        <td>' . htmlspecialchars($data['transactionId']) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Local ID</td>
                        <td>' . htmlspecialchars($data['localId']) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Kategoriya</td>
                        <td>' . htmlspecialchars($data['category']) . '</td>
                    </tr>
                    <tr>
                        <td class="label">User</td>
                        <td>' . htmlspecialchars($data['userData']) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Sana</td>
                        <td>' . htmlspecialchars($data['date']) . '</td>
                    </tr>
                </table>

                <div class="footer">
                    Mehrli insonlar safida bo\'ling:
                    <div class="links">PAYME | CLICK | APELSIN</div>
                </div>
            </div>
        </body>
        </html>';

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 12,
            'margin_bottom' => 12,
            'margin_left' => 12,
            'margin_right' => 12,
        ]);

        $mpdf->WriteHTML($html);
        $mpdf->Output($outputPath, \Mpdf\Output\Destination::FILE);
    }
}