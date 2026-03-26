<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }
    public function searchEvents(?string $location, ?string $date, ?string $seats): array
{
    $qb = $this->createQueryBuilder('e');

    if (!empty($location)) {
        $qb->andWhere('e.location LIKE :location')
           ->setParameter('location', '%' . $location . '%');
    }

    if (!empty($date)) {
        $start = new \DateTime($date . ' 00:00:00');
        $end = new \DateTime($date . ' 23:59:59');

        $qb->andWhere('e.date BETWEEN :start AND :end')
           ->setParameter('start', $start)
           ->setParameter('end', $end);
    }

    if (!empty($seats)) {
        $qb->andWhere('e.seats >= :seats')
           ->setParameter('seats', (int) $seats);
    }

    return $qb->orderBy('e.date', 'ASC')
              ->getQuery()
              ->getResult();
}

//    /**
//     * @return Event[] Returns an array of Event objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Event
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
