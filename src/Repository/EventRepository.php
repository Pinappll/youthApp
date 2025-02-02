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

    public function findFiltered(array $criteria = [], int $page = 1, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.sector', 's')
            ->leftJoin('e.targetChurch', 'tc')
            ->leftJoin('e.targetSector', 'ts')
            ->leftJoin('e.attendances', 'a')
            ->addSelect('s')
            ->addSelect('tc')
            ->addSelect('ts')
            ->addSelect('COUNT(DISTINCT a.id) as total_attendances')
            ->addSelect('SUM(CASE WHEN a.isPresent = true THEN 1 ELSE 0 END) as present_count')
            ->groupBy('e.id')
            ->addGroupBy('s.id')
            ->addGroupBy('tc.id')
            ->addGroupBy('ts.id')
            ->orderBy('e.date', 'DESC');

        if (!empty($criteria['search'])) {
            $qb->andWhere('e.name LIKE :search')
               ->setParameter('search', '%' . $criteria['search'] . '%');
        }

        if (!empty($criteria['month'])) {
            $startDate = new \DateTime($criteria['month'] . '-01');
            $endDate = (clone $startDate)->modify('last day of this month');
            $qb->andWhere('e.date BETWEEN :start AND :end')
               ->setParameter('start', $startDate)
               ->setParameter('end', $endDate);
        }

        if (!empty($criteria['sectors'])) {
            $qb->andWhere('e.sector IN (:sectors)')
               ->setParameter('sectors', $criteria['sectors']);
        }

        if (!empty($criteria['churches'])) {
            $qb->andWhere('e.targetChurch IN (:churches)')
               ->setParameter('churches', $criteria['churches']);
        }

        $totalItems = count($qb->getQuery()->getResult());
        $totalPages = ceil($totalItems / $limit);

        $qb->setMaxResults($limit)
           ->setFirstResult(($page - 1) * $limit);

        $results = $qb->getQuery()->getResult();

        // Calculate attendance rates
        $events = array_map(function($result) {
            $event = $result[0];
            $totalAttendances = $result['total_attendances'];
            $presentCount = $result['present_count'];
            
            $event->attendanceRate = $totalAttendances > 0 
                ? round(($presentCount / $totalAttendances) * 100) 
                : null;
            
            return $event;
        }, $results);

        return [
            'events' => $events,
            'totalPages' => $totalPages,
            'currentPage' => $page
        ];
    }
}