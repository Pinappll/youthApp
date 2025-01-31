<?php

namespace App\Repository;

use App\Entity\Attendance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Attendance>
 *
 * @method Attendance|null find($id, $lockMode = null, $lockVersion = null)
 * @method Attendance|null findOneBy(array $criteria, array $orderBy = null)
 * @method Attendance[]    findAll()
 * @method Attendance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttendanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Attendance::class);
    }

    public function findByEventWithStats(int $eventId)
    {
        return $this->createQueryBuilder('a')
            ->select('e', 'COUNT(a.id) as totalAttendees', 'SUM(CASE WHEN a.isPresent = true THEN 1 ELSE 0 END) as presentCount')
            ->join('a.event', 'e')
            ->where('e.id = :eventId')
            ->setParameter('eventId', $eventId)
            ->groupBy('e.id')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getAverageAttendanceRate(): float
    {
        $result = $this->createQueryBuilder('a')
            ->select('COALESCE(SUM(CASE WHEN a.isPresent = true THEN 1 ELSE 0 END) * 100.0 / COUNT(a.id), 0)')
            ->getQuery()
            ->getSingleScalarResult();

        return round($result, 1);
    }

    public function findAttendancesByCriteria(array $criteria, int $limit = null, int $offset = null)
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.event', 'e')
            ->where('a.youth = :youth')
            ->setParameter('youth', $criteria['youth'])
            ->orderBy('e.date', 'DESC');

        if (!empty($criteria['presence'])) {
            $qb->andWhere('a.isPresent = :presence')
               ->setParameter('presence', $criteria['presence'] === 'present');
        }

        if (!empty($criteria['month'])) {
            $date = new \DateTime($criteria['month'] . '-01');
            $qb->andWhere('e.date >= :startDate')
               ->andWhere('e.date < :endDate')
               ->setParameter('startDate', $date->format('Y-m-01'))
               ->setParameter('endDate', $date->format('Y-m-t'));
        }

        if ($limit) {
            $qb->setMaxResults($limit)
               ->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    public function countAttendancesByCriteria(array $criteria)
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->leftJoin('a.event', 'e')
            ->where('a.youth = :youth')
            ->setParameter('youth', $criteria['youth']);

        if (!empty($criteria['presence'])) {
            $qb->andWhere('a.isPresent = :presence')
               ->setParameter('presence', $criteria['presence'] === 'present');
        }

        if (!empty($criteria['month'])) {
            $date = new \DateTime($criteria['month'] . '-01');
            $qb->andWhere('e.date >= :startDate')
               ->andWhere('e.date < :endDate')
               ->setParameter('startDate', $date->format('Y-m-01'))
               ->setParameter('endDate', $date->format('Y-m-t'));
        }

        return $qb->getQuery()->getSingleScalarResult();
    }
}