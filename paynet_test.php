<?php

$wsdl = __DIR__ . '/api/web/ProviderWebService.wsdl';

$client = new SoapClient($wsdl, [
    'location' => 'http://127.0.0.1:8002/v1/paynet',
    'trace' => 1,
    'exceptions' => true,
    'cache_wsdl' => WSDL_CACHE_NONE,
]);

$params = [
    'username' => 'testuser',
    'password' => 'testpass123',
    'serviceId' => 1,
    'transactionId' => '999001',
    'transactionTime' => date('Y-m-d\TH:i:s.000P'),
    'amount' => 500000,
    'parameters' => [
        [
            'paramKey' => 'account',
            'paramValue' => 'Rahmatillo'
        ]
    ]
];

try {
    $result = $client->__soapCall('PerformTransaction', [$params]);
    var_dump($result);
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}