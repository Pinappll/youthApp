<?php

namespace App\Repository;

use App\Entity\Youth;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Youth>
 *
 * @method Youth|null find($id, $lockMode = null, $lockVersion = null)
 * @method Youth|null findOneBy(array $criteria, array $orderBy = null)
 * @method Youth[]    findAll()
 * @method Youth[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class YouthRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Youth::class);
    }

    public function findByChurchAndAge(int $churchId, int $minAge, int $maxAge)
    {
        $now = new \DateTime();
        $maxBirthDate = (clone $now)->modify("-{$minAge} years");
        $minBirthDate = (clone $now)->modify("-{$maxAge} years");

        return $this->createQueryBuilder('y')
            ->andWhere('y.church = :churchId')
            ->andWhere('y.birthDate BETWEEN :minBirthDate AND :maxBirthDate')
            ->setParameter('churchId', $churchId)
            ->setParameter('minBirthDate', $minBirthDate)
            ->setParameter('maxBirthDate', $maxBirthDate)
            ->orderBy('y.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }
} 