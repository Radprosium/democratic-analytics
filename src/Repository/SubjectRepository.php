<?php

namespace App\Repository;

use App\Entity\Subject;
use App\Enum\SubjectCategory;
use App\Enum\SubjectStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Subject>
 */
class SubjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subject::class);
    }

    public function save(Subject $subject, bool $flush = true): void
    {
        $this->getEntityManager()->persist($subject);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find subjects ordered by importance and date.
     *
     * @return Subject[]
     */
    public function findAllOrderedByImportance(int $limit = 50): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.importance', 'DESC')
            ->addOrderBy('s.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find active subjects ordered by most recently updated.
     *
     * @return Subject[]
     */
    public function findActive(int $limit = 50): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.status = :status')
            ->setParameter('status', SubjectStatus::Active)
            ->orderBy('s.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find subjects by category.
     *
     * @return Subject[]
     */
    public function findByCategory(SubjectCategory $category, int $limit = 50): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.category = :category')
            ->setParameter('category', $category)
            ->orderBy('s.importance', 'DESC')
            ->addOrderBy('s.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Search subjects by title (case-insensitive LIKE).
     *
     * @return Subject[]
     */
    public function searchByTitle(string $query, int $limit = 20): array
    {
        return $this->createQueryBuilder('s')
            ->where('LOWER(s.title) LIKE LOWER(:query)')
            ->setParameter('query', '%'.$query.'%')
            ->orderBy('s.importance', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findBySlug(string $slug): ?Subject
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
