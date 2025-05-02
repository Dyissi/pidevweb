<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;

class NinjaApiService
{
    private const VALID_MUSCLES = [
        'abdominals', 'abductors', 'adductors', 'biceps', 'calves',
        'chest', 'forearms', 'glutes', 'hamstrings', 'lats',
        'lower_back', 'middle_back', 'neck', 'quadriceps',
        'traps', 'triceps'
    ];

    private const VALID_TYPES = [
        'cardio', 'olympic_weightlifting', 'plyometrics',
        'powerlifting', 'strength', 'stretching', 'strongman'
    ];

    private const VALID_DIFFICULTIES = [
        'beginner', 'intermediate', 'expert'
    ];

    private $httpClient;
    private $params;
    private $logger;

    public function __construct(
        HttpClientInterface $httpClient, 
        ParameterBagInterface $params,
        LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        $this->params = $params;
        $this->logger = $logger;
    }

    public function getExercisesByMuscle(string $muscle): array
    {
        try {
            $apiKey = $this->params->get('app.ninja_api_key');
            
            // Trim any whitespace from API key
            $apiKey = trim($apiKey);
            
            $this->logger->info('API Request Details:', [
                'api_key_length' => strlen($apiKey),
                'muscle' => $muscle
            ]);
            
            if (empty($apiKey)) {
                $this->logger->error('API key is empty or not set');
                throw new \Exception('API key is not configured');
            }

            $mappedMuscle = $this->mapFocusToMuscle($muscle);
            $this->logger->info('Mapped muscle: ' . $mappedMuscle);
            
            // Validate muscle group
            if (!in_array($mappedMuscle, self::VALID_MUSCLES)) {
                $this->logger->error('Invalid muscle group requested', [
                    'requested' => $mappedMuscle,
                    'valid_muscles' => self::VALID_MUSCLES
                ]);
                throw new \Exception('Invalid muscle group. Valid muscles are: ' . implode(', ', self::VALID_MUSCLES));
            }

            $requestUrl = 'https://api.api-ninjas.com/v1/exercises';
            $requestHeaders = [
                'X-Api-Key' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ];

            // Try first with just muscle parameter
            $queryParams = ['muscle' => $mappedMuscle];

            $this->logger->info('Making initial API request', [
                'url' => $requestUrl,
                'params' => $queryParams,
                'headers' => $requestHeaders
            ]);

            try {
                $response = $this->httpClient->request('GET', $requestUrl, [
                    'headers' => $requestHeaders,
                    'query' => $queryParams,
                ]);

                $statusCode = $response->getStatusCode();
                $this->logger->info('Initial API response status code: ' . $statusCode);

                if ($statusCode === 200) {
                    $content = $response->getContent();
                    $exercises = json_decode($content, true);
                    
                    if (json_last_error() === JSON_ERROR_NONE && is_array($exercises) && !empty($exercises)) {
                        $this->logger->info('Successfully fetched exercises with muscle parameter only');
                        return $this->formatExercises($exercises);
                    }
                }

                // If first attempt failed, try with both muscle and type
                $queryParams['type'] = 'strength';
                
                $this->logger->info('Making second API request with type parameter', [
                    'url' => $requestUrl,
                    'params' => $queryParams,
                    'headers' => $requestHeaders
                ]);

                $response = $this->httpClient->request('GET', $requestUrl, [
                    'headers' => $requestHeaders,
                    'query' => $queryParams,
                ]);

                $statusCode = $response->getStatusCode();
                $content = $response->getContent();
                
                $this->logger->info('Second API response', [
                    'status_code' => $statusCode,
                    'response_preview' => substr($content, 0, 100)
                ]);

                if ($statusCode !== 200) {
                    throw new \Exception('API request failed with status code: ' . $statusCode . '. Response: ' . $content);
                }

                $exercises = json_decode($content, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Failed to decode API response: ' . json_last_error_msg());
                }

                if (!is_array($exercises)) {
                    throw new \Exception('Invalid response format from API');
                }

                return $this->formatExercises($exercises);

            } catch (\Exception $e) {
                $this->logger->error('API request failed', [
                    'error' => $e->getMessage(),
                    'headers_sent' => $requestHeaders,
                    'query_params' => $queryParams
                ]);
                throw $e;
            }

        } catch (\Exception $e) {
            $this->logger->error('Failed to fetch exercises: ' . $e->getMessage(), [
                'muscle' => $muscle,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function formatExercises(array $exercises): array
    {
        return array_map(function($exercise) {
            return [
                'id' => md5($exercise['name']), // Generate a unique ID
                'name' => $exercise['name'],
                'description' => $exercise['instructions'],
                'category' => $exercise['muscle'],
                'equipment' => $exercise['equipment'],
                'difficulty' => $exercise['difficulty']
            ];
        }, $exercises);
    }

    private function mapFocusToMuscle(string $focus): string
    {
        // Convert to lowercase and trim for consistent matching
        $focus = strtolower(trim($focus));
        
        // Direct mapping if it's already a valid muscle
        if (in_array($focus, self::VALID_MUSCLES)) {
            $this->logger->info('Using direct muscle mapping', ['muscle' => $focus]);
            return $focus;
        }

        // Map training focus to valid muscle groups
        $mapping = [
            'arms' => 'biceps',
            'back' => 'lats',
            'chest' => 'chest',
            'shoulders' => 'traps',
            'legs' => 'quadriceps',
            'abs' => 'abdominals',
            'calves' => 'calves',
            'strength' => 'chest',
            'agility' => 'calves',
            'endurance' => 'quadriceps',
            'sprint' => 'hamstrings',
            'speed' => 'quadriceps',
            'dribbling' => 'forearms'
        ];

        $mappedMuscle = $mapping[$focus] ?? 'chest'; // Default to chest if no mapping found
        
        $this->logger->info('Mapped focus to muscle', [
            'original_focus' => $focus,
            'mapped_muscle' => $mappedMuscle,
            'was_mapped' => isset($mapping[$focus])
        ]);

        return $mappedMuscle;
    }
} 