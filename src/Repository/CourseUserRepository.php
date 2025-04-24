<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CourseUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CourseUser>
 */
class CourseUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourseUser::class);
    }

    public function isParticipant(int $courseId, int $userId): bool
    {
        $result = $this->createQueryBuilder('cu')
            ->andWhere('cu.course = :courseId')
            ->andWhere('cu.user = :userId')
            ->setParameter('courseId', $courseId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();

        return $result !== null;
    }

//    /**
//     * @return CourseUser[] Returns an array of CourseUser objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CourseUser
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
