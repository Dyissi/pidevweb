<?php

namespace App\Repository;

use App\Entity\Injury;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Injury|null find($id, $lockMode = null, $lockVersion = null)
 * @method Injury|null findOneBy(array $criteria, array $orderBy = null)
 * @method Injury[]    findAll()
 * @method Injury[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InjuryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Injury::class);
    }

    // Add custom query methods below

    /**
     * Find injuries by severity.
     *
     * @param string $severity
     * @return Injury[]
     */
    public function findBySeverity(string $severity): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.injury_severity = :severity')
            ->setParameter('severity', $severity)
            ->orderBy('i.injuryDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search injuries based on injury type or user name.
     *
     * @param string $searchTerm
     * @return Injury[]
     */
    public function searchInjuries(string $searchTerm): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.user_id', 'u')
            ->andWhere('i.injuryType LIKE :searchTerm')
            ->orWhere('u.user_fname LIKE :searchTerm')
            ->orWhere('u.user_lname LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('i.injuryDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByUserId($userId): array
{
    return $this->createQueryBuilder('i')
        ->andWhere('i.user = :user')
        ->setParameter('user', $userId)
        ->getQuery()
        ->getResult();
}
}
