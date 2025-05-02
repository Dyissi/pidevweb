<?php

namespace App\Service;

use App\Entity\Data;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;

class CsvImportService
{
    public function __construct(private EntityManagerInterface $em) 
    {
    }

    public function import(string $filePath, User $user): int
    {
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);
        
        // Get headers and normalize them (trim and lowercase)
        $headers = array_map(function($header) {
            return strtolower(trim($header));
        }, $csv->getHeader());
        
        $count = 0;
        foreach ($csv as $record) {
            // Normalize record keys to match headers
            $normalizedRecord = array_combine(
                array_map('strtolower', array_map('trim', array_keys($record))),
                array_values($record)
            );
            
            $data = new Data();
            $data->setPerformanceSpeed((float)$normalizedRecord['performancespeed']);
            $data->setPerformanceAgility((float)$normalizedRecord['performanceagility']);
            $data->setPerformanceNbrGoals((int)$normalizedRecord['performancenbrgoals']);
            $data->setPerformanceAssists((int)$normalizedRecord['performanceassists']);
            $data->setPerformanceDateRecorded(new \DateTime($normalizedRecord['performancedaterecorded']));
            $data->setPerformanceNbrFouls((int)$normalizedRecord['performancenbrfouls']);
            $data->setUser($user);
            
            $this->em->persist($data);
            $count++;
        }
        
        $this->em->flush();
        return $count;
    }
} 