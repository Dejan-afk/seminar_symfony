<?php

namespace App\Repository;

use App\Entity\Seminar;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SeminarRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Seminar::class);
    }

    public function findUpcomingFiltered(?string $search, ?int $organizerId, int $limit = 20, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.organizer', 'o')->addSelect('o')
            ->orderBy('s.startDate', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->andWhere('s.startDate >= :now')
            ->setParameter('now', new \DateTimeImmutable());

        if ($search) {
            $qb->andWhere('LOWER(s.title) LIKE :search OR LOWER(s.description) LIKE :search')
                ->setParameter('search', '%' . mb_strtolower($search) . '%');
        }

        if ($organizerId) {
            $qb->andWhere('o.id = :organizerId')
                ->setParameter('organizerId', $organizerId);
        }

        return $qb->getQuery()->getResult();
    }

    public function findOneWithRelations(int $id): ?Seminar
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.organizer', 'o')->addSelect('o')
            ->leftJoin('s.sessions', 'sess')->addSelect('sess')
            ->leftJoin('s.registrations', 'r')->addSelect('r')
            ->andWhere('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}