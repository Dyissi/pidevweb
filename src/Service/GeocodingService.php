<?php

namespace App\Service;

use App\Entity\Location;
use Geocoder\Provider\Provider;

class GeocodingService
{
    private $geocoder;

    public function __construct(Provider $nominatimGeocoder)
    {
        $this->geocoder = $nominatimGeocoder;
    }

    public function getCoordinates(Location $location): ?array
    {
        $address = sprintf(
            '%s, %s, Tunisie',
            $location->getLocationAddress(),
            $location->getLocationCity()
        );

        try {
            // Add a small delay to respect Nominatim's usage policy
            usleep(1000000); // 1 second delay
            
            $query = \Geocoder\Query\GeocodeQuery::create($address)
                ->withLocale('fr')
                ->withLimit(1)
                ->withData('countrycodes', 'tn');

            $result = $this->geocoder->geocodeQuery($query);

            if ($result->count() > 0) {
                $coordinates = $result->first()->getCoordinates();
                return [
                    'latitude' => $coordinates->getLatitude(),
                    'longitude' => $coordinates->getLongitude()
                ];
            }
            
            // If not found, try with just city and country
            $cityQuery = \Geocoder\Query\GeocodeQuery::create(
                sprintf('%s, Tunisie', $location->getLocationCity())
            )
                ->withLocale('fr')
                ->withLimit(1)
                ->withData('countrycodes', 'tn');

            $cityResult = $this->geocoder->geocodeQuery($cityQuery);
            
            if ($cityResult->count() > 0) {
                $coordinates = $cityResult->first()->getCoordinates();
                return [
                    'latitude' => $coordinates->getLatitude(),
                    'longitude' => $coordinates->getLongitude()
                ];
            }
            
            return null;
        } catch (\Exception $e) {
            // Handle the error appropriately
            throw new \RuntimeException('Geocoding failed: ' . $e->getMessage());
        }
    }

    private function isWithinTunisia(float $lat, float $lng): bool
    {
        // Tunisia's approximate bounding box
        $minLat = 30.230236;
        $maxLat = 37.728926;
        $minLng = 7.524833;
        $maxLng = 11.598278;

        return $lat >= $minLat && $lat <= $maxLat && $lng >= $minLng && $lng <= $maxLng;
    }

    private function normalizeAddress(string $address): string
    {
        // Common Tunisian address abbreviations and corrections
        $replacements = [
            // Streets and Roads
            'Av.' => 'Avenue',
            'Ave.' => 'Avenue',
            'Rue' => 'Avenue',
            'Route' => 'Road',
            'Rt' => 'Road',
            'Boulevard' => 'Avenue',
            'Blvd' => 'Avenue',
            
            // Common place names
            'Lac' => 'Lake',
            'Berges' => 'Banks',
            'Centre' => 'Center',
            'Pôle' => 'Pole',
            'Technologique' => 'Technology',
            
            // Common Tunisian street names
            'Mohamed V' => 'Mohammed V',
            'Habib Bourguiba' => 'Bourguiba',
            'Leopold Sedar Senghor' => 'Senghor',
            'Fethi Zouhir' => 'Fethi Zouhir Road',
            'Mharza' => 'El Mharza',
            
            // Areas
            'Cebalat' => 'Cebalet',
            'El Menzah' => 'Menzah',
            'Les Berges du Lac' => 'Lake Bank'
        ];

        // Remove special characters except basic punctuation
        $address = preg_replace('/[^\p{L}\p{N}\s,.-]/u', ' ', $address);
        
        // Normalize spaces
        $address = preg_replace('/\s+/', ' ', $address);
        
        // Apply replacements
        $address = str_replace(array_keys($replacements), array_values($replacements), $address);
        
        return trim($address);
    }

    private function normalizeCity(string $city): string
    {
        // Common Tunisian city name normalizations
        $cityReplacements = [
            'tunis' => 'Tunis',
            'ariana' => 'Ariana',
            'Rades' => 'Rades',
            'sousse' => 'Sousse',
            'monastir' => 'Monastir'
        ];

        $city = strtolower(trim($city));
        return $cityReplacements[$city] ?? ucfirst($city);
    }
} 