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
} 