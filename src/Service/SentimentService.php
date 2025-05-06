<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class SentimentService
{
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(HttpClientInterface $client, string $huggingfaceApiKey)
    {
        $this->client = $client;
        $this->apiKey = $huggingfaceApiKey;
    }

    public function analyzeSentiment(string $text): array
    {
        $response = $this->client->request('POST', 'https://api-inference.huggingface.co/models/distilbert-base-uncased-finetuned-sst-2-english', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => ['inputs' => $text],
        ]);

        return $response->toArray();
    }
}
