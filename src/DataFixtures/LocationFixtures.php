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
                'name' => 'Stade Olympique Rades',
                'address' => 'Route Mharza',
                'city' => 'Rades',
                'capacity' => 60000,
                'type' => 'Outdoor'
            ],
            [
                'name' => 'Salle Sport El Menzah',
                'address' => 'Rue du Lac Biwa',
                'city' => 'Tunis',
                'capacity' => 5000,
                'type' => 'Indoor'
            ],
            [
                'name' => 'Complexe Sportif Sousse',
                'address' => 'Avenue Habib Bourguiba',
                'city' => 'Sousse',
                'capacity' => 3000,
                'type' => 'Outdoor'
            ],
            [
                'name' => 'Stade Mustapha Ben Jannet',
                'address' => 'Avenue Habib Bourguiba',
                'city' => 'Monastir',
                'capacity' => 20000,
                'type' => 'Outdoor'
            ],
            [
                'name' => 'Stade Olympique Sousse',
                'address' => 'Avenue Leopold Sedar Senghor',
                'city' => 'Sousse',
                'capacity' => 25000,
                'type' => 'Outdoor'
            ],
            [
                'name' => 'Stade Chedly Zouiten',
                'address' => 'Avenue Mohamed V',
                'city' => 'Tunis',
                'capacity' => 18000,
                'type' => 'Outdoor'
            ],
        ];

        foreach ($locations as $index => $locationData) {
            $location = new Location();
            $location->setLocationName($locationData['name']);
            $location->setLocationAddress($locationData['address']);
            $location->setLocationCity($locationData['city']);
            $location->setLocationCapacity($locationData['capacity']);
            $location->setLocationType($locationData['type']);

            // Store reference for other fixtures if needed
            $this->addReference('location_' . $index, $location);

            $manager->persist($location);
        }

        $manager->flush();
    }
}