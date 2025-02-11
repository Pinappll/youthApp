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

    public function findByCriteria(array $criteria, $orderBy = null, $limit = null, $offset = null)
    {
        $qb = $this->createQueryBuilder('y')
                   ->leftJoin('y.church', 'c')
                   ->leftJoin('c.sector', 's');

        // Name search
        if (isset($criteria['firstName'])) {
            $qb->andWhere('LOWER(y.firstName) LIKE LOWER(:search) OR LOWER(y.lastName) LIKE LOWER(:search)')
               ->setParameter('search', '%' . $criteria['firstName'] . '%');
        }

        // Church filter
        if (isset($criteria['church'])) {
            $qb->andWhere('y.church IN (:churches)')
               ->setParameter('churches', $criteria['church']);
        }

        // Age filter
        if (!empty($criteria['ageGroup'])) {
            $now = new \DateTime();
            $eighteenYearsAgo = clone $now;
            $eighteenYearsAgo->modify('-18 years');
            
            if ($criteria['ageGroup'] === 'under18') {
                $qb->andWhere('y.birthDate > :eighteenYearsAgo')
                   ->setParameter('eighteenYearsAgo', $eighteenYearsAgo);
            } elseif ($criteria['ageGroup'] === 'over18') {
                $qb->andWhere('y.birthDate <= :eighteenYearsAgo')
                   ->setParameter('eighteenYearsAgo', $eighteenYearsAgo);
            }
        }

        // Sector filter
        if (isset($criteria['sector'])) {
            $qb->andWhere('s.id IN (:sectors)')
               ->setParameter('sectors', $criteria['sector']);
        }

        // Add default ordering
        $qb->orderBy('y.lastName', 'ASC')
           ->addOrderBy('y.firstName', 'ASC');

        if ($limit) {
            $qb->setMaxResults($limit)
               ->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    public function countByCriteria(array $criteria)
    {
        $qb = $this->createQueryBuilder('y')
                   ->select('COUNT(y.id)')
                   ->leftJoin('y.church', 'c')
                   ->leftJoin('c.sector', 's');

        // Name search
        if (isset($criteria['firstName'])) {
            $qb->andWhere('LOWER(y.firstName) LIKE LOWER(:search) OR LOWER(y.lastName) LIKE LOWER(:search)')
               ->setParameter('search', '%' . $criteria['firstName'] . '%');
        }

        // Church filter
        if (isset($criteria['church'])) {
            $qb->andWhere('y.church IN (:churches)')
               ->setParameter('churches', $criteria['church']);
        }

        // Age filter
        if (isset($criteria['ageGroup'])) {
            $now = new \DateTime();
            $eighteenYearsAgo = (clone $now)->modify('-18 years');
            
            if ($criteria['ageGroup'] === 'under18') {
                $qb->andWhere('y.birthDate > :eighteenYearsAgo')
                   ->setParameter('eighteenYearsAgo', $eighteenYearsAgo->format('Y-m-d'));
            } elseif ($criteria['ageGroup'] === 'over18') {
                $qb->andWhere('y.birthDate <= :eighteenYearsAgo')
                   ->setParameter('eighteenYearsAgo', $eighteenYearsAgo->format('Y-m-d'));
            }
        }

        // Sector filter
        if (isset($criteria['sector'])) {
            $qb->andWhere('s.id IN (:sectors)')
               ->setParameter('sectors', $criteria['sector']);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findUpcomingBirthdays(\DateTime $from, \DateTime $to): array
{
    // Récupérer tous les Youth avec une date de naissance
    $qb = $this->createQueryBuilder('y')
        ->where('y.birthDate IS NOT NULL')
        ->getQuery()
        ->getResult();

    // Récupérer la date actuelle en format mois-jour (ex: "0211" pour le 11 février)
    $today = (int)(new \DateTime())->format('md');
    $fromDate = (int)$from->format('md');
    $toDate = (int)$to->format('md');

    // Filtrage des anniversaires dans la plage de dates
    $upcomingBirthdays = array_filter($qb, function(Youth $youth) use ($fromDate, $toDate, $today) {
        $birthday = $youth->getBirthDate();
        if (!$birthday) {
            return false;
        }

        // Extraire mois et jour de l'anniversaire
        $birthdayDate = (int)$birthday->format('md');

        // Cas où la plage traverse une année (ex: 15 décembre → 15 janvier)
        if ($fromDate > $toDate) {
            return ($birthdayDate >= $fromDate || $birthdayDate <= $toDate) && $birthdayDate >= $today;
        }

        // Cas normal (ex: 1 février → 1 mars)
        return $birthdayDate >= $fromDate && $birthdayDate <= $toDate && $birthdayDate >= $today;
    });

    // Trier les anniversaires par mois et jour
    usort($upcomingBirthdays, function(Youth $a, Youth $b) {
        return (int)$a->getBirthDate()->format('md') <=> (int)$b->getBirthDate()->format('md');
    });

    return array_values($upcomingBirthdays);
}

}