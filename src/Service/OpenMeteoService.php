<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenMeteoService
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getWeatherData(float $lat, float $lon): ?array
    {
        $url = sprintf(
            'https://api.open-meteo.com/v1/forecast?latitude=%s&longitude=%s&hourly=temperature_2m,relative_humidity_2m,rain,showers,wind_speed_10m,soil_temperature_0cm,soil_moisture_0_to_1cm',
            $lat, $lon
        );
        try {
            $response = $this->httpClient->request('GET', $url);
            $data = $response->toArray();
            // Find the index for the current hour
            $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
            $currentHour = $now->format('Y-m-d\TH:00');
            $index = array_search($currentHour, $data['hourly']['time']);
            if ($index === false) {
                $index = 0; // fallback to first hour
            }
            return [
                'temperature' => $data['hourly']['temperature_2m'][$index] ?? null,
                'humidity' => $data['hourly']['relative_humidity_2m'][$index] ?? null,
                'rain' => $data['hourly']['rain'][$index] ?? null,
                'wind_speed' => $data['hourly']['wind_speed_10m'][$index] ?? null,
                'soil_temp' => $data['hourly']['soil_temperature_0cm'][$index] ?? null,
                'soil_moisture' => $data['hourly']['soil_moisture_0_to_1cm'][$index] ?? null,
                'city' => 'Tunis', // You can enhance this with reverse geocoding if needed
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
} 