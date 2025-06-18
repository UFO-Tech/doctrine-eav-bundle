<?php

namespace Ufo\EAV\Repositories;

use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Query;
use Ufo\EAV\Entity\Views\SpecDetailsJson;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Ufo\EAV\Filters\FilterRow\FilterData;
use Ufo\EAV\Services\LocaleService;

use function implode;
use function strtolower;

/**
 * @method SpecDetailsJson|null find($id, $lockMode = null, $lockVersion = null)
 * @method SpecDetailsJson|null findOneBy(array $criteria, array $orderBy = null)
 */
class SpecDetailsJsonRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        protected LocaleService $localeService
    )
    {
        parent::__construct($registry, SpecDetailsJson::class);
    }

    public function findAll(): array
    {
        return $this->findBy($this->localeService->getLocaleCriteria());
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return parent::findBy($this->localeService->addLocaleToCriteria($criteria), $orderBy, $limit, $offset);
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

    public function getByParams(string $paramTag, array $values): array
    {
        if (!$results = $this->findByParams($paramTag, $values)) {
            throw new EntityNotFoundException("SpecDetailsJson for '$paramTag = ". implode(', ', $values) ."' is not found" );
        }
        return $results;
    }

    public function findByParam(string $paramTag, string|int|bool $value): array
    {
        $query = $this->getQuery($paramTag, $value);
        return $query->getResult();
    }

    public function findByParams(string $paramTag, array $values): array
    {
        $query = $this->getQuerySomeParams($paramTag, $values);
        return $query->getResult();
    }

    protected function getQuery(string $paramTag, string|int|bool $value): Query
    {
         $qb = $this->createQueryBuilder('p')
            ->where("JSON_EXTRACT(p.specValues, :jsonPath) = :value")
            ->andWhere("JSON_EXTRACT(p.specValues, :jsonPath) IS NOT NULL") // Виключає NULL-рядки
            ->setParameter('jsonPath', '$.' . $paramTag . '.value')
            ->setParameter('value', $value);

         if ($this->localeService->isDefaultLocale()) {
             $qb->andWhere('p.locale IS NULL');
         } else {
             $qb->andWhere('p.locale = :locale')->setParameter('locale', $this->localeService->getLocale());
         }

        return $qb->getQuery();
    }

    protected function getQuerySomeParams(string $paramTag, array $values): Query
    {
        $qb = $this->createQueryBuilder('p');

        $qb->where($qb->expr()->in(
            "JSON_UNQUOTE(JSON_EXTRACT(p.specValues, :jsonPath))",
            ':values'
        ))
           ->setParameter('jsonPath', '$.' . $paramTag . '.value')
           ->setParameter('values', $values);

        if ($this->localeService->isDefaultLocale()) {
            $qb->andWhere('p.locale IS NULL');
        } else {
            $qb->andWhere('p.locale = :locale')
               ->setParameter('locale', $this->localeService->getLocale());
        }

        return $qb->getQuery();
    }

    /**
     * @param string $queryString
     * @param FilterData|null $filterData
     * @return SpecDetailsJson[]
     */
    public function search(string $queryString, ?FilterData $filterData = null): array
    {
        $exactQuery = strtolower($queryString);
        $partialQuery = '%' . strtolower($queryString) . '%';

        $queryBuilder = $this->createQueryBuilder('sd');

        $queryBuilder->select('sd')
                     ->where('LOWER(sd.specName) = :exactQuery')
                     ->orWhere('JSON_UNQUOTE(JSON_EXTRACT(sd.specValues, :jsonPath)) = :exactQuery')
                     ->orWhere('LOWER(sd.specName) LIKE :partialQuery')
                     ->orWhere('JSON_UNQUOTE(JSON_EXTRACT(sd.specValues, :jsonPath)) LIKE :partialQuery')
                     ->orderBy(
                         'CASE 
                        WHEN LOWER(sd.specName) = :exactQuery THEN 0
                        WHEN JSON_UNQUOTE(JSON_EXTRACT(sd.specValues, :jsonPath)) = :exactQuery THEN 1
                        WHEN LOWER(sd.specName) LIKE :partialQuery THEN 2
                        WHEN JSON_UNQUOTE(JSON_EXTRACT(sd.specValues, :jsonPath)) LIKE :partialQuery THEN 3
                        ELSE 4 
                      END'
                     )
                     ->setParameter('exactQuery', $exactQuery)
                     ->setParameter('partialQuery', $partialQuery)
                     ->setParameter('jsonPath', '$.*.value');

        if ($this->localeService->isDefaultLocale()) {
            $queryBuilder->andWhere('sd.locale is NULL');
        } else {
            $queryBuilder->andWhere('sd.locale = :locale')
                         ->setParameter('locale', $this->localeService->getLocale());
        }


        if ($filterData !== null && !$filterData->isEmpty()) {
            foreach ($filterData->getParams() as $param) {
                $tag = $param->tag;
                $values = array_map(fn($value) => mb_strtolower($value->content), $param->getValues());
                $jsonPath = '$.' . $tag . '.value';

                $queryBuilder->andWhere(
                    $queryBuilder->expr()->in(
                        "LOWER(JSON_UNQUOTE(JSON_EXTRACT(sd.specValues, :jsonPath_$tag)))",
                        ":$tag"
                    )
                )
                ->setParameter("jsonPath_$tag", $jsonPath)
                ->setParameter($tag, $values);
            }
        }
        return $queryBuilder->getQuery()->getResult();
    }

}
