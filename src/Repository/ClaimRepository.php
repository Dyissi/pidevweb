<?php

namespace App\Repository;

use App\Entity\Claim;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class ClaimRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Claim::class);
    }

    public function findSortedFilteredQuery(
        ?string $status = null,
        ?string $category = null,
        ?string $submitterName = null,
        ?string $sortField = 'c.claimDate',
        ?string $sortDirection = 'ASC'
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.id_user', 'u')
            ->addSelect('u');

        if ($status) {
            $qb->andWhere('c.claimStatus = :status')
               ->setParameter('status', $status);
        }

        if ($category) {
            $qb->andWhere('c.claimCategory = :category')
               ->setParameter('category', $category);
        }

        if ($submitterName) {
            $qb->andWhere("CONCAT(u.user_fname, ' ', u.user_lname) LIKE :name")
               ->setParameter('name', '%' . $submitterName . '%');
        }

        // Allowed fields must include the alias "c." prefix
        $allowedSortFields = [
            'c.claimDescription',
            'c.claimStatus',
            'c.claimDate',
            'c.claimCategory',
        ];

        if (in_array($sortField, $allowedSortFields, true)) {
            $qb->orderBy($sortField, strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC');
        } else {
            $qb->orderBy('c.claimDate', 'ASC');
        }

        // Tell Doctrine to use KnpPaginator's OrderBy Walker
        $qb->getQuery()->setHint(
            \Doctrine\ORM\Query::HINT_CUSTOM_TREE_WALKERS,
            [\Knp\Component\Pager\Event\Subscriber\Sortable\Doctrine\ORM\Query\OrderByWalker::class]
        );

        return $qb;
    }
}
