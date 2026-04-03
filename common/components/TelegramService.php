<?php

namespace common\components;

use Mpdf\Mpdf;
use Yii;

class TelegramService
{
    public $botToken;
    public $chatIds = [];

    protected function getChatIds()
    {
        return array_values(array_filter((array)$this->chatIds));
    }

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

    public function sendMessageToAll($text)
    {
        foreach ($this->getChatIds() as $chatId) {
            $this->callTelegram('sendMessage', [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);
        }
    }

    public function sendPhotoToAll($photoPath, $caption = null)
    {
        if (!file_exists($photoPath)) {
            Yii::error('telegram: photo not found: ' . $photoPath, 'telegram');
            return;
        }

        foreach ($this->getChatIds() as $chatId) {
            $postFields = [
                'chat_id' => $chatId,
                'photo' => new \CURLFile($photoPath),
                'parse_mode' => 'HTML',
            ];

            if ($caption) {
                $postFields['caption'] = $caption;
            }

            $this->callTelegram('sendPhoto', $postFields);
        }
    }

    public function sendDocumentToAll($documentPath, $caption = null)
    {
        if (!file_exists($documentPath)) {
            Yii::error('telegram: document not found: ' . $documentPath, 'telegram');
            return;
        }

        foreach ($this->getChatIds() as $chatId) {
            $postFields = [
                'chat_id' => $chatId,
                'document' => new \CURLFile($documentPath),
                'parse_mode' => 'HTML',
            ];

            if ($caption) {
                $postFields['caption'] = $caption;
            }

            $this->callTelegram('sendDocument', $postFields);
        }
    }

    protected function centerText($image, $text, $fontPath, $fontSize, $y, $color)
    {
        $box = imagettfbbox($fontSize, 0, $fontPath, $text);
        $textWidth = $box[2] - $box[0];
        $imageWidth = imagesx($image);
        $x = (int)(($imageWidth - $textWidth) / 2);

        imagettftext($image, $fontSize, 0, $x, $y, $color, $fontPath, $text);
    }

    protected function generateSummaryImage($outputPath, array $data)
    {
        $templatePath = Yii::getAlias('@common/assets/payment-template.jpg');

        if (!file_exists($templatePath)) {
            Yii::error('telegram: template not found: ' . $templatePath, 'telegram');
            return false;
        }

        $fontBold = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
        $font = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';

        if (!file_exists($fontBold) || !file_exists($font)) {
            Yii::error('telegram: font not found', 'telegram');
            return false;
        }

        $imageInfo = getimagesize($templatePath);
        if (!$imageInfo || empty($imageInfo['mime'])) {
            Yii::error('telegram: unsupported template file', 'telegram');
            return false;
        }

        if ($imageInfo['mime'] === 'image/png') {
            $image = imagecreatefrompng($templatePath);
        } elseif ($imageInfo['mime'] === 'image/jpeg') {
            $image = imagecreatefromjpeg($templatePath);
        } else {
            Yii::error('telegram: unsupported template mime: ' . $imageInfo['mime'], 'telegram');
            return false;
        }

        if (!$image) {
            Yii::error('telegram: image open failed', 'telegram');
            return false;
        }

        $blue  = imagecolorallocate($image, 0, 115, 201);
        $black = imagecolorallocate($image, 55, 55, 55);
        $gray  = imagecolorallocate($image, 110, 110, 110);

        // template bo'sh bo'lishi kerak
        $this->centerText($image, 'Bugun', $font, 34, 360, $gray);
        $this->centerText($image, $data['amount'], $fontBold, 78, 520, $blue);
        $this->centerText($image, "so'm xayriya qilindi", $font, 30, 650, $black);
        $this->centerText($image, $data['date'], $font, 24, 1320, $gray);

        imagejpeg($image, $outputPath, 95);
        imagedestroy($image);

        return file_exists($outputPath);
    }

    protected function generateSummaryPdf($outputPath, array $data)
    {
        $html = '
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                body { font-family: sans-serif; color: #222; font-size: 14px; }
                .wrapper { border: 1px solid #d9d9d9; padding: 24px; }
                .title { font-size: 22px; font-weight: bold; color: #0b74c9; margin-bottom: 12px; }
                table { width: 100%; border-collapse: collapse; }
                td { padding: 10px 8px; border-bottom: 1px solid #e5e5e5; }
                .label { width: 220px; font-weight: bold; background: #f8f8f8; }
                .footer { margin-top: 30px; font-size: 13px; color: #555; }
            </style>
        </head>
        <body>
            <div class="wrapper">
                <div class="title">Mehrli insonlar - 3 soatlik hisobot</div>
                <table>
                    <tr><td class="label">Bugungi jami summa</td><td>' . htmlspecialchars($data['amount']) . ' so\'m</td></tr>
                    <tr><td class="label">Hisobot vaqti</td><td>' . htmlspecialchars($data['date']) . '</td></tr>
                    <tr><td class="label">Interval</td><td>Har 3 soatda, bugungi cumulative total</td></tr>
                </table>
                <div class="footer">
                    Mehrli insonlar safida bo\'ling: PAYME | CLICK | APELSIN
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

    public function sendDailySummary($amount, $date)
    {
        $amount = number_format((float)$amount, 0, '.', ' ');

        $baseDir = Yii::getAlias('@common/../runtime/telegram-payments');
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
        }

        $key = date('Ymd_His');
        $imagePath = $baseDir . '/summary-' . $key . '.jpg';
        $pdfPath = $baseDir . '/summary-' . $key . '.pdf';

        $this->generateSummaryImage($imagePath, [
            'amount' => $amount,
            'date' => $date,
        ]);

        $this->generateSummaryPdf($pdfPath, [
            'amount' => $amount,
            'date' => $date,
        ]);

        $caption = "💚 <b>Bugungi jami xayriya</b>\n"
            . "💰 <b>Summa:</b> {$amount} so'm\n"
            . "🕒 <b>Vaqt:</b> {$date}";

        $docCaption = "Mexrli insonlar safida bo'ling:\n👉 PAYME | CLICK | APELSIN";

        if (file_exists($imagePath)) {
            $this->sendPhotoToAll($imagePath, $caption);
        } else {
            $this->sendMessageToAll($caption);
        }

        if (file_exists($pdfPath)) {
            $this->sendDocumentToAll($pdfPath, $docCaption);
        }
    }
}