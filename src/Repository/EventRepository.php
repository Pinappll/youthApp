<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findBySectorAndDateRange(int $sectorId, \DateTime $start, \DateTime $end)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.sector = :sectorId')
            ->andWhere('e.date BETWEEN :start AND :end')
            ->setParameter('sectorId', $sectorId)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findUpcoming(int $limit = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.date > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('e.date', 'ASC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function countUpcoming(): int
    {
        return $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.date > :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getSingleScalarResult();
    }
} 