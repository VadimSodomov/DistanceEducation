<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Course;
use App\Entity\LessonUser;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LessonUser>
 */
class LessonUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LessonUser::class);
    }

    /**
     * @return LessonUser[] Returns an array of LessonUser objects
     */
    public function findByCourseUser(Course $course, User $user): array
    {
        return $this->createQueryBuilder('lu')
            ->innerJoin('lu.lesson', 'l')
            ->andWhere('l.course = :course')
            ->andWhere('lu.user = :user')
            ->setParameter('course', $course)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }
}
