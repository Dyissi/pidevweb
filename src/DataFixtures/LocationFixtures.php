<?php

namespace App\DataFixtures;

use App\Entity\Location;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LocationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $locations = [
            [
                'name' => 'Olympic Stadium Rades',
                'address' => 'Route Mharza, Rades',
                'city' => 'Rades',
                'capacity' => 60000,
                'type' => 'Outdoor'
            ],
            [
                'name' => 'Sports Hall El Menzah',
                'address' => 'Rue du Lac Biwa, Les Berges du Lac',
                'city' => 'Tunis',
                'capacity' => 5000,
                'type' => 'Indoor'
            ],
            [
                'name' => 'Sports Complex Sousse',
                'address' => 'Avenue Habib Bourguiba, Sousse Centre',
                'city' => 'Sousse',
                'capacity' => 3000,
                'type' => 'Outdoor'
            ],
            // Add more well-known Tunisian sports venues
            [
                'name' => 'Stadium Mustapha Ben Jannet',
                'address' => 'Avenue Habib Bourguiba',
                'city' => 'Monastir',
                'capacity' => 20000,
                'type' => 'Outdoor'
            ],
            [
                'name' => 'Olympic Stadium Sousse',
                'address' => 'Avenue Leopold Sedar Senghor',
                'city' => 'Sousse',
                'capacity' => 25000,
                'type' => 'Outdoor'
            ],
            [
                'name' => 'Stadium Chedly Zouiten',
                'address' => 'Avenue Mohamed V',
                'city' => 'Tunis',
                'capacity' => 18000,
                'type' => 'Outdoor'
            ],
        ];

        foreach ($locations as $locationData) {
            $location = new Location();
            $location->setLocationName($locationData['name']);
            $location->setLocationAddress($locationData['address']);
            $location->setLocationCity($locationData['city']);
            $location->setLocationCapacity($locationData['capacity']);
            $location->setLocationType($locationData['type']);

            $manager->persist($location);
        }

        $manager->flush();
    }
} 
