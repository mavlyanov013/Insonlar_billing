<?php

namespace common\components;

use common\models\payment\Payment;

class TelegramService
{
    public $botToken;
    public $chatId;

    public function sendMessage($text)
    {
        if (!$this->botToken || !$this->chatId || !$text) {
            return false;
        }

        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

        $postFields = [
            'chat_id' => $this->chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    public function sendPaymentNotification(Payment $payment)
    {
        $amount = number_format((float)$payment->amount, 0, '.', ' ');
        $date = date('Y-m-d H:i:s', (int)($payment->time / 1000));

        $userData = trim((string)$payment->user_data);
        $transactionId = (string)$payment->transaction_id;
        $method = strtoupper((string)$payment->method);
        $status = strtoupper((string)$payment->status);
        $localId = (string)$payment->local_id;
        $category = (string)$payment->category;

        $text  = "✅ <b>Yangi to‘lov tushdi</b>\n\n";
        $text .= "🆔 <b>Local ID:</b> {$localId}\n";
        $text .= "💳 <b>Metod:</b> {$method}\n";
        $text .= "📌 <b>Status:</b> {$status}\n";
        $text .= "💰 <b>Summa:</b> {$amount} so‘m\n";
        $text .= "👤 <b>User:</b> " . htmlspecialchars($userData, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "\n";
        $text .= "🗂 <b>Kategoriya:</b> {$category}\n";
        $text .= "🔢 <b>Transaction ID:</b> {$transactionId}\n";
        $text .= "🕒 <b>Vaqt:</b> {$date}";

        return $this->sendMessage($text);
    }
}