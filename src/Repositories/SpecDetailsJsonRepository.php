<?php

namespace Ufo\EAV\Repositories;

use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Query;
use Ufo\EAV\Entity\Views\SpecDetailsJson;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method SpecDetailsJson|null find($id, $lockMode = null, $lockVersion = null)
 * @method SpecDetailsJson|null findOneBy(array $criteria, array $orderBy = null)
 * @method SpecDetailsJson[] findAll()
 * @method SpecDetailsJson[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpecDetailsJsonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SpecDetailsJson::class);
    }

    public function getOneByParam(string $paramTag, string|int|bool $value): SpecDetailsJson
    {
        $results = $this->getByParam($paramTag, $value);
        return $results[0];
    }

    public function getByParam(string $paramTag, string|int|bool $value): array
    {
        if (!$results = $this->findByParam($paramTag, $value)) {
            throw new EntityNotFoundException("SpecDetailsJson for '$paramTag = $value' is not found" );
        }
        return $results;
    }

    public function findByParam(string $paramTag, string|int|bool $value): array
    {
        $query = $this->getQuery($paramTag, $value);
        return $query->getResult();
    }

    protected function getQuery(string $paramTag, string|int|bool $value): Query
    {
        $qb = $this->createQueryBuilder('p');
        $jsonExtract = sprintf('JSON_EXTRACT(p.spec_values, \'$.%s.value\')', $paramTag);

        $qb->andWhere($qb->expr()->eq($jsonExtract, ':value'))
           ->setParameter('value', $value, ParameterType::STRING);

        return $qb->getQuery();
    }

}
