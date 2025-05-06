<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class BadWordsService
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function containsBadWords(string $text): bool
    {
        $response = $this->client->request('GET', 'https://www.purgomalum.com/service/containsprofanity', [
            'query' => ['text' => $text],
        ]);

        return $response->getContent() === 'true';
    }
}
