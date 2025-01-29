<?php

namespace App\Repository;

use App\Entity\Church;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Church>
 *
 * @method Church|null find($id, $lockMode = null, $lockVersion = null)
 * @method Church|null findOneBy(array $criteria, array $orderBy = null)
 * @method Church[]    findAll()
 * @method Church[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChurchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Church::class);
    }

    public function findBySectorWithYouthCount(?int $sectorId = null)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c as church', 'COUNT(DISTINCT y.id) as youthCount')
            ->leftJoin('c.youths', 'y')
            ->groupBy('c.id');

        if ($sectorId !== null) {
            $qb->andWhere('c.sector = :sectorId')
               ->setParameter('sectorId', $sectorId);
        }

        $results = $qb->getQuery()->getResult();

        // Reformater les résultats
        return array_map(function($result) {
            return [
                'church' => $result['church'],
                'youthCount' => $result['youthCount']
            ];
        }, $results);
    }
} 