<?php
require 'vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

$client = new Client();
try {
    $response = $client->post('https://api.infobip.com/sms/2/text/advanced', [
        'headers' => [
            'Authorization' => 'App 61c6081f58caf63e486745d5bab5d94e-8d3606d0-c309-4ef1-867f-98eb0eca04ae',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ],
        'json' => [
            'messages' => [
                [
                    'from' => '+447491163443',
                    'destinations' => [['to' => '+21627100103']],
                    'text' => 'Test OTP: 123456',
                ],
            ],
        ],
    ]);
    echo "Response: " . $response->getBody() . "\n";
} catch (RequestException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if ($e->hasResponse()) {
        echo "Response: " . $e->getResponse()->getBody() . "\n";
    }
} catch (Exception $e) {
    echo "General Error: " . $e->getMessage() . "\n";
}