<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class WgerApiService
{
    private $httpClient;
    private $baseUrl = 'https://api.api-ninjas.com/v1';
    private $apiKey;

    public function __construct(HttpClientInterface $httpClient, string $apiKey)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
    }

    public function getExercisesByCategory(string $category): array
    {
        $muscle = $this->getMuscleCategory($category);
        
        try {
            $response = $this->httpClient->request('GET', "{$this->baseUrl}/exercises", [
                'headers' => [
                    'X-Api-Key' => $this->apiKey
                ],
                'query' => [
                    'muscle' => $muscle
                ]
            ]);

            $data = $response->toArray();
            
            if (!is_array($data)) {
                return [];
            }

            return array_map(function($exercise) {
                return [
                    'id' => md5($exercise['name']), // Generate a unique ID since API doesn't provide one
                    'name' => $exercise['name'],
                    'description' => $exercise['instructions'],
                    'category' => $exercise['muscle'],
                    'type' => $exercise['type'],
                    'equipment' => $exercise['equipment'],
                    'difficulty' => $exercise['difficulty']
                ];
            }, $data);

        } catch (\Exception $e) {
            error_log('Exercise API Error: ' . $e->getMessage());
            return [];
        }
    }

    public function getExerciseDetails(int $exerciseId): ?array
    {
        try {
            $response = $this->httpClient->request('GET', "{$this->baseUrl}/exerciseinfo/{$exerciseId}/", [
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'query' => [
                    'format' => 'json'
                ]
            ]);

            return $response->toArray();
        } catch (\Exception $e) {
            error_log('WgerAPI Error: ' . $e->getMessage());
            return null;
        }
    }

    private function getMuscleCategory(string $category): string
    {
        $muscleMap = [
            'arms' => 'biceps',      // Maps to biceps exercises
            'shoulders' => 'traps',   // Maps to shoulder/trap exercises
            'chest' => 'chest',
            'calves' => 'calves',
            'legs' => 'quadriceps',   // Maps to quad exercises
            'back' => 'lats',         // Maps to back exercises
            'abs' => 'abdominals'
        ];

        return $muscleMap[strtolower($category)] ?? 'biceps';
    }
} 