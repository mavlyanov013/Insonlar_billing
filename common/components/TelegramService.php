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

    $blue = imagecolorallocate($image, 0, 115, 201);
    $gray = imagecolorallocate($image, 95, 95, 95);

    // Faqat summa va sana yoziladi.
    $amountText = $data['amount'];
    $dateText = $data['date'];

    // --- SUMMA ---
    $amountFontSize = 86;
    $amountBox = imagettfbbox($amountFontSize, 0, $fontBold, $amountText);
    $amountWidth = $amountBox[2] - $amountBox[0];
    $amountX = (imagesx($image) - $amountWidth) / 2;
    $amountY = 900;

    imagettftext($image, $amountFontSize, 0, (int)$amountX, $amountY, $blue, $fontBold, $amountText);

    // --- SANA ---
    $dateFontSize = 24;
    $dateBox = imagettfbbox($dateFontSize, 0, $font, $dateText);
    $dateWidth = $dateBox[2] - $dateBox[0];
    $dateX = (imagesx($image) - $dateWidth) / 2;
    $dateY = 1335;

    imagettftext($image, $dateFontSize, 0, (int)$dateX, $dateY, $gray, $font, $dateText);

    imagejpeg($image, $outputPath, 100);
    imagedestroy($image);

    return file_exists($outputPath);
}

    protected function generateSummaryPdf($outputPath, array $data)
{
    $payments = isset($data['payments']) ? $data['payments'] : [];

    $rows = '';
    $i = 1;
    foreach ($payments as $payment) {
        $amount = number_format((float)$payment->amount, 0, '.', ' ');
        $method = strtoupper((string)$payment->method);

        $time = '';
        if (!empty($payment->time)) {
            $time = date('H:i:s', ((int)$payment->time) / 1000);
        }

        $comment = '';
        if (!empty($payment->user_data)) {
            $comment = htmlspecialchars((string)$payment->user_data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        } elseif (!empty($payment->transaction_id)) {
            $comment = htmlspecialchars((string)$payment->transaction_id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        $rows .= '
            <tr>
                <td class="center">' . $i . '</td>
                <td class="right">' . $amount . '</td>
                <td class="center">' . $method . '</td>
                <td class="center">' . $time . '</td>
                <td>' . $comment . '</td>
            </tr>
        ';
        $i++;
    }

    if ($rows === '') {
        $rows = '
            <tr>
                <td colspan="5" class="center">Buguncha to\'lovlar topilmadi</td>
            </tr>
        ';
    }

    $html = '
    <html>
    <head>
        <meta charset="utf-8">
        <style>
            body {
                font-family: dejavusans;
                font-size: 12px;
                color: #222;
            }

            .header {
                width: 100%;
                margin-bottom: 12px;
            }

            .title-left {
                font-weight: bold;
                font-size: 14px;
            }

            .title-right {
                text-align: right;
                font-size: 12px;
                font-weight: bold;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 8px;
            }

            th, td {
                border: 1px solid #cfcfcf;
                padding: 7px 8px;
                font-size: 11px;
                vertical-align: middle;
            }

            th {
                background: #efefef;
                font-weight: bold;
                text-align: center;
            }

            .center {
                text-align: center;
            }

            .right {
                text-align: right;
            }

            .summary {
                margin-top: 14px;
                font-size: 13px;
            }

            .summary b {
                font-weight: bold;
            }

            .links {
                margin-top: 16px;
                font-size: 12px;
                line-height: 1.8;
            }

            .links a {
                color: #1a5fd0;
                text-decoration: underline;
            }

            .footer {
                margin-top: 30px;
                text-align: center;
                font-size: 11px;
                color: #666;
            }
        </style>
    </head>
    <body>
        <table class="header" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td class="title-left">Mehrli insonlar</td>
                <td class="title-right">Sana: ' . htmlspecialchars($data['date']) . '</td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">№</th>
                    <th style="width: 110px;">Summa</th>
                    <th style="width: 90px;">Turi</th>
                    <th style="width: 90px;">Vaqt</th>
                    <th>Izoh</th>
                </tr>
            </thead>
            <tbody>
                ' . $rows . '
            </tbody>
        </table>

        <div class="summary">
            ' . htmlspecialchars($data['date']) . ' ga qadar kunlik jami tushum:
            <b>' . htmlspecialchars($data['amount']) . ' so\'m</b>
        </div>

        <div class="links">
            "Muhtoj Bolajon" guruhiga a\'zo bo\'ling:
            <a href="https://t.me/mehrli_bolajon">@mehrli_bolajon</a><br>

            "Mehrli Insonlar" guruhiga a\'zo bo\'ling:
            <a href="https://t.me/mehrli_insonlar">@mehrli_insonlar</a><br><br>

            To\'lov qilish:
            <a href="https://payme.uz/fallback/merchant/?id=65897b594de4489c5e278a0f">PAYME</a> |
            <a href="https://my.click.uz/services/pay/?service_id=31218">CLICK</a> |
            <a href="https://www.apelsin.uz/open-service?serviceId=12030307">APELSIN</a> |
            <a href="https://app.paynet.uz/?m=4590">PAYNET</a>
        </div>

        <div class="footer">1-sahifa</div>
    </body>
    </html>';

    $mpdf = new \Mpdf\Mpdf([
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
	    'payments' => $this->getTodayPayments(),
        ]);

        $caption = "💚 <b>Bugungi jami xayriya</b>\n"
            . "💰 <b>Summa:</b> {$amount} so'm\n"
            . "🕒 <b>Vaqt:</b> {$date}";

        $docCaption = "Mexrli insonlar safida bo'ling:\n"
    	. "👉 <a href='https://payme.uz/fallback/merchant/?id=65897b594de4489c5e278a0f'>PAYME</a> | "
    	. "<a href='https://my.click.uz/services/pay/?service_id=31218'>CLICK</a> | "
    	. "<a href='https://www.apelsin.uz/open-service?serviceId=12030307'>UZUMBANK</a> | "
    	. "<a href='https://app.paynet.uz/?m=4590'>PAYNET</a>";

        if (file_exists($imagePath)) {
            $this->sendPhotoToAll($imagePath, $caption);
        } else {
            $this->sendMessageToAll($caption);
        }

        if (file_exists($pdfPath)) {
            $this->sendDocumentToAll($pdfPath, $docCaption);
        }
    }
    protected function getTodayPayments()
	{
    	$today = new \DateTime('today', new \DateTimeZone('Asia/Tashkent'));
    	$from = $today->getTimestamp() * 1000;
    	$to = time() * 1000;

    	return \common\models\payment\Payment::find()
        	->where([
            	'time' => [
                	'$gte' => $from,
                	'$lte' => $to,
            	],
            	'status' => 'success',
        	])
        	->orderBy(['time' => SORT_ASC])
        	->all();
	}
}
