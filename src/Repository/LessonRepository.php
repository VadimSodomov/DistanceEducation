<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Lesson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lesson>
 */
class LessonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lesson::class);
    }

    public function getStatistics(int $lessonId): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = <<<SQL
        SELECT COUNT(DISTINCT lu.user_id)                                                           AS passed_count,
               COUNT(DISTINCT cu.user_id) - COUNT(DISTINCT lu.user_id)                              AS not_passed_count,
               COUNT(DISTINCT cu.user_id)                                                           AS total_users,
               ROUND(COUNT(DISTINCT lu.user_id) * 100.0 / NULLIF(COUNT(DISTINCT cu.user_id), 0), 2) AS passed_percentage,
               ROUND((COUNT(DISTINCT cu.user_id) - COUNT(DISTINCT lu.user_id)) * 100.0 / NULLIF(COUNT(DISTINCT cu.user_id), 0),
                     2)                                                                             AS not_passed_percentage,
               AVG(lu.score)                                                                        AS avg_passed,
               COUNT(DISTINCT lu.user_id) FILTER ( WHERE lu.score >= 80 )                           AS passed_count_80,
               COUNT(DISTINCT lu.user_id) FILTER ( WHERE lu.score >= 60 AND lu.score < 80)          AS passed_count_60,
               COUNT(DISTINCT lu.user_id) FILTER ( WHERE lu.score >= 40 AND lu.score < 60)          AS passed_count_40,
               COUNT(DISTINCT lu.user_id) FILTER ( WHERE lu.score >= 20 AND lu.score < 40)          AS passed_count_20,
               COUNT(DISTINCT lu.user_id) FILTER ( WHERE lu.score < 20)                             AS passed_count_0
        FROM course_user cu
                 INNER JOIN lesson l ON l.course_id = cu.course_id
                 LEFT JOIN lesson_user lu ON lu.lesson_id = :lessonId AND lu.user_id = cu.user_id
        WHERE l.id = :lessonId
        SQL;

        return $conn->executeQuery($sql, ['lessonId' => $lessonId])->fetchAssociative();
    }
}
