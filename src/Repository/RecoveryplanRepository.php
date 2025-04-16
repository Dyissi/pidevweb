<?php

namespace App\Repository;

use App\Entity\Recoveryplan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Recoveryplan|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recoveryplan|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recoveryplan[]    findAll()
 * @method Recoveryplan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecoveryplanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recoveryplan::class);
    }

    // Add custom query methods below

    /**
     * Find recovery plans by user.
     *
     * @param int $userId
     * @return Recoveryplan[] 
     */
    public function findByUserId(int $userId): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user_id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('r.recovery_StartDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search recovery plans by goal or description.
     *
     * @param string $searchTerm
     * @return Recoveryplan[] 
     */
    public function searchInjuries(string $searchTerm): array
{
    return $this->createQueryBuilder('i')
        ->leftJoin('i.user', 'u')  
        ->addSelect('u')  
        ->where('i.injuryType LIKE :searchTerm')
        ->orWhere('u.user_fname LIKE :searchTerm')
        ->orWhere('u.user_lname LIKE :searchTerm')
        ->setParameter('searchTerm', '%' . $searchTerm . '%')
        ->orderBy('i.injuryDate', 'DESC')
        ->getQuery()
        ->getResult();
}

    /**
     * Find recovery plans by injury.
     *
     * @param int $injuryId
     * @return Recoveryplan[] 
     */
    public function findByInjuryId(int $injuryId): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.injury_id = :injuryId')
            ->setParameter('injuryId', $injuryId)
            ->orderBy('r.recovery_StartDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get recovery plans within a specific date range.
     *
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @return Recoveryplan[] 
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.recovery_StartDate >= :startDate')
            ->andWhere('r.recovery_EndDate <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('r.recovery_StartDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
