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
        
        $count = 0;
        foreach ($csv as $record) {
            $data = new Data();
            $data->setPerformanceSpeed((float)$record['performanceSpeed']);
            $data->setPerformanceAgility((float)$record['performanceAgility']);
            $data->setPerformanceNbrGoals((int)$record['performanceNbrGoals']);
            $data->setPerformanceAssists((int)$record['performanceAssists']);
            $data->setPerformanceDateRecorded(new \DateTime($record['performanceDateRecorded']));
            $data->setPerformanceNbrFouls((int)$record['performanceNbrFouls']);
            $data->setUser($user);
            
            $this->em->persist($data);
            $count++;
        }
        
        $this->em->flush();
        return $count;
    }
}