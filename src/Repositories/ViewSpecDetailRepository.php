<?php

namespace Ufo\EAV\Repositories;

use Doctrine\ORM\EntityRepository;
use Ufo\EAV\Entity\Views\SpecDetail;

class ViewSpecDetailRepository extends EntityRepository
{
    /**
     * @param string $queryString
     * @return SpecDetail[]
     */
    public function search(string $queryString): array
    {
        $queryBuilder = $this->createQueryBuilder('sd');

        $queryBuilder->select('sd', 's')
            ->leftJoin('sd.spec', 's')  // Додаємо join з Spec
            ->where('LOWER(sd.value) = :exactQuery')
            ->orWhere('LOWER(sd.value) LIKE :partialQuery')
            ->orderBy('CASE WHEN LOWER(sd.value) = :exactQuery THEN 0 ELSE 1 END')
            ->setParameter('exactQuery', strtolower($queryString))
            ->setParameter('partialQuery', '%' . strtolower($queryString) . '%');

        return $queryBuilder->getQuery()->getResult();
    }

    public function all()
    {
        return $this->findAll();
    }
}