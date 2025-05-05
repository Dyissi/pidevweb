<?php

namespace App\Repository;

use App\Entity\TrainingSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TrainingSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrainingSession::class);
    }

    // Add your custom repository methods here
    public function findAllOrderedByStartTime(): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.sessionStartTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByFocus(?string $focus = null): array
    {
        $qb = $this->createQueryBuilder('t');

        if ($focus && $focus !== 'all') {
            $qb->andWhere('t.sessionFocus = :focus')
               ->setParameter('focus', $focus);
        }

        return $qb->orderBy('t.sessionStartTime', 'DESC')
                 ->getQuery()
                 ->getResult();
    }

    public function findFiltered(?string $filter = null, ?string $focus = null): array
    {
        $qb = $this->createQueryBuilder('t');

        // Apply focus filter if provided
        if ($focus && $focus !== 'all') {
            $qb->andWhere('t.sessionFocus = :focus')
               ->setParameter('focus', $focus);
        }

        // Apply sorting and time-based filters
        switch ($filter) {
            case 'upcoming':
                $qb->andWhere('t.sessionStartTime >= :now')
                   ->setParameter('now', new \DateTime())
                   ->orderBy('t.sessionStartTime', 'ASC');
                break;
            case 'past':
                $qb->andWhere('t.sessionStartTime < :now')
                   ->setParameter('now', new \DateTime())
                   ->orderBy('t.sessionStartTime', 'DESC');
                break;
            case 'longest':
                $qb->orderBy('t.sessionDuration', 'DESC');
                break;
            case 'shortest':
                $qb->orderBy('t.sessionDuration', 'ASC');
                break;
            default:
                $qb->orderBy('t.sessionStartTime', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }
}