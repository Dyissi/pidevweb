<?php

namespace App\Repository;

use App\Entity\Data;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Data::class);
    }

    
    //filtering 
    public function findFiltered(?string $filter = null): array
{
    $qb = $this->createQueryBuilder('p');

    switch ($filter) {
        case 'least_fouls':
            $qb->orderBy('p.performanceNbrFouls', 'ASC');
            break;
        case 'highest_speed':
            $qb->orderBy('p.performanceSpeed', 'DESC');
            break;
        default:
            $qb->orderBy('p.performanceDateRecorded', 'DESC');
    }

    return $qb->getQuery()->getResult();
}
public function findPerformanceData(): array
{
    return $this->createQueryBuilder('d')
        ->select([
            'AVG(d.performanceSpeed) as avg_speed',
            'SUM(d.performanceNbrGoals) as total_goals'
        ])
        ->getQuery()
        ->getSingleResult(); 
}

public function findAllOrdered(): array
{
    return $this->createQueryBuilder('d')
        ->orderBy('d.performanceDateRecorded', 'DESC')
        ->getQuery()
        ->getResult();
}

}