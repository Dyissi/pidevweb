<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class AssemblyAIService
{
    private HttpClientInterface $httpClient;
    private string $apiKey;
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $httpClient, string $assemblyAiApiKey, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $assemblyAiApiKey;
        $this->logger = $logger;
    }

    public function uploadAudio(string $filePath): ?string
    {
        try {
            $response = $this->httpClient->request('POST', 'https://api.assemblyai.com/v2/upload', [
                'headers' => [
                    'authorization' => $this->apiKey,
                ],
                'body' => fopen($filePath, 'r'),
            ]);

            $data = $response->toArray();
            $this->logger->info('Audio uploaded. URL: ' . ($data['upload_url'] ?? 'none'));

            return $data['upload_url'] ?? null;
        } catch (\Throwable $e) {
            $this->logger->error('AssemblyAI upload failed: ' . $e->getMessage());
            return null;
        }
    }

    public function transcribe(string $audioUrl): ?string
    {
        try {
            $response = $this->httpClient->request('POST', 'https://api.assemblyai.com/v2/transcript', [
                'headers' => [
                    'authorization' => $this->apiKey,
                    'content-type' => 'application/json',
                ],
                'json' => [
                    'audio_url' => $audioUrl,
                ],
            ]);

            $data = $response->toArray();
            $transcriptId = $data['id'] ?? null;

            if (!$transcriptId) {
                $this->logger->error('No transcript ID returned from AssemblyAI.');
                return null;
            }

            $this->logger->info("Polling for transcript ID: $transcriptId");

            $status = '';
            $transcriptText = null;

            // Polling loop
            while ($status !== 'completed') {
                sleep(3);

                $pollResponse = $this->httpClient->request('GET', 'https://api.assemblyai.com/v2/transcript/' . $transcriptId, [
                    'headers' => [
                        'authorization' => $this->apiKey,
                    ],
                ]);

                $pollData = $pollResponse->toArray();
                $status = $pollData['status'] ?? 'unknown';

                $this->logger->info("Polling status: $status");

                if ($status === 'completed') {
                    $transcriptText = $pollData['text'] ?? null;
                } elseif ($status === 'error') {
                    $errorMessage = $pollData['error'] ?? 'Unknown transcription error';
                    $this->logger->error('AssemblyAI error: ' . $errorMessage);
                    return null;
                }
            }

            return $transcriptText;
        } catch (\Throwable $e) {
            $this->logger->error('Transcription exception: ' . $e->getMessage());
            return null;
        }
    }
}
