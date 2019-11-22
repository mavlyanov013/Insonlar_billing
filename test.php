<?php

$a    = json_decode('{"store_id":"38","transaction_id":"160","amount":"50000","sign":"a3b92c414ac8e1f50820f00ee1f8de40","transaction_time":"2018-11-23 16:22:10","account":"Ильхом"}', true);
$hash = md5($a['store_id'] . $a['transaction_id'] . $a['account'] . $a['amount'] . 'Lz3eMzChgRurJx8e9sQGGzKQPxniAfYj');
print_r($a);
echo $hash . PHP_EOL;

var_dump($a['sign'] === $hash);