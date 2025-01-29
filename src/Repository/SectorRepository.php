<?php

namespace App\Repository;

use App\Entity\Sector;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sector>
 *
 * @method Sector|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sector|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sector[]    findAll()
 * @method Sector[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SectorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sector::class);
    }

    public function findWithStatistics()
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s as sector', 'COUNT(DISTINCT c.id) as churchCount', 'COUNT(DISTINCT y.id) as youthCount')
            ->leftJoin('s.churches', 'c')
            ->leftJoin('c.youths', 'y')
            ->groupBy('s.id');

        $results = $qb->getQuery()->getResult();

        // Reformater les résultats
        return array_map(function($result) {
            return [
                'sector' => $result['sector'],
                'churchCount' => $result['churchCount'],
                'youthCount' => $result['youthCount']
            ];
        }, $results);
    }
} 