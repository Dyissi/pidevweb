<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ElevenLabsTTSService
{
    private $client;
    private $apiKey;

    public function __construct(HttpClientInterface $client, string $elevenLabsApiKey)
    {
        $this->client = $client;
        $this->apiKey = $elevenLabsApiKey;
    }

    public function textToSpeech(string $text, string $voiceId = 'IKne3meq5aSn9XLyUdCD'): string
    {
        $response = $this->client->request('POST', "https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}", [
            'headers' => [
                'xi-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'text' => $text,
                'model_id' => 'eleven_multilingual_v2',
            ],
        ]);

        $audioContent = $response->getContent();
        $filePath = sys_get_temp_dir() . '/output_' . uniqid() . '.mp3';
        file_put_contents($filePath, $audioContent);

        return $filePath;
    }
}
